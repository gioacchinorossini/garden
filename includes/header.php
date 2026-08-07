<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . " - Idle Land Gardening" : "Idle Land for Community Gardening System"; ?></title>
    <!-- Google Fonts (Outfit & Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/style.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!-- Bootstrap CSS alternative via cdnjs to make sure it loads standard styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Tailwind CSS Play CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Leaflet.js Map CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <!-- Custom Design System Styles -->
    <link rel="stylesheet" href="<?php echo isset($base_path) ? $base_path : ''; ?>assets/css/style.css">
    <style>
        /* Small adjustments to integrate with Bootstrap */
        .leaflet-container {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>
<body>
    <div class="app-container">
