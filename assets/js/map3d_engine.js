/**
 * Map3DEngine — 3D City & Garden Visualization
 * Inspired by cartesiancs/map3d (Three.js, OpenStreetMap Overpass Buildings/Roads Extrusion)
 * Focused on generating 3D maps directly from selected plots and gardens.
 */

(function (window) {
    'use strict';

    const SCALE = 51000;
    const OVERPASS_URL = 'https://overpass-api.de/api/interpreter';

    class Map3DEngine {
        constructor(options) {
            this.container = typeof options.container === 'string' 
                ? document.getElementById(options.container) 
                : options.container;
            
            this.initialCenter = options.center || { lat: 14.5995, lng: 120.9842 };
            this.landsData = options.landsData || [];
            this.plotsData = options.plotsData || [];
            this.onLandClick = options.onLandClick || null;
            this.onExit3D = options.onExit3D || null;
            this.basePath = options.basePath || '../';
            this.activeTimeMode = 'day'; // 'day' | 'sunset' | 'night'

            this.scene = null;
            this.camera = null;
            this.renderer = null;
            this.controls = null;
            this.raycaster = new THREE.Raycaster();
            this.mouse = new THREE.Vector2();

            this.buildingsGroup = new THREE.Group();
            this.roadsGroup = new THREE.Group();
            this.gardensGroup = new THREE.Group();
            this.selectedPlotGroup = new THREE.Group();
            this.markersList = [];
            this.plotMeshesList = [];
            this.buildingsMeshList = [];

            this.enablePlotDragging = options.enablePlotDragging !== undefined ? options.enablePlotDragging : true;
            this.onPlotRepositioned = options.onPlotRepositioned || null;
            this.isDraggingPlot = false;
            this.draggedPlotGroup = null;
            this.dragPlane = new THREE.Plane(new THREE.Vector3(0, 1, 0), -1.3);
            this.planeIntersect = new THREE.Vector3();
            this.dragOffset = new THREE.Vector3();

            this.refLat = this.initialCenter.lat;
            this.refLng = this.initialCenter.lng;
            this.currentLand = null;
            this.currentPlot = null;
            this.isInitialized = false;
            this.animationFrameId = null;
            this.cachedAreas = {};

            this.init();
        }

        project(lat, lng) {
            const x = (lng - this.refLng) * SCALE * Math.cos((this.refLat * Math.PI) / 180);
            const z = -(lat - this.refLat) * SCALE;
            return { x, z };
        }

        unproject(x, z) {
            const lat = this.refLat - z / SCALE;
            const lng = this.refLng + x / (SCALE * Math.cos((this.refLat * Math.PI) / 180));
            return { lat, lng };
        }

        init() {
            if (!this.container || typeof THREE === 'undefined') return;

            const width = this.container.clientWidth || window.innerWidth;
            const height = this.container.clientHeight || 500;

            // Scene setup
            this.scene = new THREE.Scene();
            this.scene.background = new THREE.Color(0xdce8f2);
            this.scene.fog = new THREE.FogExp2(0xdce8f2, 0.0015);

            // Camera setup
            this.camera = new THREE.PerspectiveCamera(50, width / height, 1, 6000);
            this.camera.position.set(0, 110, 150);

            // Renderer setup
            this.renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            this.renderer.setSize(width, height);
            this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
            this.renderer.shadowMap.enabled = true;
            this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            this.container.appendChild(this.renderer.domElement);

            // Controls setup
            if (typeof THREE.OrbitControls !== 'undefined') {
                this.controls = new THREE.OrbitControls(this.camera, this.renderer.domElement);
                this.controls.enableDamping = true;
                this.controls.dampingFactor = 0.06;
                this.controls.maxPolarAngle = Math.PI / 2 - 0.04; // Don't look under ground
                this.controls.minDistance = 2; // Allow maximum close-up zoom into plots
                this.controls.maxDistance = 2500;
                this.controls.target.set(0, 0, 0);
            }

            // Lighting setup
            this.setupLighting();

            // Ground & Grid
            this.setupGround();

            // Add groups to scene
            this.scene.add(this.roadsGroup);
            this.scene.add(this.buildingsGroup);
            this.scene.add(this.gardensGroup);
            this.scene.add(this.selectedPlotGroup);

            // Create TopNav & HUD Overlays
            this.createTopNav();
            this.createHUD();

            // Event Listeners
            this.bindEvents();

            // Animation Loop
            this.animate = this.animate.bind(this);
            this.animate();

            this.isInitialized = true;
        }

        setupLighting() {
            this.ambientLight = new THREE.AmbientLight(0xffffff, 0.85);
            this.scene.add(this.ambientLight);

            this.dirLight = new THREE.DirectionalLight(0xfff7ed, 1.25);
            this.dirLight.position.set(150, 300, 180);
            this.dirLight.castShadow = true;
            this.dirLight.shadow.mapSize.width = 2048;
            this.dirLight.shadow.mapSize.height = 2048;
            this.dirLight.shadow.camera.near = 10;
            this.dirLight.shadow.camera.far = 1200;
            const d = 300;
            this.dirLight.shadow.camera.left = -d;
            this.dirLight.shadow.camera.right = d;
            this.dirLight.shadow.camera.top = d;
            this.dirLight.shadow.camera.bottom = -d;
            this.dirLight.shadow.bias = -0.0005;
            this.scene.add(this.dirLight);

            this.hemiLight = new THREE.HemisphereLight(0xffffff, 0x94a3b8, 0.4);
            this.scene.add(this.hemiLight);
        }

        setupGround() {
            const groundGeo = new THREE.PlaneGeometry(3500, 3500);
            const groundMat = new THREE.MeshStandardMaterial({
                color: 0xebf2f7,
                roughness: 0.9,
                metalness: 0.1
            });
            const ground = new THREE.Mesh(groundGeo, groundMat);
            ground.rotation.x = -Math.PI / 2;
            ground.receiveShadow = true;
            this.scene.add(ground);

            // Subtle technological grid plane
            const grid = new THREE.GridHelper(3000, 120, 0xc8d7e6, 0xdfe9f2);
            grid.position.y = 0.05;
            this.scene.add(grid);
        }

        /**
         * Generates the 3D Map focused directly on a Selected Land or Plot
         * Follows cartesiancs/map3d generation sequence with scanning modal
         */
        async generateForPlot(land, plot = null) {
            if (!land) return;
            this.currentLand = land;
            this.currentPlot = plot;

            // Calculate precise coordinates
            let targetLat = parseFloat(land.latitude);
            let targetLng = parseFloat(land.longitude);
            const landPlots = this.plotsData.filter(p => p.land_id == land.id);

            if (plot) {
                const totalArea = parseFloat(land.area) || 100;
                const sideMeters = Math.sqrt(totalArea);
                const halfSide = sideMeters / 2;
                const deltaLat = halfSide / 111320;
                const deltaLng = halfSide / (111320 * Math.cos(targetLat * Math.PI / 180));

                const N = landPlots.length || 1;
                const cols = Math.ceil(Math.sqrt(N));
                const rows = Math.ceil(N / cols);
                const plotIndex = landPlots.findIndex(p => p.id == plot.id);

                if (plotIndex !== -1) {
                    const plotW = (deltaLng * 2) / cols;
                    const plotH = (deltaLat * 2) / rows;
                    const c = plotIndex % cols;
                    const r = Math.floor(plotIndex / cols);

                    const pMinLat = (targetLat + deltaLat) - ((r + 1) * plotH);
                    const pMaxLat = (targetLat + deltaLat) - (r * plotH);
                    const pMinLng = (targetLng - deltaLng) + (c * plotW);
                    const pMaxLng = (targetLng - deltaLng) + ((c + 1) * plotW);

                    targetLat = (pMinLat + pMaxLat) / 2;
                    targetLng = (pMinLng + pMaxLng) / 2;
                }
            }

            // Center local reference origin on the selected plot
            this.refLat = targetLat;
            this.refLng = targetLng;

            // Update TopNav title badge
            this.updateTopNavInfo(land, plot);

            // Show Animated Processing Modal (cartesiancs/map3d style)
            await this.runGenerationSequence(land, plot, targetLat, targetLng);
        }

        async runGenerationSequence(land, plot, lat, lng) {
            const modal = this.showProcessingModal(land, plot);

            // Step 1: Coordinates
            this.updateProcessingStep(1, 'active');
            await this.sleep(300);
            this.updateProcessingStep(1, 'completed');

            // Step 2: OpenStreetMap Data Fetch
            this.updateProcessingStep(2, 'active');
            
            // Generate synthetic fallback city immediately
            const syntheticCity = this.generateSyntheticCity(lat, lng);
            this.renderOSMData(syntheticCity);

            // Fetch live OSM structures centered on this plot
            const osmPromise = this.fetchOverpassData(lat, lng);
            await Promise.race([osmPromise, this.sleep(1200)]);
            this.updateProcessingStep(2, 'completed');

            // Step 3: Extrude Geometry & Roads
            this.updateProcessingStep(3, 'active');
            await this.sleep(350);
            this.updateProcessingStep(3, 'completed');

            // Step 4: Cultivate Plot Boundaries & Crops
            this.updateProcessingStep(4, 'active');
            this.renderPlotCentricScene(land, plot);
            await this.sleep(400);
            this.updateProcessingStep(4, 'completed');

            // Smoothly remove modal
            if (modal && modal.parentNode) {
                modal.style.transition = 'opacity 0.4s ease';
                modal.style.opacity = '0';
                setTimeout(() => {
                    if (modal.parentNode) modal.parentNode.removeChild(modal);
                }, 400);
            }

            // Frame camera onto plot
            this.animateCameraToPlot();
        }

        animateCameraToPlot() {
            if (!this.controls || !this.camera) return;
            this.controls.target.set(0, 2, 0);
            
            // Nice dramatic bird-eye 45 degree angle
            const startPos = this.camera.position.clone();
            const endPos = new THREE.Vector3(0, 48, 68);
            const startTime = performance.now();
            const duration = 1200;

            const updateCam = (now) => {
                const elapsed = now - startTime;
                const progress = Math.min(1, elapsed / duration);
                const ease = 1 - Math.pow(1 - progress, 3); // Ease out cubic

                this.camera.position.lerpVectors(startPos, endPos, ease);
                this.controls.update();

                if (progress < 1) {
                    requestAnimationFrame(updateCam);
                }
            };
            requestAnimationFrame(updateCam);
        }

        renderPlotCentricScene(land, selectedPlot) {
            // Clear prior plots
            while (this.selectedPlotGroup.children.length > 0) {
                this.selectedPlotGroup.remove(this.selectedPlotGroup.children[0]);
            }
            while (this.gardensGroup.children.length > 0) {
                this.gardensGroup.remove(this.gardensGroup.children[0]);
            }
            this.plotMeshesList = [];
            this.markersList = [];

            let landPlots = (this.plotsData && this.plotsData.length > 0)
                ? this.plotsData.filter(p => !land.id || p.land_id == land.id)
                : [];
            if (landPlots.length === 0 && this.plotsData && this.plotsData.length > 0) {
                landPlots = this.plotsData;
            }
            const totalArea = parseFloat(land.area) || 100;
            const sideMeters = Math.max(16, Math.sqrt(totalArea));

            // Render Garden Foundation Lot
            const gardenBedGeo = new THREE.BoxGeometry(sideMeters * 1.05, 1.2, sideMeters * 1.05);
            const gardenBedMat = new THREE.MeshStandardMaterial({
                color: 0x334155,
                roughness: 0.85
            });
            const gardenBed = new THREE.Mesh(gardenBedGeo, gardenBedMat);
            gardenBed.position.set(0, 0.6, 0);
            gardenBed.receiveShadow = true;
            this.gardensGroup.add(gardenBed);

            // Soil Surface
            const soilGeo = new THREE.PlaneGeometry(sideMeters, sideMeters);
            const soilMat = new THREE.MeshStandardMaterial({
                color: 0x22543d,
                roughness: 0.9,
                metalness: 0.1
            });
            const soil = new THREE.Mesh(soilGeo, soilMat);
            soil.rotation.x = -Math.PI / 2;
            soil.position.set(0, 1.25, 0);
            soil.receiveShadow = true;
            this.gardensGroup.add(soil);

            // Render Sub-partition Plots
            const N = landPlots.length || 1;
            const cols = Math.ceil(Math.sqrt(N));
            const rows = Math.ceil(N / cols);
            const plotW = sideMeters / cols;
            const plotH = sideMeters / rows;

            landPlots.forEach((p, idx) => {
                const c = idx % cols;
                const r = Math.floor(idx / cols);

                const defaultPosX = -sideMeters / 2 + (c + 0.5) * plotW;
                const defaultPosZ = -sideMeters / 2 + (r + 0.5) * plotH;

                const posX = (p.customX !== undefined) ? p.customX : defaultPosX;
                const posZ = (p.customZ !== undefined) ? p.customZ : defaultPosZ;

                const isSelected = selectedPlot ? (p.id == selectedPlot.id) : (idx === 0);
                const isAvail = p.status === 'available';
                const isOccupied = p.status === 'occupied';

                const plotGroup = new THREE.Group();
                plotGroup.position.set(posX, 1.3, posZ);
                plotGroup.userData = { plot: p, land: land, isPlot: true, plotIndex: idx };

                // 1. Raised Plot Bed Mesh
                const pBedW = plotW * 0.88;
                const pBedH = plotH * 0.88;
                const pHeight = isSelected ? 2.4 : 1.2;
                
                const plotBedGeo = new THREE.BoxGeometry(pBedW, pHeight, pBedH);
                const plotBedMat = new THREE.MeshStandardMaterial({
                    color: isSelected ? 0x15803d : (isAvail ? 0x166534 : 0x1e3a8a),
                    roughness: 0.5,
                    metalness: 0.2
                });
                const plotMesh = new THREE.Mesh(plotBedGeo, plotBedMat);
                plotMesh.position.y = pHeight / 2;
                plotMesh.castShadow = true;
                plotMesh.receiveShadow = true;
                plotMesh.userData = { plot: p, land: land, isPlot: true };
                plotGroup.add(plotMesh);
                this.plotMeshesList.push(plotMesh);

                // 2. Wireframe border highlight
                const wireGeo = new THREE.EdgesGeometry(plotBedGeo);
                const wireMat = new THREE.LineBasicMaterial({
                    color: isSelected ? 0x38bdf8 : (isAvail ? 0x4ade80 : 0x60a5fa),
                    linewidth: isSelected ? 3 : 1
                });
                const wire = new THREE.LineSegments(wireGeo, wireMat);
                wire.position.y = pHeight / 2;
                plotGroup.add(wire);

                // 3. Selected Plot Glowing Beacon & Crop Canopy
                if (isSelected) {
                    // Pulsing Ring at base
                    const ringGeo = new THREE.RingGeometry(pBedW * 0.55, pBedW * 0.72, 32);
                    const ringMat = new THREE.MeshBasicMaterial({
                        color: 0x38bdf8,
                        side: THREE.DoubleSide,
                        transparent: true,
                        opacity: 0.8
                    });
                    const pulseRing = new THREE.Mesh(ringGeo, ringMat);
                    pulseRing.rotation.x = -Math.PI / 2;
                    pulseRing.position.y = 0.05;
                    pulseRing.name = 'pulseRing';
                    plotGroup.add(pulseRing);

                    // Floating 3D Diamond Beacon
                    const beaconGeo = new THREE.OctahedronGeometry(2.5, 0);
                    const beaconMat = new THREE.MeshStandardMaterial({
                        color: 0x38bdf8,
                        emissive: 0x0284c7,
                        emissiveIntensity: 0.6,
                        roughness: 0.2,
                        metalness: 0.8
                    });
                    const beacon = new THREE.Mesh(beaconGeo, beaconMat);
                    beacon.position.y = pHeight + 7;
                    beacon.name = 'beacon';
                    plotGroup.add(beacon);

                    // Light column
                    const colGeo = new THREE.CylinderGeometry(0.3, 0.3, 7, 16);
                    const colMat = new THREE.MeshBasicMaterial({
                        color: 0x38bdf8,
                        transparent: true,
                        opacity: 0.5
                    });
                    const col = new THREE.Mesh(colGeo, colMat);
                    col.position.y = pHeight + 3.5;
                    plotGroup.add(col);
                }

                // 4. Crop Sprouts representation on the plot
                const plantCount = 4;
                for (let pi = 0; pi < plantCount; pi++) {
                    const plantGeo = new THREE.ConeGeometry(0.7, 1.8, 6);
                    const plantMat = new THREE.MeshStandardMaterial({
                        color: 0x4ade80,
                        roughness: 0.3
                    });
                    const plant = new THREE.Mesh(plantGeo, plantMat);
                    const subX = (pi % 2 === 0 ? -1 : 1) * (pBedW * 0.25);
                    const subZ = (pi < 2 ? -1 : 1) * (pBedH * 0.25);
                    plant.position.set(subX, pHeight + 0.9, subZ);
                    plotGroup.add(plant);
                }

                this.selectedPlotGroup.add(plotGroup);
            });
        }

        async fetchOverpassData(lat, lng) {
            const cacheKey = `${lat.toFixed(3)}_${lng.toFixed(3)}`;
            if (this.cachedAreas[cacheKey]) {
                this.renderOSMData(this.cachedAreas[cacheKey]);
                return;
            }

            const delta = 0.006; // ~350m neighborhood radius
            const south = lat - delta;
            const west = lng - delta;
            const north = lat + delta;
            const east = lng + delta;

            const query = `[out:json][timeout:15];(way["building"](${south},${west},${north},${east});way["highway"](${south},${west},${north},${east}););out body geom 250;`;
            const overpassUrl = `${OVERPASS_URL}?data=${encodeURIComponent(query)}`;

            try {
                const response = await fetch(overpassUrl, { headers: { 'Accept': 'application/json' } });
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                const data = await response.json();
                if (data.elements && data.elements.length > 0) {
                    this.cachedAreas[cacheKey] = data.elements;
                    this.renderOSMData(data.elements);
                }
            } catch (err) {
                console.warn('[Map3D] Overpass fetch deferred or offline; synthetic neighborhood active:', err);
            }
        }

        renderOSMData(elements) {
            // Clear existing
            while (this.buildingsGroup.children.length > 0) {
                this.buildingsGroup.remove(this.buildingsGroup.children[0]);
            }
            while (this.roadsGroup.children.length > 0) {
                this.roadsGroup.remove(this.roadsGroup.children[0]);
            }
            this.buildingsMeshList = [];

            const buildingPalette = [
                0xffffff, // Modern Clean White
                0xf8fafc, // Slate White
                0xe2e8f0, // Architectural Concrete
                0xdbeafe, // Soft Sky Blue Glass
                0xf1f5f9  // Pearl White
            ];

            elements.forEach((el) => {
                if (!el.geometry || el.geometry.length < 2) return;

                // ─── 1. Buildings ───
                if (el.tags && el.tags.building) {
                    const shapePoints = el.geometry.map((pt) => {
                        const p = this.project(pt.lat, pt.lon);
                        return new THREE.Vector2(p.x, -p.z); // Map to Shape XY where Y = -p.z
                    });

                    if (shapePoints.length < 3) return;

                    // Ensure closed loop
                    if (!shapePoints[0].equals(shapePoints[shapePoints.length - 1])) {
                        shapePoints.push(shapePoints[0].clone());
                    }

                    const shape = new THREE.Shape(shapePoints);
                    let height = parseFloat(el.tags.height || '');
                    const levels = parseFloat(el.tags['building:levels'] || '');

                    if (!isNaN(levels)) {
                        height = levels * 3.6;
                    } else if (isNaN(height) || height <= 0) {
                        height = 14 + (Math.abs(el.id || 1) % 28);
                    }

                    const extrudeSettings = {
                        steps: 1,
                        depth: Math.max(6, height),
                        bevelEnabled: false
                    };

                    const geo = new THREE.ExtrudeGeometry(shape, extrudeSettings);
                    geo.rotateX(-Math.PI / 2);
                    geo.computeVertexNormals();

                    const color = buildingPalette[Math.abs(el.id || 0) % buildingPalette.length];
                    const mat = new THREE.MeshStandardMaterial({
                        color: color,
                        roughness: 0.45,
                        metalness: 0.1,
                        flatShading: true
                    });

                    const mesh = new THREE.Mesh(geo, mat);
                    mesh.castShadow = true;
                    mesh.receiveShadow = true;
                    mesh.position.set(0, 0, 0);

                    // Add architectural edge lines
                    const edges = new THREE.EdgesGeometry(geo, 28);
                    const edgeMat = new THREE.LineBasicMaterial({
                        color: 0x94a3b8,
                        transparent: true,
                        opacity: 0.55
                    });
                    const edgeLines = new THREE.LineSegments(edges, edgeMat);
                    mesh.add(edgeLines);

                    mesh.userData = {
                        tags: el.tags,
                        height: height,
                        isBuilding: true
                    };

                    this.buildingsGroup.add(mesh);
                    this.buildingsMeshList.push(mesh);
                }

                // ─── 2. Highways / Roads ───
                else if (el.tags && el.tags.highway) {
                    const pts = el.geometry.map((pt) => {
                        const p = this.project(pt.lat, pt.lon);
                        return new THREE.Vector3(p.x, 0.35, p.z);
                    });

                    if (pts.length >= 2) {
                        const isPrimary = el.tags.highway === 'primary' || el.tags.highway === 'trunk' || el.tags.highway === 'secondary';
                        const lineGeo = new THREE.BufferGeometry().setFromPoints(pts);
                        const lineMat = new THREE.LineBasicMaterial({
                            color: isPrimary ? 0x475569 : 0x94a3b8,
                            linewidth: isPrimary ? 3 : 1
                        });
                        const line = new THREE.Line(lineGeo, lineMat);
                        this.roadsGroup.add(line);
                    }
                }
            });
        }

        generateSyntheticCity(centerLat, centerLng) {
            const synthetic = [];
            const orig = { lat: centerLat, lon: centerLng };

            // Generate surrounding road grid
            for (let i = -3; i <= 3; i++) {
                synthetic.push({
                    id: 1000 + i,
                    tags: { highway: (i === 0 ? 'primary' : 'residential') },
                    geometry: [
                        { lat: orig.lat + i * 0.0018, lon: orig.lon - 0.005 },
                        { lat: orig.lat + i * 0.0018, lon: orig.lon + 0.005 }
                    ]
                });
                synthetic.push({
                    id: 2000 + i,
                    tags: { highway: (i === 0 ? 'primary' : 'residential') },
                    geometry: [
                        { lat: orig.lat - 0.005, lon: orig.lon + i * 0.0018 },
                        { lat: orig.lat + 0.005, lon: orig.lon + i * 0.0018 }
                    ]
                });
            }

            // Generate surrounding buildings (leaving center lot for garden)
            for (let r = -2; r <= 2; r++) {
                for (let c = -2; c <= 2; c++) {
                    if (r === 0 && c === 0) continue; // Leave center space for garden
                    const bLat = orig.lat + (r * 0.0018) + 0.0005;
                    const bLon = orig.lon + (c * 0.0018) + 0.0005;
                    const w = 0.0004;
                    synthetic.push({
                        id: 30000 + (r + 5) * 100 + (c + 5),
                        tags: {
                            building: 'commercial',
                            name: `Urban Block ${r},${c}`,
                            'building:levels': Math.floor(4 + (Math.abs(r * 3 + c * 2) % 10))
                        },
                        geometry: [
                            { lat: bLat - w, lon: bLon - w },
                            { lat: bLat - w, lon: bLon + w },
                            { lat: bLat + w, lon: bLon + w },
                            { lat: bLat + w, lon: bLon - w },
                            { lat: bLat - w, lon: bLon - w }
                        ]
                    });
                }
            }

            return synthetic;
        }

        createTopNav() {
            let topnav = this.container.querySelector('.map3d-topnav');
            if (!topnav) {
                topnav = document.createElement('div');
                topnav.className = 'map3d-topnav';
                this.container.appendChild(topnav);
            }

            topnav.innerHTML = `
                <!-- Left: Return to 2D Map -->
                <div class="map3d-topnav-left">
                    <button type="button" class="map3d-back-btn" id="map3dBackBtn" title="Return to 2D Map">
                        <i class="bi bi-arrow-left"></i>
                        <span>Back to 2D Map</span>
                    </button>
                </div>

                <!-- Center: Active Plot & Garden Badge -->
                <div class="map3d-topnav-center">
                    <div class="map3d-plot-title-badge" id="map3dTopNavBadge">
                        <span class="map3d-status-dot"></span>
                        <span class="fw-bold" id="map3dTitleGarden">Garden 3D Space</span>
                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5" id="map3dTitlePlot" style="font-size:0.75rem;">All Plots</span>
                    </div>
                </div>

                <!-- Right: Lighting & Layer Controls -->
                <div class="map3d-topnav-right">
                    <div class="map3d-control-group">
                        <button type="button" class="map3d-nav-btn" id="map3dLightCycleBtn" title="Day / Sunset / Neon Night">
                            <i class="bi bi-brightness-high" id="map3dLightNavIcon"></i>
                        </button>
                        <button type="button" class="map3d-nav-btn active" id="map3dToggleBldBtn" title="Toggle Buildings">
                            <i class="bi bi-building"></i>
                        </button>
                        <button type="button" class="map3d-nav-btn active" id="map3dToggleRoadsBtn" title="Toggle Roads">
                            <i class="bi bi-signpost-split"></i>
                        </button>
                        <button type="button" class="map3d-nav-btn" id="map3dResetCamBtn" title="Reset Camera View">
                            <i class="bi bi-compass"></i>
                        </button>
                    </div>
                </div>
            `;

            // Bind TopNav buttons
            const backBtn = topnav.querySelector('#map3dBackBtn');
            if (backBtn) {
                backBtn.onclick = () => {
                    if (typeof this.onExit3D === 'function') {
                        this.onExit3D();
                    } else if (typeof window.toggle3DMapMode === 'function') {
                        window.toggle3DMapMode();
                    }
                };
            }

            const lightBtn = topnav.querySelector('#map3dLightCycleBtn');
            if (lightBtn) lightBtn.onclick = () => this.cycleLighting();

            const bldBtn = topnav.querySelector('#map3dToggleBldBtn');
            if (bldBtn) {
                bldBtn.onclick = () => {
                    this.buildingsGroup.visible = !this.buildingsGroup.visible;
                    bldBtn.classList.toggle('active', this.buildingsGroup.visible);
                };
            }

            const roadBtn = topnav.querySelector('#map3dToggleRoadsBtn');
            if (roadBtn) {
                roadBtn.onclick = () => {
                    this.roadsGroup.visible = !this.roadsGroup.visible;
                    roadBtn.classList.toggle('active', this.roadsGroup.visible);
                };
            }

            const resetBtn = topnav.querySelector('#map3dResetCamBtn');
            if (resetBtn) resetBtn.onclick = () => this.animateCameraToPlot();
        }

        updateTopNavInfo(land, plot) {
            const gardenTitleEl = this.container.querySelector('#map3dTitleGarden');
            const plotBadgeEl = this.container.querySelector('#map3dTitlePlot');

            if (gardenTitleEl) {
                gardenTitleEl.textContent = land ? (land.title || 'Selected Garden') : 'Garden 3D Space';
            }
            if (plotBadgeEl) {
                if (plot) {
                    const statusText = plot.status === 'available' ? 'Available' : 'Leased';
                    plotBadgeEl.textContent = `${plot.plot_number} • ${plot.area} m² (${statusText})`;
                    plotBadgeEl.className = plot.status === 'available' 
                        ? 'badge bg-success text-white rounded-pill px-2.5 py-0.5' 
                        : 'badge bg-primary text-white rounded-pill px-2.5 py-0.5';
                } else {
                    plotBadgeEl.textContent = `${land.area || 100} m² • Full Garden`;
                    plotBadgeEl.className = 'badge bg-success-subtle text-success rounded-pill px-2.5 py-0.5';
                }
            }
        }

        createHUD() {
            let hud = this.container.querySelector('.map3d-hud');
            if (!hud) {
                hud = document.createElement('div');
                hud.className = 'map3d-hud';
                this.container.appendChild(hud);
            }

            hud.innerHTML = `
                <!-- Building & Plot Inspection Popup Tooltip -->
                <div class="map3d-tooltip" id="map3dTooltip"></div>
            `;
        }

        showProcessingModal(land, plot) {
            let existing = this.container.querySelector('.map3d-processing-modal');
            if (existing && existing.parentNode) existing.parentNode.removeChild(existing);

            const modal = document.createElement('div');
            modal.className = 'map3d-processing-modal';

            const plotLabel = plot ? `${plot.plot_number} (${plot.area} m²)` : `${land.title}`;
            const coordsText = `${parseFloat(land.latitude).toFixed(4)}° N, ${parseFloat(land.longitude).toFixed(4)}° E`;

            modal.innerHTML = `
                <div class="map3d-processing-box">
                    <div class="map3d-radar-wrapper">
                        <div class="map3d-radar-ring"></div>
                        <div class="map3d-radar-ring"></div>
                        <div class="map3d-radar-core">
                            <i class="bi bi-box-fill"></i>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-1" style="font-size:1.15rem;letter-spacing:-0.2px;">Generating 3D Plot Map</h5>
                    <p class="text-xs mb-3 text-cyan" style="color:#38bdf8;">
                        <i class="bi bi-geo-alt-fill me-1"></i>${plotLabel} • ${coordsText}
                    </p>

                    <div class="map3d-step-list">
                        <div class="map3d-step-item" id="stepItem1">
                            <span class="map3d-step-icon"><i class="bi bi-check2"></i></span>
                            <span>Pinpoint plot boundary & coordinates</span>
                        </div>
                        <div class="map3d-step-item" id="stepItem2">
                            <span class="map3d-step-icon"><i class="bi bi-check2"></i></span>
                            <span>Query OpenStreetMap buildings & roads</span>
                        </div>
                        <div class="map3d-step-item" id="stepItem3">
                            <span class="map3d-step-icon"><i class="bi bi-check2"></i></span>
                            <span>Extrude 3D architectural geometry</span>
                        </div>
                        <div class="map3d-step-item" id="stepItem4">
                            <span class="map3d-step-icon"><i class="bi bi-check2"></i></span>
                            <span>Cultivate soil beds & 3D crop canopies</span>
                        </div>
                    </div>

                    <div class="progress" style="height: 6px; background: rgba(255,255,255,0.1); border-radius: 10px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="map3dGenProgress" role="progressbar" style="width: 25%"></div>
                    </div>
                </div>
            `;

            this.container.appendChild(modal);
            return modal;
        }

        updateProcessingStep(stepNumber, state) {
            const el = this.container.querySelector(`#stepItem${stepNumber}`);
            const bar = this.container.querySelector('#map3dGenProgress');
            if (el) {
                el.classList.remove('active', 'completed');
                el.classList.add(state);
            }
            if (bar) {
                bar.style.width = `${stepNumber * 25}%`;
            }
        }

        cycleLighting() {
            const navIcon = this.container.querySelector('#map3dLightNavIcon');
            if (this.activeTimeMode === 'day') {
                this.activeTimeMode = 'sunset';
                this.scene.background.setHex(0xfad2a8);
                this.scene.fog.color.setHex(0xfad2a8);
                this.dirLight.color.setHex(0xff7733);
                this.dirLight.intensity = 1.6;
                this.ambientLight.color.setHex(0xffaa77);
                if (navIcon) navIcon.className = 'bi bi-sunset';
            } else if (this.activeTimeMode === 'sunset') {
                this.activeTimeMode = 'night';
                this.scene.background.setHex(0x0a0f1d);
                this.scene.fog.color.setHex(0x0a0f1d);
                this.dirLight.color.setHex(0x38bdf8);
                this.dirLight.intensity = 0.5;
                this.ambientLight.color.setHex(0x1e293b);
                this.ambientLight.intensity = 0.6;
                if (navIcon) navIcon.className = 'bi bi-moon-stars';
            } else {
                this.activeTimeMode = 'day';
                this.scene.background.setHex(0xdce8f2);
                this.scene.fog.color.setHex(0xdce8f2);
                this.dirLight.color.setHex(0xfff7ed);
                this.dirLight.intensity = 1.25;
                this.ambientLight.color.setHex(0xffffff);
                this.ambientLight.intensity = 0.85;
                if (navIcon) navIcon.className = 'bi bi-brightness-high';
            }
        }

        repositionPlot(plotId, newX, newZ) {
            const plotGroup = this.selectedPlotGroup.children.find(
                g => g.userData && g.userData.plot && g.userData.plot.id == plotId
            );
            if (plotGroup) {
                plotGroup.position.x = newX;
                plotGroup.position.z = newZ;
                plotGroup.userData.plot.customX = newX;
                plotGroup.userData.plot.customZ = newZ;
            }
        }

        bindEvents() {
            let pointerDownPos = { x: 0, y: 0 };

            const onPointerDown = (event) => {
                if (event.button !== 0) return;
                const rect = this.renderer.domElement.getBoundingClientRect();
                this.mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
                this.mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
                pointerDownPos = { x: event.clientX, y: event.clientY };

                if (this.enablePlotDragging) {
                    this.raycaster.setFromCamera(this.mouse, this.camera);
                    const plotHits = this.raycaster.intersectObjects(this.plotMeshesList, false);
                    if (plotHits.length > 0) {
                        let group = plotHits[0].object;
                        while (group && (!group.userData || !group.userData.isPlot) && group.parent) {
                            group = group.parent;
                        }
                        if (group && group.userData && group.userData.isPlot) {
                            this.isDraggingPlot = true;
                            this.draggedPlotGroup = group;
                            if (this.controls) this.controls.enabled = false;

                            if (this.raycaster.ray.intersectPlane(this.dragPlane, this.planeIntersect)) {
                                this.dragOffset.copy(group.position).sub(this.planeIntersect);
                            }
                            this.container.style.cursor = 'grabbing';
                        }
                    }
                }
            };

            const onPointerMove = (event) => {
                const rect = this.renderer.domElement.getBoundingClientRect();
                this.mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
                this.mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
                this.raycaster.setFromCamera(this.mouse, this.camera);

                if (this.isDraggingPlot && this.draggedPlotGroup) {
                    if (this.raycaster.ray.intersectPlane(this.dragPlane, this.planeIntersect)) {
                        const targetPos = this.planeIntersect.clone().add(this.dragOffset);
                        const totalArea = parseFloat(this.currentLand ? this.currentLand.area : 100) || 100;
                        const sideMeters = Math.max(16, Math.sqrt(totalArea));
                        const bound = (sideMeters / 2) * 0.90;

                        const clampedX = Math.max(-bound, Math.min(bound, targetPos.x));
                        const clampedZ = Math.max(-bound, Math.min(bound, targetPos.z));

                        this.draggedPlotGroup.position.x = clampedX;
                        this.draggedPlotGroup.position.z = clampedZ;
                        this.draggedPlotGroup.userData.plot.customX = clampedX;
                        this.draggedPlotGroup.userData.plot.customZ = clampedZ;

                        if (typeof this.onPlotRepositioned === 'function') {
                            this.onPlotRepositioned(this.draggedPlotGroup.userData.plot, clampedX, clampedZ, false);
                        }
                    }
                    return;
                }

                this.checkIntersections();
            };

            const onPointerUp = (event) => {
                if (this.isDraggingPlot) {
                    if (typeof this.onPlotRepositioned === 'function' && this.draggedPlotGroup) {
                        this.onPlotRepositioned(
                            this.draggedPlotGroup.userData.plot,
                            this.draggedPlotGroup.position.x,
                            this.draggedPlotGroup.position.z,
                            true
                        );
                    }
                    this.isDraggingPlot = false;
                    this.draggedPlotGroup = null;
                    if (this.controls) this.controls.enabled = true;
                    this.container.style.cursor = 'default';
                }
            };

            const onClick = (event) => {
                const dist = Math.hypot(event.clientX - pointerDownPos.x, event.clientY - pointerDownPos.y);
                if (dist > 5) return; // Ignore drag clicks
                const rect = this.renderer.domElement.getBoundingClientRect();
                this.mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
                this.mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
                this.handleObjectClick();
            };

            const onResize = () => {
                if (!this.container || !this.camera || !this.renderer) return;
                const width = this.container.clientWidth;
                const height = this.container.clientHeight || 500;
                this.camera.aspect = width / height;
                this.camera.updateProjectionMatrix();
                this.renderer.setSize(width, height);
            };

            this.renderer.domElement.addEventListener('pointerdown', onPointerDown);
            this.renderer.domElement.addEventListener('pointermove', onPointerMove);
            window.addEventListener('pointerup', onPointerUp);
            this.renderer.domElement.addEventListener('click', onClick);
            window.addEventListener('resize', onResize);

            this.cleanEventListeners = () => {
                this.renderer.domElement.removeEventListener('pointerdown', onPointerDown);
                this.renderer.domElement.removeEventListener('pointermove', onPointerMove);
                window.removeEventListener('pointerup', onPointerUp);
                this.renderer.domElement.removeEventListener('click', onClick);
                window.removeEventListener('resize', onResize);
            };
        }

        checkIntersections() {
            this.raycaster.setFromCamera(this.mouse, this.camera);

            // 1. Check Plots
            const plotHits = this.raycaster.intersectObjects(this.plotMeshesList, false);
            if (plotHits.length > 0) {
                this.container.style.cursor = 'pointer';
                return;
            }

            // 2. Check Buildings
            const buildingHits = this.raycaster.intersectObjects(this.buildingsMeshList, false);
            if (buildingHits.length > 0) {
                this.container.style.cursor = 'pointer';
                return;
            }

            this.container.style.cursor = 'default';
        }

        handleObjectClick() {
            this.raycaster.setFromCamera(this.mouse, this.camera);

            // 1. Check Plot Bed Click
            const plotHits = this.raycaster.intersectObjects(this.plotMeshesList, false);
            if (plotHits.length > 0) {
                const mesh = plotHits[0].object;
                if (mesh.userData && mesh.userData.plot) {
                    this.showPlotPopup(mesh.userData.land, mesh.userData.plot);
                    return;
                }
            }

            // 2. Check Building Click (shows OSM Information card like cartesiancs/map3d)
            const bldHits = this.raycaster.intersectObjects(this.buildingsMeshList, false);
            if (bldHits.length > 0) {
                const bld = bldHits[0].object;
                this.showBuildingPopup(bld.userData);
                return;
            }

            this.hideTooltip();
        }

        repositionPlot(plotId, newX, newZ) {
            if (!this.selectedPlotGroup) return;
            this.selectedPlotGroup.traverse((child) => {
                if (child.isGroup && child.userData && child.userData.isPlot && child.userData.plot) {
                    if (child.userData.plot.id == plotId) {
                        child.position.x = newX;
                        child.position.z = newZ;
                        child.userData.plot.customX = newX;
                        child.userData.plot.customZ = newZ;
                    }
                }
            });
        }

        showPlotPopup(land, plot) {
            const tooltip = this.container.querySelector('#map3dTooltip');
            if (!tooltip) return;

            const isAvail = plot.status === 'available';
            const statusBadge = isAvail 
                ? '<span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1">Available for Lease</span>'
                : '<span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1">Leased (Occupied)</span>';

            const cropBadge = plot.crop ? `<span class="fw-bold text-dark">${plot.crop}</span>` : 'Mixed Vegetables';

            tooltip.innerHTML = `
                <div class="map3d-popup-card">
                    <div class="d-flex align-items-center justify-content-between pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-1.5">
                            <i class="bi bi-grid-3x3-gap-fill text-success"></i>
                            <strong class="text-dark" style="font-size:0.95rem;">${plot.plot_number}</strong>
                        </div>
                        <button type="button" class="btn-close text-xs" onclick="document.getElementById('map3dTooltip').style.display='none'"></button>
                    </div>

                    <div class="mt-2 mb-2">
                        ${statusBadge}
                        <span class="badge bg-light text-secondary border rounded-pill px-2 py-1 ms-1 text-xs">${plot.area} m²</span>
                    </div>

                    <div class="text-xs text-muted mb-2">
                        <strong>Garden:</strong> ${land.title}
                    </div>
                    <div class="text-xs text-muted mb-3">
                        <strong>Current / Permitted Crop:</strong> ${cropBadge}
                    </div>

                    <button type="button" class="btn btn-drive-primary btn-sm rounded-pill w-100 py-1.5 text-xs" onclick="if(window.viewLandPlotsDetail) window.viewLandPlotsDetail(${land.id});">
                        <i class="bi bi-info-circle me-1"></i>View Full Plot Details
                    </button>
                </div>
            `;
            tooltip.style.display = 'block';
        }

        showBuildingPopup(userData) {
            const tooltip = this.container.querySelector('#map3dTooltip');
            if (!tooltip) return;

            const tags = userData.tags || {};
            const height = userData.height ? `${Math.round(userData.height)} m` : 'N/A';
            const levels = tags['building:levels'] || '1-3';
            const name = tags.name || tags['name:en'] || 'City Building';
            const type = tags.building || 'Commercial / Mixed';

            tooltip.innerHTML = `
                <div class="map3d-popup-card">
                    <div class="d-flex align-items-center justify-content-between pb-1 border-bottom">
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-0.5 text-xs">
                            <i class="bi bi-building me-1"></i>3D OSM Geometry
                        </span>
                        <button type="button" class="btn-close text-xs" onclick="document.getElementById('map3dTooltip').style.display='none'"></button>
                    </div>
                    <h6 class="fw-bold mt-2 mb-1 text-dark">${name}</h6>
                    <div class="text-xs text-muted mb-2">Structure Type: <strong class="text-dark">${type}</strong></div>
                    <div class="d-flex justify-content-between py-1 border-top text-xs">
                        <span class="text-muted">Estimated Height:</span>
                        <span class="fw-semibold text-dark">${height}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-top text-xs">
                        <span class="text-muted">Building Levels:</span>
                        <span class="fw-semibold text-dark">${levels} floors</span>
                    </div>
                </div>
            `;
            tooltip.style.display = 'block';
        }

        hideTooltip() {
            const tooltip = this.container.querySelector('#map3dTooltip');
            if (tooltip) tooltip.style.display = 'none';
        }

        animate() {
            this.animationFrameId = requestAnimationFrame(this.animate);

            // Beacon rotation and pulsing
            const time = performance.now() * 0.002;
            const beacon = this.selectedPlotGroup.getObjectByName('beacon');
            const pulseRing = this.selectedPlotGroup.getObjectByName('pulseRing');

            if (beacon) {
                beacon.rotation.y = time * 1.2;
                beacon.position.y = 8 + Math.sin(time * 2.5) * 1.2;
            }
            if (pulseRing) {
                pulseRing.scale.setScalar(1 + Math.sin(time * 3) * 0.08);
            }

            if (this.controls) {
                this.controls.update();
            }

            if (this.renderer && this.scene && this.camera) {
                this.renderer.render(this.scene, this.camera);
            }
        }

        sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        destroy() {
            if (this.animationFrameId) {
                cancelAnimationFrame(this.animationFrameId);
            }
            if (this.cleanEventListeners) {
                this.cleanEventListeners();
            }
            if (this.renderer && this.renderer.domElement && this.renderer.domElement.parentNode) {
                this.renderer.domElement.parentNode.removeChild(this.renderer.domElement);
            }
            this.isInitialized = false;
        }
    }

    // Export globally
    window.Map3DEngine = Map3DEngine;

})(window);
