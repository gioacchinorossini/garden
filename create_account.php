<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/mail_helper.php';

// ── AJAX Action: Send Verification Code ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_code') {
    header('Content-Type: application/json; charset=UTF-8');
    $email = strtolower(trim($_POST['email'] ?? ''));

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address first.']);
        exit;
    }

    // Check for duplicate email in database
    $exists = false;
    try {
        require_once __DIR__ . '/includes/db.php';
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $check->execute([$email]);
        if ($check->fetch()) {
            $exists = true;
        }
    } catch (\Exception $e) {
        if (isset($_SESSION['mock_users']) && is_array($_SESSION['mock_users'])) {
            foreach ($_SESSION['mock_users'] as $u) {
                if (strtolower($u['email']) === $email) {
                    $exists = true;
                    break;
                }
            }
        }
    }

    if ($exists) {
        echo json_encode(['status' => 'error', 'message' => 'An account with this email address already exists. Please sign in instead.']);
        exit;
    }

    // Generate 6-digit OTP code
    $code = generate_verification_code();

    // Dispatch verification code via email
    $result = send_verification_email($email, $code);

    if (!$result['success']) {
        echo json_encode([
            'status'  => 'error',
            'message' => $result['error'] ?: 'Could not send verification email. Please check your mail settings.'
        ]);
        exit;
    }

    // Store verification code in session only after successful delivery
    $_SESSION['email_verification'] = [
        'email'      => $email,
        'code'       => $code,
        'expires_at' => time() + 900 // 15 minutes
    ];

    echo json_encode([
        'status'  => 'success',
        'message' => 'A 6-digit verification code has been sent to ' . htmlspecialchars($email)
    ]);
    exit;
}

$error_message    = '';
$success_message  = '';
$name              = '';
$email             = '';
$phone             = '';
$role              = 'gardener';
$current_step      = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $name              = trim($_POST['name'] ?? '');
    $email             = strtolower(trim($_POST['email'] ?? ''));
    $password          = trim($_POST['password'] ?? '');
    $confirm_password  = trim($_POST['confirm_password'] ?? '');
    $role              = in_array($_POST['role'] ?? '', ['gardener', 'landowner']) ? $_POST['role'] : 'gardener';
    $phone             = trim($_POST['phone'] ?? '');
    $verification_code = trim($_POST['verification_code'] ?? '');

    // Default step to stay on step 2 if user submitted step 2
    $current_step = 2;

    $verif = $_SESSION['email_verification'] ?? null;

    if (empty($name) || empty($email) || empty($password)) {
        $error_message = 'Please fill in all required fields.';
        $current_step  = 1;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
        $current_step  = 1;
    } elseif (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters.';
        $current_step  = 1;
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match. Please check and try again.';
        $current_step  = 1;
    } elseif (empty($verification_code)) {
        $error_message = 'Please enter the 6-digit verification code sent to your email.';
    } elseif (!$verif || empty($verif['code'])) {
        $error_message = 'Please request an email verification code first.';
        $current_step  = 1;
    } elseif (strtolower($verif['email']) !== $email) {
        $error_message = 'The verification code was sent to a different email address. Please request a new code.';
        $current_step  = 1;
    } elseif (time() > ($verif['expires_at'] ?? 0)) {
        $error_message = 'The verification code has expired. Please request a new code.';
    } elseif ((string)$verif['code'] !== $verification_code) {
        $error_message = 'Invalid verification code. Please check your email and try again.';
    } else {
        // Verified! Clear verification session
        unset($_SESSION['email_verification']);

        // ── Real DB registration ────────────────────────────────────────────
        try {
            require_once __DIR__ . '/includes/db.php';

            // Check duplicate email
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $check->execute([$email]);
            if ($check->fetch()) {
                $error_message = 'An account with this email address already exists. Please sign in instead.';
                $current_step  = 1;
            } else {
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $insert = $pdo->prepare(
                    "INSERT INTO users (name, email, password, role, phone, status) VALUES (?, ?, ?, ?, ?, 'active')"
                );
                $insert->execute([$name, $email, $hashed, $role, $phone]);
                $new_id = (int)$pdo->lastInsertId();

                $_SESSION['user_id']     = $new_id;
                $_SESSION['user_name']   = $name;
                $_SESSION['user_email']  = $email;
                $_SESSION['active_role'] = $role;

                $redirect = match($role) {
                    'admin'     => 'admin/dashboard.php',
                    'landowner' => 'landowner/dashboard.php',
                    default     => 'gardener/dashboard.php',
                };
                header("Location: $redirect");
                exit;
            }
        } catch (\PDOException $e) {
            // ── Fallback: session-based mock when DB is unavailable ─────────
            if (!isset($_SESSION['mock_users']) || !is_array($_SESSION['mock_users'])) {
                $_SESSION['mock_users'] = [
                    ['id' => 1, 'name' => 'System Administrator', 'email' => 'admin@garden.com',     'role' => 'admin',     'phone' => '09170000000', 'status' => 'active'],
                    ['id' => 2, 'name' => 'John Landowner',       'email' => 'landowner@garden.com', 'role' => 'landowner', 'phone' => '09171112222', 'status' => 'active'],
                    ['id' => 3, 'name' => 'Mary Gardener',        'email' => 'gardener@garden.com',  'role' => 'gardener',  'phone' => '09173334444', 'status' => 'active'],
                ];
            }

            $exists = false;
            foreach ($_SESSION['mock_users'] as $u) {
                if (strtolower($u['email']) === $email) { $exists = true; break; }
            }

            if ($exists) {
                $error_message = 'An account with this email address already exists. Please sign in instead.';
                $current_step  = 1;
            } else {
                $new_id   = count($_SESSION['mock_users']) + 1;
                $new_user = ['id' => $new_id, 'name' => $name, 'email' => $email, 'role' => $role, 'phone' => $phone, 'status' => 'active'];
                $_SESSION['mock_users'][] = $new_user;

                $_SESSION['user_id']     = $new_id;
                $_SESSION['user_name']   = $name;
                $_SESSION['user_email']  = $email;
                $_SESSION['active_role'] = $role;

                $redirect = match($role) {
                    'admin'     => 'admin/dashboard.php',
                    'landowner' => 'landowner/dashboard.php',
                    default     => 'gardener/dashboard.php',
                };
                header("Location: $redirect");
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
    <!-- Fonts (Local & Web Fallback) -->
    <link rel="stylesheet" href="assets/vendor/fonts/fonts.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 & Icons -->
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
                    fontFamily: {
                        sans: ['Outfit', 'Inter', 'Roboto', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-drive-canvas text-drive-text-main font-sans flex items-center justify-center min-h-screen w-screen m-0 p-4">

<div class="bg-white border border-drive-border rounded-3xl p-8 max-w-[520px] w-full shadow-lg my-6 transition-all duration-300">
    <!-- Google-style Logo -->
    <div class="flex justify-center items-center gap-2.5 mb-2">
        <img src="logo.jpeg" alt="IdleLand Logo" class="h-11 w-11 object-contain rounded-full shadow-sm">
        <span class="text-3xl font-bold font-['Outfit'] tracking-tight text-drive-primary">Idle<span class="text-drive-text-main">Land</span></span>
    </div>
    
    <h2 class="text-2xl font-semibold text-center text-drive-text-main mb-1" id="pageTitle">Create your Account</h2>
    <p class="text-center text-drive-text-muted mb-5 text-sm" id="pageSubtitle">Join IdleLand to manage properties or lease urban gardens</p>

    <!-- Multi-Step Progress Stepper -->
    <div class="flex items-center justify-center gap-2 mb-6 select-none">
        <div id="stepPill1" class="flex items-center gap-1.5 text-xs font-semibold text-drive-primary transition-all">
            <span id="stepIcon1" class="w-6 h-6 rounded-full bg-drive-primary text-white flex items-center justify-center text-[11px] shadow-xs">1</span>
            <span>Account Details</span>
        </div>
        <div id="stepLine" class="w-10 h-0.5 bg-drive-border rounded-full transition-all"></div>
        <div id="stepPill2" class="flex items-center gap-1.5 text-xs font-semibold text-drive-text-muted transition-all">
            <span id="stepIcon2" class="w-6 h-6 rounded-full bg-gray-100 text-drive-text-muted flex items-center justify-center text-[11px]">2</span>
            <span>Verify Email</span>
        </div>
    </div>

    <!-- Global Alert Box -->
    <div id="globalAlert" class="<?php echo !empty($error_message) ? '' : 'hidden'; ?> alert alert-danger rounded-2xl text-xs py-3 px-4 mb-4 d-flex align-items-center gap-2 border-0 shadow-xs">
        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 fs-6"></i>
        <span id="globalAlertText"><?php echo htmlspecialchars($error_message); ?></span>
    </div>

    <!-- Account Registration Form Wrapping Both Steps -->
    <form id="registrationForm" action="create_account.php" method="POST" class="m-0" novalidate>
        <input type="hidden" name="action" value="register">
        <input type="hidden" name="role" id="selectedRoleInput" value="<?php echo htmlspecialchars($role); ?>">

        <!-- ================================================================= -->
        <!-- STEP 1: ACCOUNT DETAILS                                           -->
        <!-- ================================================================= -->
        <div id="step1Section" class="<?php echo $current_step === 2 ? 'hidden' : ''; ?> space-y-4">
            
            <!-- Role Selector Cards -->
            <div>
                <label class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-2">I am joining as a:</label>
                <div class="grid grid-cols-2 gap-3">
                    <div id="roleCardGardener" onclick="selectRole('gardener')" class="<?php echo $role === 'gardener' ? 'border-2 border-drive-primary bg-drive-surface-selected' : 'border border-drive-border bg-white'; ?> rounded-2xl p-3.5 cursor-pointer transition-all hover:shadow-sm">
                        <div class="flex items-center justify-between mb-1">
                            <i class="bi bi-flower1 text-xl text-drive-primary"></i>
                            <i id="checkGardener" class="bi bi-check-circle-fill text-drive-primary text-sm <?php echo $role === 'gardener' ? '' : 'hidden'; ?>"></i>
                        </div>
                        <div class="font-bold text-xs text-drive-text-main">Gardener</div>
                        <div class="text-[11px] text-drive-text-muted leading-tight mt-0.5">Lease plots & grow crops</div>
                    </div>

                    <div id="roleCardLandowner" onclick="selectRole('landowner')" class="<?php echo $role === 'landowner' ? 'border-2 border-drive-primary bg-drive-surface-selected' : 'border border-drive-border bg-white'; ?> rounded-2xl p-3.5 cursor-pointer transition-all hover:border-drive-primary hover:shadow-sm">
                        <div class="flex items-center justify-between mb-1">
                            <i class="bi bi-house-door text-xl text-drive-text-sub"></i>
                            <i id="checkLandowner" class="bi bi-check-circle-fill text-drive-primary text-sm <?php echo $role === 'landowner' ? '' : 'hidden'; ?>"></i>
                        </div>
                        <div class="font-bold text-xs text-drive-text-main">Landowner</div>
                        <div class="text-[11px] text-drive-text-muted leading-tight mt-0.5">Register idle lands</div>
                    </div>
                </div>
            </div>

            <!-- Full Name -->
            <div>
                <label for="name" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1.5">Full Name</label>
                <input 
                    type="text" 
                    class="w-full bg-white border border-drive-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-drive-primary focus:ring-1 focus:ring-drive-primary transition-all text-drive-text-main placeholder-drive-text-muted" 
                    id="name" 
                    name="name" 
                    value="<?php echo htmlspecialchars($name); ?>"
                    placeholder="Jane Doe" 
                    required
                >
            </div>

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1.5">Email Address</label>
                <input 
                    type="email" 
                    class="w-full bg-white border border-drive-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-drive-primary focus:ring-1 focus:ring-drive-primary transition-all text-drive-text-main placeholder-drive-text-muted" 
                    id="email" 
                    name="email" 
                    value="<?php echo htmlspecialchars($email); ?>"
                    placeholder="jane@example.com" 
                    required
                >
            </div>

            <!-- Phone Number -->
            <div>
                <label for="phone" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1.5">Phone Number (Optional)</label>
                <input 
                    type="text" 
                    class="w-full bg-white border border-drive-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-drive-primary focus:ring-1 focus:ring-drive-primary transition-all text-drive-text-main placeholder-drive-text-muted" 
                    id="phone" 
                    name="phone" 
                    value="<?php echo htmlspecialchars($phone); ?>"
                    placeholder="09123456789" 
                >
            </div>

            <!-- Passwords -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="password" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1.5">Password</label>
                    <input 
                        type="password" 
                        class="w-full bg-white border border-drive-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-drive-primary focus:ring-1 focus:ring-drive-primary transition-all text-drive-text-main placeholder-drive-text-muted" 
                        id="password" 
                        name="password" 
                        placeholder="Min 6 characters" 
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
                        placeholder="Re-enter password" 
                        required
                    >
                </div>
            </div>

            <!-- Step 1 Bottom Actions -->
            <div class="flex items-center justify-between pt-4">
                <a href="index.php" class="text-xs font-semibold text-drive-primary hover:underline text-decoration-none">Sign in instead</a>
                <button 
                    type="button" 
                    id="btnNextToStep2" 
                    onclick="proceedToStep2()" 
                    class="bg-drive-primary hover:bg-drive-primary-hover active:scale-95 text-white font-semibold text-sm px-6 py-2.5 rounded-full shadow-md transition-all flex items-center gap-2"
                >
                    <span id="btnNextText">Next: Verify Email</span>
                    <i class="bi bi-arrow-right text-xs" id="btnNextIcon"></i>
                </button>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- STEP 2: EMAIL VERIFICATION CODE                                   -->
        <!-- ================================================================= -->
        <div id="step2Section" class="<?php echo $current_step === 2 ? '' : 'hidden'; ?> space-y-5">
            
            <!-- Email Verification Header Card -->
            <div class="text-center py-2">
                <div class="w-16 h-16 bg-drive-surface-selected border-2 border-emerald-200 text-drive-primary rounded-full flex items-center justify-center mx-auto mb-3 shadow-xs">
                    <i class="bi bi-envelope-check text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-drive-text-main mb-1">Verify your email</h3>
                <p class="text-xs text-drive-text-muted leading-relaxed">
                    We sent a 6-digit verification code to<br>
                    <span id="emailTargetDisplay" class="font-semibold text-drive-text-main text-sm"><?php echo htmlspecialchars($email); ?></span>
                    <button type="button" onclick="goToStep(1)" class="text-xs text-drive-primary font-semibold hover:underline ml-1">Change</button>
                </p>
            </div>

            <!-- Verification Code Input -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="verification_code" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider">6-Digit Code</label>
                    <span id="codeTimerBadge" class="text-[11px] text-drive-primary font-medium flex items-center gap-1">
                        <i class="bi bi-clock"></i> <span id="codeTimerText">Expires in 15:00</span>
                    </span>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-drive-text-muted">
                        <i class="bi bi-shield-lock text-base"></i>
                    </div>
                    <input 
                        type="text" 
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        maxlength="6"
                        class="w-full bg-white border-2 border-drive-border rounded-2xl pl-11 pr-4 py-3 text-lg font-mono font-bold tracking-[0.35em] text-center focus:outline-none focus:border-drive-primary focus:ring-2 focus:ring-drive-primary/20 transition-all text-drive-text-main placeholder-drive-text-muted" 
                        id="verification_code" 
                        name="verification_code" 
                        placeholder="••••••" 
                        autocomplete="one-time-code"
                        required
                    >
                </div>
                <p class="text-[11px] text-drive-text-muted text-center mt-2">Enter the verification code sent to your email inbox (or spam).</p>
            </div>

            <!-- Resend Section -->
            <div class="text-center pt-1">
                <p class="text-xs text-drive-text-muted">
                    Didn't receive the code?
                    <button 
                        type="button" 
                        id="btnResendCode" 
                        onclick="resendVerificationCode()" 
                        class="font-semibold text-drive-primary hover:underline ml-1 disabled:opacity-50 disabled:cursor-not-allowed disabled:no-underline"
                    >
                        Resend Code
                    </button>
                </p>
                <div id="resendStatusBox" class="hidden text-xs mt-2 text-drive-primary font-medium"></div>
            </div>

            <!-- Step 2 Actions -->
            <div class="flex items-center justify-between pt-4 border-t border-drive-border/60">
                <button 
                    type="button" 
                    onclick="goToStep(1)" 
                    class="px-5 py-2.5 rounded-full border border-drive-border text-xs font-semibold text-drive-text-sub hover:bg-gray-50 active:scale-95 transition-all flex items-center gap-1.5"
                >
                    <i class="bi bi-arrow-left"></i> Back
                </button>
                <button 
                    type="submit" 
                    id="btnSubmitRegistration" 
                    class="bg-drive-primary hover:bg-drive-primary-hover active:scale-95 text-white font-semibold text-sm px-7 py-2.5 rounded-full shadow-md transition-all flex items-center gap-2"
                >
                    <span>Verify & Create Account</span>
                    <i class="bi bi-check-circle-fill text-xs"></i>
                </button>
            </div>
        </div>

    </form>
</div>

<script>
    let currentStep = <?php echo (int)$current_step; ?>;
    let resendCountdown = 0;
    let resendInterval = null;
    let expiryCountdown = 900;
    let expiryInterval = null;

    // Initialize display state
    if (currentStep === 2) {
        goToStep(2);
        startExpiryTimer(900);
    }

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

    function showAlert(message) {
        const alertBox = document.getElementById('globalAlert');
        const alertText = document.getElementById('globalAlertText');
        alertText.textContent = message;
        alertBox.classList.remove('hidden');
    }

    function hideAlert() {
        const alertBox = document.getElementById('globalAlert');
        alertBox.classList.add('hidden');
    }

    function goToStep(step) {
        currentStep = step;
        hideAlert();

        const step1 = document.getElementById('step1Section');
        const step2 = document.getElementById('step2Section');
        const pill1 = document.getElementById('stepPill1');
        const pill2 = document.getElementById('stepPill2');
        const icon1 = document.getElementById('stepIcon1');
        const icon2 = document.getElementById('stepIcon2');
        const line  = document.getElementById('stepLine');
        const title = document.getElementById('pageTitle');
        const sub   = document.getElementById('pageSubtitle');

        if (step === 1) {
            step1.classList.remove('hidden');
            step2.classList.add('hidden');

            pill1.className = "flex items-center gap-1.5 text-xs font-semibold text-drive-primary transition-all";
            icon1.className = "w-6 h-6 rounded-full bg-drive-primary text-white flex items-center justify-center text-[11px] shadow-xs";
            icon1.innerHTML = "1";

            line.className  = "w-10 h-0.5 bg-drive-border rounded-full transition-all";

            pill2.className = "flex items-center gap-1.5 text-xs font-semibold text-drive-text-muted transition-all";
            icon2.className = "w-6 h-6 rounded-full bg-gray-100 text-drive-text-muted flex items-center justify-center text-[11px]";

            title.textContent = "Create your Account";
            sub.textContent   = "Join IdleLand to manage properties or lease urban gardens";
        } else {
            step1.classList.add('hidden');
            step2.classList.remove('hidden');

            pill1.className = "flex items-center gap-1.5 text-xs font-semibold text-drive-text-sub transition-all cursor-pointer";
            pill1.onclick   = () => goToStep(1);
            icon1.className = "w-6 h-6 rounded-full bg-emerald-100 text-drive-primary flex items-center justify-center text-[11px]";
            icon1.innerHTML = '<i class="bi bi-check"></i>';

            line.className  = "w-10 h-0.5 bg-drive-primary rounded-full transition-all";

            pill2.className = "flex items-center gap-1.5 text-xs font-semibold text-drive-primary transition-all";
            icon2.className = "w-6 h-6 rounded-full bg-drive-primary text-white flex items-center justify-center text-[11px] shadow-xs";

            title.textContent = "Email Verification";
            sub.textContent   = "Confirm your email to complete registration";

            const codeInput = document.getElementById('verification_code');
            setTimeout(() => codeInput.focus(), 150);
        }
    }

    // Step 1: Validate inputs and request code to proceed to Step 2
    function proceedToStep2() {
        hideAlert();

        const nameInput    = document.getElementById('name');
        const emailInput   = document.getElementById('email');
        const pwdInput     = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');

        const nameVal    = nameInput.value.trim();
        const emailVal   = emailInput.value.trim();
        const pwdVal     = pwdInput.value;
        const confirmVal = confirmInput.value;

        // Validation checks
        if (!nameVal) {
            showAlert('Please enter your full name.');
            nameInput.focus();
            return;
        }

        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailVal || !emailPattern.test(emailVal)) {
            showAlert('Please enter a valid email address.');
            emailInput.focus();
            return;
        }

        if (pwdVal.length < 6) {
            showAlert('Password must be at least 6 characters long.');
            pwdInput.focus();
            return;
        }

        if (pwdVal !== confirmVal) {
            showAlert('Passwords do not match. Please re-enter your password.');
            confirmInput.focus();
            return;
        }

        // Trigger Send Code and transition to Step 2
        const nextBtn  = document.getElementById('btnNextToStep2');
        const nextText = document.getElementById('btnNextText');
        const nextIcon = document.getElementById('btnNextIcon');

        nextBtn.disabled   = true;
        nextText.textContent = 'Sending Code...';
        nextIcon.className   = 'spinner-border spinner-border-sm';

        const formData = new FormData();
        formData.append('action', 'send_code');
        formData.append('email', emailVal);

        fetch('create_account.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            nextBtn.disabled = false;
            nextText.textContent = 'Next: Verify Email';
            nextIcon.className   = 'bi bi-arrow-right text-xs';

            if (data.status === 'success') {
                document.getElementById('emailTargetDisplay').textContent = emailVal;
                goToStep(2);
                startResendTimer(60);
                startExpiryTimer(900);
            } else {
                showAlert(data.message || 'Failed to send verification code.');
            }
        })
        .catch(err => {
            nextBtn.disabled = false;
            nextText.textContent = 'Next: Verify Email';
            nextIcon.className   = 'bi bi-arrow-right text-xs';
            showAlert('An unexpected error occurred while sending the code. Please try again.');
        });
    }

    function resendVerificationCode() {
        const emailVal = document.getElementById('email').value.trim();
        const resendBtn = document.getElementById('btnResendCode');
        const statusBox = document.getElementById('resendStatusBox');

        resendBtn.disabled = true;
        resendBtn.textContent = 'Sending...';

        const formData = new FormData();
        formData.append('action', 'send_code');
        formData.append('email', emailVal);

        fetch('create_account.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                statusBox.textContent = 'New verification code sent!';
                statusBox.classList.remove('hidden');
                setTimeout(() => statusBox.classList.add('hidden'), 3500);

                startResendTimer(60);
                startExpiryTimer(900);
            } else {
                showAlert(data.message || 'Failed to resend code.');
                resendBtn.disabled = false;
                resendBtn.textContent = 'Resend Code';
            }
        })
        .catch(() => {
            showAlert('Failed to resend code. Please try again.');
            resendBtn.disabled = false;
            resendBtn.textContent = 'Resend Code';
        });
    }

    function startResendTimer(seconds) {
        clearInterval(resendInterval);
        resendCountdown = seconds;
        const resendBtn = document.getElementById('btnResendCode');

        resendBtn.disabled = true;
        resendBtn.textContent = `Resend Code (${resendCountdown}s)`;

        resendInterval = setInterval(() => {
            resendCountdown--;
            if (resendCountdown <= 0) {
                clearInterval(resendInterval);
                resendBtn.disabled = false;
                resendBtn.textContent = 'Resend Code';
            } else {
                resendBtn.textContent = `Resend Code (${resendCountdown}s)`;
            }
        }, 1000);
    }

    function startExpiryTimer(seconds) {
        clearInterval(expiryInterval);
        expiryCountdown = seconds;
        const badge = document.getElementById('codeTimerBadge');
        const timerText = document.getElementById('codeTimerText');

        function updateDisplay() {
            const m = Math.floor(expiryCountdown / 60);
            const s = expiryCountdown % 60;
            timerText.textContent = `Expires in ${m}:${s < 10 ? '0' : ''}${s}`;
        }

        updateDisplay();
        expiryInterval = setInterval(() => {
            expiryCountdown--;
            if (expiryCountdown <= 0) {
                clearInterval(expiryInterval);
                timerText.textContent = 'Code expired';
                badge.className = 'text-[11px] text-red-600 font-medium flex items-center gap-1';
            } else {
                updateDisplay();
            }
        }, 1000);
    }

    // Auto-advance or format code input
    document.getElementById('verification_code').addEventListener('input', function (e) {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
    });
</script>
<script src="assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
