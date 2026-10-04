<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . " - Idle Land Gardening" : "Idle Land for Community Gardening System"; ?></title>
    <link rel="icon" type="image/jpeg" href="<?php echo isset($base_path) ? $base_path : ''; ?>logo.jpeg">
    <?php $bp = isset($base_path) ? $base_path : ''; ?>
    <!-- Fonts (Local & Web Fallback) -->
    <link rel="stylesheet" href="<?php echo $bp; ?>assets/vendor/fonts/fonts.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&family=Nunito:wght@400;600;700;800;900&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="<?php echo $bp; ?>assets/vendor/bootstrap/bootstrap.min.css">
    <!-- Tailwind CSS -->
    <script src="<?php echo $bp; ?>assets/vendor/tailwind/tailwind.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        drive: {
                            canvas: "#F7F2E9",
                            surface: "#FFFDF9",
                            "surface-hover": "#F5EFE4",
                            "surface-active": "#EAE0D3",
                            "surface-selected": "#EFE6DA",
                            primary: "#7FA668",
                            "primary-hover": "#6F9557",
                            "primary-text": "#FFFDF9",
                            "text-main": "#3E2B1E",
                            "text-sub": "#6B5446",
                            "text-muted": "#968172",
                            border: "#785D4D",
                            "border-subtle": "#DFCFC2",
                        },
                        cs: {
                            cocoa: "#785D4D",
                            "cocoa-dark": "#4A3528",
                            parchment: "#F7F2E9",
                            cream: "#FFFDF9",
                            matcha: "#BBD6B8",
                            "matcha-dark": "#7FA668",
                            coral: "#E2735D",
                            honey: "#F4B342",
                            slot: "#F7EFE6",
                            dashed: "#DFCFC2",
                        }
                    },
                    borderRadius: {
                        '3xl': '24px',
                        '4xl': '28px',
                    },
                    fontFamily: {
                        sans: ['"Quicksand"', '"Nunito"', '"Outfit"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="<?php echo $bp; ?>assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <!-- Lucide Icons -->
    <script src="<?php echo $bp; ?>assets/vendor/lucide/lucide.min.js"></script>
    <!-- Leaflet.js Map CSS & JS -->
    <link rel="stylesheet" href="<?php echo $bp; ?>assets/vendor/leaflet/leaflet.css">
    <script src="<?php echo $bp; ?>assets/vendor/leaflet/leaflet.js"></script>
    <!-- Three.js & OrbitControls for 3D City & Garden Map View -->
    <script src="<?php echo $bp; ?>assets/vendor/three/three.min.js"></script>
    <script src="<?php echo $bp; ?>assets/vendor/three/OrbitControls.js"></script>
    <!-- Custom Design System Styles -->
    <link rel="stylesheet" href="<?php echo $bp; ?>assets/css/style.css?v=<?php echo time(); ?>">
    <script>
        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    </script>
    <style>
        /* Small adjustments to integrate with Bootstrap */
        .leaflet-container {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>
<body>
    <div class="app-container">
