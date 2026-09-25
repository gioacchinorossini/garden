<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check for simple demo login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'login') {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        // Simple authentication routing for demo
        if ($email === 'admin@garden.com') {
            $_SESSION['active_role'] = 'admin';
            $_SESSION['user_name'] = 'System Administrator';
            $_SESSION['user_id'] = 1;
            header("Location: admin/dashboard.php");
            exit;
        } elseif ($email === 'landowner@garden.com') {
            $_SESSION['active_role'] = 'landowner';
            $_SESSION['user_name'] = 'John Landowner';
            $_SESSION['user_id'] = 2;
            header("Location: landowner/dashboard.php");
            exit;
        } elseif ($email === 'gardener@garden.com') {
            $_SESSION['active_role'] = 'gardener';
            $_SESSION['user_name'] = 'Mary Gardener';
            $_SESSION['user_id'] = 3;
            header("Location: gardener/dashboard.php");
            exit;
        } else {
            // Default check (fallback to admin for demo)
            $_SESSION['active_role'] = 'admin';
            $_SESSION['user_name'] = 'Demo User';
            $_SESSION['user_id'] = 1;
            header("Location: admin/dashboard.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in - Idle Land Gardening</title>
    <link rel="icon" type="image/jpeg" href="logo.jpeg">
    <!-- Fonts (Local & Web Fallback) -->
    <link rel="stylesheet" href="assets/vendor/fonts/fonts.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <!-- Tailwind CSS -->
    <script src="assets/vendor/tailwind/tailwind.js"></script>
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
                        sans: ['Outfit', 'Inter', 'Roboto', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>

<body
    class="bg-drive-canvas text-drive-text-main font-sans flex items-center justify-center min-h-screen w-screen m-0 p-4">

    <div class="bg-white border border-drive-border rounded-3xl p-8 max-w-[450px] w-full shadow-lg">
        <!-- Google-style Logo -->
        <div class="flex justify-center items-center gap-2.5 mb-3">
            <img src="logo.jpeg" alt="IdleLand Logo" class="h-12 w-12 object-contain rounded-full shadow-sm">
            <span class="text-3xl font-bold font-['Outfit'] tracking-tight text-drive-primary">Idle<span
                    class="text-drive-text-main">Land</span></span>
        </div>

        <h2 class="text-2xl font-semibold text-center text-drive-text-main mb-1">Sign in</h2>
        <p class="text-center text-drive-text-muted mb-6 text-sm">Sign in to your account</p>

        <!-- Sign-in Form -->
        <form id="signinForm" action="index.php" method="POST" class="needs-validation m-0" novalidate>
            <input type="hidden" name="action" value="login">

            <div class="mb-4">
                <label for="email"
                    class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-2">Email</label>
                <input type="email"
                    class="w-full bg-white border border-drive-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-drive-primary focus:ring-1 focus:ring-drive-primary transition-all text-drive-text-main placeholder-drive-text-muted"
                    id="email" name="email" placeholder="email@example.com" required>
                <div class="invalid-feedback text-xs mt-1">Please enter a valid email address.</div>
            </div>

            <div class="mb-6">
                <div class="flex justify-between items-center mb-2">
                    <label for="password"
                        class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider">Password</label>
                    <a href="#"
                        class="text-xs font-semibold text-drive-primary hover:underline text-decoration-none">Forgot
                        password?</a>
                </div>
                <input type="password"
                    class="w-full bg-white border border-drive-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-drive-primary focus:ring-1 focus:ring-drive-primary transition-all text-drive-text-main placeholder-drive-text-muted"
                    id="password" name="password" placeholder="Enter password" required>
                <div class="invalid-feedback text-xs mt-1">Please enter your password.</div>
            </div>

            <!-- Role Helper Tabs / Quick 1-Click Sign-in Grid -->
            <div class="p-3.5 bg-drive-canvas border border-drive-border rounded-2xl mb-6">
                <div class="flex items-center justify-between mb-2.5">
                    <span class="text-[10px] font-bold text-drive-text-sub uppercase tracking-wider">Quick Sign
                        In</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button"
                        class="flex flex-col items-center justify-center py-2.5 px-2 bg-white hover:bg-drive-surface-hover hover:border-drive-primary border border-drive-border rounded-xl text-center transition-all group shadow-xs active:scale-95 cursor-pointer"
                        onclick="quickSignIn('admin@garden.com')" title="Sign in as Administrator">
                        <div
                            class="w-8 h-8 rounded-full bg-emerald-50 text-drive-primary flex items-center justify-center mb-1.5 group-hover:bg-drive-primary group-hover:text-white transition-colors">
                            <i class="bi bi-shield-lock-fill text-sm"></i>
                        </div>
                        <span class="text-xs font-semibold text-drive-text-main">Admin</span>
                    </button>
                    <button type="button"
                        class="flex flex-col items-center justify-center py-2.5 px-2 bg-white hover:bg-drive-surface-hover hover:border-drive-primary border border-drive-border rounded-xl text-center transition-all group shadow-xs active:scale-95 cursor-pointer"
                        onclick="quickSignIn('landowner@garden.com')" title="Sign in as Landowner">
                        <div
                            class="w-8 h-8 rounded-full bg-emerald-50 text-drive-primary flex items-center justify-center mb-1.5 group-hover:bg-drive-primary group-hover:text-white transition-colors">
                            <i class="bi bi-house-door-fill text-sm"></i>
                        </div>
                        <span class="text-xs font-semibold text-drive-text-main">Landowner</span>
                    </button>
                    <button type="button"
                        class="flex flex-col items-center justify-center py-2.5 px-2 bg-white hover:bg-drive-surface-hover hover:border-drive-primary border border-drive-border rounded-xl text-center transition-all group shadow-xs active:scale-95 cursor-pointer"
                        onclick="quickSignIn('gardener@garden.com')" title="Sign in as Gardener">
                        <div
                            class="w-8 h-8 rounded-full bg-emerald-50 text-drive-primary flex items-center justify-center mb-1.5 group-hover:bg-drive-primary group-hover:text-white transition-colors">
                            <i class="bi bi-flower1 text-sm"></i>
                        </div>
                        <span class="text-xs font-semibold text-drive-text-main">Gardener</span>
                    </button>
                </div>
            </div>

            <div class="flex justify-between items-center">
                <a href="create_account.php"
                    class="text-xs font-semibold text-drive-primary hover:underline text-decoration-none">Create
                    account</a>
                <button type="submit"
                    class="bg-drive-primary hover:bg-drive-primary-hover active:scale-95 text-white font-semibold text-sm px-6 py-2.5 rounded-full shadow-md transition-all">Next</button>
            </div>
        </form>
    </div>

    <script>
        function quickSignIn(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password123';
            document.getElementById('signinForm').submit();
        }

        function fillForm(email) {
            quickSignIn(email);
        }

        // Bootstrap Validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
    <script src="assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
</body>

</html>