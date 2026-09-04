<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $role = trim($_POST['role'] ?? 'gardener');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($email) || empty($password)) {
        $error_message = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match. Please check and try again.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        // Initialize session mock users if empty
        if (!isset($_SESSION['mock_users']) || !is_array($_SESSION['mock_users'])) {
            $_SESSION['mock_users'] = [
                ['id' => 1, 'name' => 'System Administrator', 'email' => 'admin@garden.com', 'role' => 'admin', 'phone' => '09170000000', 'status' => 'active'],
                ['id' => 2, 'name' => 'John Landowner', 'email' => 'landowner@garden.com', 'role' => 'landowner', 'phone' => '09171112222', 'status' => 'active'],
                ['id' => 3, 'name' => 'Mary Gardener', 'email' => 'gardener@garden.com', 'role' => 'gardener', 'phone' => '09173334444', 'status' => 'active']
            ];
        }

        // Check if email exists
        $exists = false;
        foreach ($_SESSION['mock_users'] as $u) {
            if (strtolower($u['email']) === strtolower($email)) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            $error_message = 'An account with this email address already exists. Please sign in instead.';
        } else {
            $new_user_id = count($_SESSION['mock_users']) + 1;
            $new_user = [
                'id' => $new_user_id,
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'phone' => $phone,
                'status' => 'active'
            ];

            $_SESSION['mock_users'][] = $new_user;

            // Auto log in newly registered user
            $_SESSION['active_role'] = $role;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_id'] = $new_user_id;

            // Redirect to appropriate dashboard
            if ($role === 'landowner') {
                header("Location: landowner/dashboard.php");
                exit;
            } elseif ($role === 'admin') {
                header("Location: admin/dashboard.php");
                exit;
            } else {
                header("Location: gardener/dashboard.php");
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Idle Land Gardening</title>
    <link rel="icon" type="image/jpeg" href="logo.jpeg">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
                    fontFamily: {
                        sans: ['Outfit', 'Inter', 'Roboto', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-drive-canvas text-drive-text-main font-sans flex items-center justify-center min-h-screen w-screen m-0 p-4">

<div class="bg-white border border-drive-border rounded-3xl p-8 max-w-[520px] w-full shadow-lg my-6">
    <!-- Google-style Logo -->
    <div class="flex justify-center items-center gap-2.5 mb-3">
        <img src="logo.jpeg" alt="IdleLand Logo" class="h-12 w-12 object-contain rounded-full shadow-sm">
        <span class="text-3xl font-bold font-['Outfit'] tracking-tight text-drive-primary">Idle<span class="text-drive-text-main">Land</span></span>
    </div>
    
    <h2 class="text-2xl font-semibold text-center text-drive-text-main mb-1">Create your Account</h2>
    <p class="text-center text-drive-text-muted mb-6 text-sm">Join IdleLand to manage properties or lease urban gardens</p>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger rounded-2xl text-xs py-3 px-4 mb-4 d-flex align-items-center gap-2 border-0 shadow-xs">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0 fs-6"></i>
            <span><?php echo htmlspecialchars($error_message); ?></span>
        </div>
    <?php endif; ?>

    <!-- Account Registration Form -->
    <form action="create_account.php" method="POST" class="needs-validation m-0" novalidate>
        <input type="hidden" name="action" value="register">
        <input type="hidden" name="role" id="selectedRoleInput" value="gardener">

        <!-- Role Selector Cards -->
        <label class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-2">I am joining as a:</label>
        <div class="grid grid-cols-2 gap-3 mb-5">
            <div id="roleCardGardener" onclick="selectRole('gardener')" class="border-2 border-drive-primary bg-drive-surface-selected rounded-2xl p-3.5 cursor-pointer transition-all hover:shadow-sm">
                <div class="flex items-center justify-between mb-1">
                    <i class="bi bi-flower1 text-xl text-drive-primary"></i>
                    <i id="checkGardener" class="bi bi-check-circle-fill text-drive-primary text-sm"></i>
                </div>
                <div class="font-bold text-xs text-drive-text-main">Gardener</div>
                <div class="text-[11px] text-drive-text-muted leading-tight mt-0.5">Lease plots & grow crops</div>
            </div>

            <div id="roleCardLandowner" onclick="selectRole('landowner')" class="border border-drive-border bg-white rounded-2xl p-3.5 cursor-pointer transition-all hover:border-drive-primary hover:shadow-sm">
                <div class="flex items-center justify-between mb-1">
                    <i class="bi bi-house-door text-xl text-drive-text-sub"></i>
                    <i id="checkLandowner" class="bi bi-check-circle-fill text-drive-primary text-sm hidden"></i>
                </div>
                <div class="font-bold text-xs text-drive-text-main">Landowner</div>
                <div class="text-[11px] text-drive-text-muted leading-tight mt-0.5">Register idle lands</div>
            </div>
        </div>

        <div class="mb-3">
            <label for="name" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1.5">Full Name</label>
            <input 
                type="text" 
                class="w-full bg-white border border-drive-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-drive-primary focus:ring-1 focus:ring-drive-primary transition-all text-drive-text-main placeholder-drive-text-muted" 
                id="name" 
                name="name" 
                placeholder="Jane Doe" 
                required
            >
            <div class="invalid-feedback text-xs mt-1">Please enter your full name.</div>
        </div>

        <div class="mb-3">
            <label for="email" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1.5">Email Address</label>
            <input 
                type="email" 
                class="w-full bg-white border border-drive-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-drive-primary focus:ring-1 focus:ring-drive-primary transition-all text-drive-text-main placeholder-drive-text-muted" 
                id="email" 
                name="email" 
                placeholder="jane@example.com" 
                required
            >
            <div class="invalid-feedback text-xs mt-1">Please enter a valid email address.</div>
        </div>

        <div class="mb-3">
            <label for="phone" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1.5">Phone Number (Optional)</label>
            <input 
                type="text" 
                class="w-full bg-white border border-drive-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-drive-primary focus:ring-1 focus:ring-drive-primary transition-all text-drive-text-main placeholder-drive-text-muted" 
                id="phone" 
                name="phone" 
                placeholder="09123456789" 
            >
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
            <div>
                <label for="password" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1.5">Password</label>
                <input 
                    type="password" 
                    class="w-full bg-white border border-drive-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-drive-primary focus:ring-1 focus:ring-drive-primary transition-all text-drive-text-main placeholder-drive-text-muted" 
                    id="password" 
                    name="password" 
                    placeholder="Create password" 
                    required
                >
            </div>
            <div>
                <label for="confirm_password" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1.5">Confirm Password</label>
                <input 
                    type="password" 
                    class="w-full bg-white border border-drive-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-drive-primary focus:ring-1 focus:ring-drive-primary transition-all text-drive-text-main placeholder-drive-text-muted" 
                    id="confirm_password" 
                    name="confirm_password" 
                    placeholder="Confirm password" 
                    required
                >
            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="index.php" class="text-xs font-semibold text-drive-primary hover:underline text-decoration-none">Sign in instead</a>
            <button type="submit" class="bg-drive-primary hover:bg-drive-primary-hover active:scale-95 text-white font-semibold text-sm px-6 py-2.5 rounded-full shadow-md transition-all">Create Account</button>
        </div>
    </form>
</div>

<script>
    function selectRole(role) {
        document.getElementById('selectedRoleInput').value = role;
        const gCard = document.getElementById('roleCardGardener');
        const lCard = document.getElementById('roleCardLandowner');
        const gCheck = document.getElementById('checkGardener');
        const lCheck = document.getElementById('checkLandowner');

        if (role === 'gardener') {
            gCard.className = "border-2 border-drive-primary bg-drive-surface-selected rounded-2xl p-3.5 cursor-pointer transition-all hover:shadow-sm";
            lCard.className = "border border-drive-border bg-white rounded-2xl p-3.5 cursor-pointer transition-all hover:border-drive-primary hover:shadow-sm";
            gCheck.classList.remove('hidden');
            lCheck.classList.add('hidden');
        } else {
            lCard.className = "border-2 border-drive-primary bg-drive-surface-selected rounded-2xl p-3.5 cursor-pointer transition-all hover:shadow-sm";
            gCard.className = "border border-drive-border bg-white rounded-2xl p-3.5 cursor-pointer transition-all hover:border-drive-primary hover:shadow-sm";
            lCheck.classList.remove('hidden');
            gCheck.classList.add('hidden');
        }
    }

    // Form Validation
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

</body>
</html>
