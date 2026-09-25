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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                            canvas: "#eef5f0",
                            surface: "#ffffff",
                            "surface-hover": "#e3efe7",
                            "surface-active": "#cbeed6",
                            "surface-selected": "#e8f7ec",
                            primary: "#198754",
                            "primary-hover": "#146c43",
                            "primary-text": "#0f5132",
                            "text-main": "#191c1a",
                            "text-sub": "#404943",
                            "text-muted": "#707973",
                            border: "#e1e3e1",
                        }
                    },
                    borderRadius: {
                        '3xl': '24px',
                        '4xl': '28px',
                    },
                    fontFamily: {
                        sans: ['"Google Sans"', 'Outfit', 'Inter', 'Roboto', 'sans-serif'],
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
    <style>
        /* Small adjustments to integrate with Bootstrap */
        .leaflet-container {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>
<body>
    <div class="app-container">
