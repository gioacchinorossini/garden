<?php
require_once __DIR__ . '/db.php';

/**
 * Get all lands from MySQL database with consistent structure
 */
function get_all_lands($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM `lands` ORDER BY `id` DESC");
        $db_lands = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formatted = [];
        foreach ($db_lands as $l) {
            $allowed_seeds = json_decode($l['allowed_seeds'] ?? '[]', true);
            if (!is_array($allowed_seeds)) {
                $allowed_seeds = !empty($l['allowed_seeds']) ? array_map('trim', explode(',', $l['allowed_seeds'])) : ['Vegetables'];
            }

            $amenities = json_decode($l['amenities'] ?? '[]', true);
            if (!is_array($amenities)) {
                $amenities = !empty($l['amenities']) ? array_map('trim', explode(',', $l['amenities'])) : [];
            }

            $formatted[] = [
                'id' => (int)$l['id'],
                'owner_id' => (int)($l['owner_id'] ?? 0),
                'title' => $l['title'] ?? 'Untitled Land',
                'title_type' => $l['title_type'] ?? '',
                'title_number' => $l['title_number'] ?? '',
                'rod_name' => $l['rod_name'] ?? '',
                'epeb_type' => '',
                'epeb_no' => '',
                'title_document_path' => $l['title_document_path'] ?? '',
                'oct_document_path' => $l['oct_document_path'] ?? ($l['title_document_path'] ?? ''),
                'lra_receipt_path' => '',
                'is_lra_verified' => 0,
                'landowner' => !empty($l['landowner_name']) ? $l['landowner_name'] : 'Landowner',
                'address' => $l['address'] ?? '',
                'latitude' => (float)($l['latitude'] ?? 14.5995),
                'longitude' => (float)($l['longitude'] ?? 120.9842),
                'area' => (float)($l['area_sqm'] ?? $l['area'] ?? 0),
                'status' => $l['status'] ?? 'pending',
                'reason' => $l['rejection_reason'] ?? ($l['reason'] ?? ''),
                'description' => $l['description'] ?? '',
                'crops' => $allowed_seeds,
                'allowed_seeds' => $allowed_seeds,
                'amenities' => $amenities,
                'total_plots' => (int)($l['plot_count'] ?? 4),
                'occupied_plots' => 0,
                'has_3d_view' => (int)($l['has_3d_view'] ?? 0),
                'polygon' => $l['polygon'] ?? '',
                'rental_price_per_year' => (float)($l['rental_price_per_year'] ?? 110000.00),
                'created_at' => $l['created_at'] ?? ''
            ];
        }
        return $formatted;
    } catch (\Exception $e) {
        error_log("Error fetching lands: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all partition plots from MySQL database
 */
function get_all_plots($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM `plots` ORDER BY `id` ASC");
        $db_plots = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formatted = [];
        foreach ($db_plots as $p) {
            $plot_seeds = json_decode($p['allowed_seeds'] ?? '[]', true);
            if (!is_array($plot_seeds)) {
                $plot_seeds = !empty($p['allowed_seeds']) ? array_map('trim', explode(',', $p['allowed_seeds'])) : ['Tomato'];
            }

            $formatted[] = [
                'id' => (int)$p['id'],
                'land_id' => (int)$p['land_id'],
                'plot_number' => $p['plot_number'] ?? 'Plot A-1',
                'area' => (float)($p['area_sqm'] ?? $p['area'] ?? 50.0),
                'rental_price_per_year' => (float)($p['rental_price_per_year'] ?? 110000.00),
                'status' => $p['status'] ?? 'available',
                'crop' => !empty($p['crop']) ? $p['crop'] : '',
                'crop_icon' => !empty($p['crop_icon']) ? $p['crop_icon'] : '',
                'crops' => $plot_seeds,
                'farmer_name' => $p['farmer_name'] ?? '',
                'lease_end' => $p['lease_end'] ?? ''
            ];
        }
        return $formatted;
    } catch (\Exception $e) {
        error_log("Error fetching plots: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all gardener plot requests from MySQL database
 */
function get_all_requests($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT r.*, l.latitude, l.longitude, l.address as land_address, l.landowner_name
            FROM `requests` r
            LEFT JOIN `lands` l ON r.land_id = l.id
            ORDER BY r.id DESC
        ");
        $db_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formatted = [];
        foreach ($db_requests as $r) {
            $formatted[] = [
                'id' => (int)$r['id'],
                'land_id' => (int)($r['land_id'] ?? 0),
                'plot_id' => (int)($r['plot_id'] ?? 0),
                'gardener' => $r['gardener_name'] ?? 'Gardener',
                'land_title' => $r['land_title'] ?? 'Garden Plot',
                'landowner' => !empty($r['landowner_name']) ? $r['landowner_name'] : 'Landowner',
                'plot_num' => $r['plot_number'] ?? 'Plot',
                'purpose' => $r['purpose'] ?? ($r['message'] ?? ''),
                'duration' => $r['requested_duration'] ?? '1 year',
                'status' => $r['status'] ?? 'pending',
                'offered_rental_rate' => !empty($r['offered_rental_rate']) ? (float)$r['offered_rental_rate'] : 110000.00,
                'counter_rental_rate' => !empty($r['counter_rental_rate']) ? (float)$r['counter_rental_rate'] : null,
                'counter_notes' => $r['counter_notes'] ?? '',
                'counter_at' => $r['counter_at'] ?? '',
                'rental_duration' => $r['rental_duration'] ?? ($r['requested_duration'] ?? '1 year'),
                'terms_conditions' => $r['terms_conditions'] ?? '',
                'email' => $r['email'] ?? '',
                'phone' => $r['phone'] ?? '',
                'notes' => $r['response_notes'] ?? '',
                'requested_at' => $r['created_at'] ?? '',
                'latitude' => (float)($r['latitude'] ?? 0),
                'longitude' => (float)($r['longitude'] ?? 0),
                'land_address' => $r['land_address'] ?? ''
            ];
        }
        return $formatted;
    } catch (\Exception $e) {
        error_log("Error fetching requests: " . $e->getMessage());
        return [];
    }
}

/**
 * Get standard list of site amenities & utilities with vector icons
 */
function get_standard_amenities($base_path = '') {
    $clean_base = !empty($base_path) ? rtrim($base_path, '/') . '/' : '';
    return [
        [
            'value' => 'Water Source',
            'label' => 'Water Source',
            'icon' => $clean_base . 'assets/amenity-icons/water-source.svg',
            'desc' => 'Well, tap, or continuous fresh water supply'
        ],
        [
            'value' => 'Electricity',
            'label' => 'Electricity',
            'icon' => $clean_base . 'assets/amenity-icons/electricity.svg',
            'desc' => 'Grid power or solar electrical outlets'
        ],
        [
            'value' => 'Irrigation System',
            'label' => 'Irrigation System',
            'icon' => $clean_base . 'assets/amenity-icons/irrigation.svg',
            'desc' => 'Drip, sprinkler, or furrow irrigation lines'
        ],
        [
            'value' => 'Tool Storage',
            'label' => 'Tool Storage',
            'icon' => $clean_base . 'assets/amenity-icons/tool-storage.svg',
            'desc' => 'Lockable garden shed or tool locker'
        ],
        [
            'value' => 'Perimeter Fencing',
            'label' => 'Perimeter Fencing',
            'icon' => $clean_base . 'assets/amenity-icons/perimeter-fencing.svg',
            'desc' => 'Secure boundary fencing or wall'
        ],
        [
            'value' => 'Compost Area',
            'label' => 'Compost Area',
            'icon' => $clean_base . 'assets/amenity-icons/compost-area.svg',
            'desc' => 'Designated organic waste & composting bin'
        ],
        [
            'value' => 'Restroom / Wash Area',
            'label' => 'Restroom / Wash Area',
            'icon' => $clean_base . 'assets/amenity-icons/restroom.svg',
            'desc' => 'On-site sanitation, sink, or comfort room'
        ],
        [
            'value' => 'Solar Power',
            'label' => 'Solar Power',
            'icon' => $clean_base . 'assets/amenity-icons/solar-power.svg',
            'desc' => 'Eco-friendly solar panels and battery setup'
        ]
    ];
}

/**
 * Resolve matching vector icon SVG path for an amenity or utility
 */
function resolve_amenity_icon($name, $base_path = '') {
    $lower = strtolower(trim((string)$name));
    $file = 'general.svg';

    if (strpos($lower, 'water') !== false || strpos($lower, 'well') !== false || strpos($lower, 'drain') !== false || strpos($lower, 'spigot') !== false || strpos($lower, 'tap') !== false) {
        $file = 'water-source.svg';
    } elseif (strpos($lower, 'elect') !== false || strpos($lower, 'power') !== false || strpos($lower, 'grid') !== false || strpos($lower, 'light') !== false) {
        $file = 'electricity.svg';
    } elseif (strpos($lower, 'irrig') !== false || strpos($lower, 'sprinkler') !== false || strpos($lower, 'drip') !== false || strpos($lower, 'hose') !== false) {
        $file = 'irrigation.svg';
    } elseif (strpos($lower, 'tool') !== false || strpos($lower, 'shed') !== false || strpos($lower, 'storage') !== false || strpos($lower, 'equip') !== false || strpos($lower, 'barn') !== false) {
        $file = 'tool-storage.svg';
    } elseif (strpos($lower, 'fenc') !== false || strpos($lower, 'perim') !== false || strpos($lower, 'wall') !== false || strpos($lower, 'gate') !== false || strpos($lower, 'secur') !== false) {
        $file = 'perimeter-fencing.svg';
    } elseif (strpos($lower, 'compost') !== false || strpos($lower, 'fertil') !== false || strpos($lower, 'soil') !== false || strpos($lower, 'organic') !== false || strpos($lower, 'mulch') !== false) {
        $file = 'compost-area.svg';
    } elseif (strpos($lower, 'restroom') !== false || strpos($lower, 'wash') !== false || strpos($lower, 'toilet') !== false || strpos($lower, 'cr') !== false || strpos($lower, 'comfort') !== false || strpos($lower, 'bath') !== false || strpos($lower, 'sink') !== false) {
        $file = 'restroom.svg';
    } elseif (strpos($lower, 'solar') !== false || strpos($lower, 'sun') !== false || strpos($lower, 'panel') !== false) {
        $file = 'solar-power.svg';
    }

    $clean_base = !empty($base_path) ? rtrim($base_path, '/') . '/' : '';
    return $clean_base . 'assets/amenity-icons/' . $file;
}
