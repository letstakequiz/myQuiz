<?php
/**
 * Updevix Quiz Platform - Landing Page
 * Registration & Login
 */

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /user/dashboard.php');
    exit;
}

$error = '';
$success = '';

// Handle Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        if ($_POST['action'] === 'register') {
            $fullName = sanitize($_POST['full_name'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $phone = sanitize($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($fullName) || empty($email) || empty($password)) {
                $error = 'Please fill in all required fields.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters long.';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } else {
                $result = registerUser($fullName, $email, $password, $phone);
                if ($result['success']) {
                    $success = $result['message'];
                } else {
                    $error = $result['message'];
                }
            }
        }

        if ($_POST['action'] === 'login') {
            $email = sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = 'Please enter email and password.';
            } else {
                $result = loginUser($email, $password);
                if ($result['success']) {
                    setUserSession($result['user']['id'], $result['user']['full_name'], $result['user']['email']);
                    header('Location: /user/dashboard.php');
                    exit;
                } else {
                    $error = $result['message'];
                }
            }
        }
    }
}

// Check for URL params
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'login_required') $error = 'Please login to continue.';
}
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'registered') $success = 'Registration successful! Please login.';
    if ($_GET['success'] === 'password_reset') $success = 'Password reset successful! Please login with your new password.';
    if ($_GET['success'] === 'logout') $success = 'You have been logged out successfully.';
}

$pageTitle = 'Welcome';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <!-- Left: Text Content -->
            <div class="hero-text">
                <div class="hero-badge">
                    <i class="fas fa-sparkles"></i> Best Quiz Platform by UpDevix
                </div>
                <h1 class="hero-title">
                    Test Your <span class="gradient-text">Tech Skills</span> With Industry Experts
                </h1>
                <p class="hero-description">
                    Master aptitude, programming & data science with hands-on quizzes. 
                    Evaluate your knowledge with MCQ, aptitude, and coding challenges designed by industry experts.
                </p>
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-number">100+</div>
                        <div class="stat-label">Questions</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Quizzes</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">95%</div>
                        <div class="stat-label">Success Rate</div>
                    </div>
                </div>
            </div>

            <!-- Right: Auth Form -->
            <div class="hero-form" id="authForm">
                <?php if ($error): ?>
                    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo sanitize($error); ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo sanitize($success); ?></div>
                <?php endif; ?>

                <!-- Tabs -->
                <div class="form-tabs">
                    <button class="form-tab active" data-tab="loginForm">Login</button>
                    <button class="form-tab" data-tab="registerForm">Register</button>
                </div>

                <!-- Login Form -->
                <form class="auth-form active" id="loginForm" method="POST" action="">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" class="form-input" placeholder="Enter your email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password" class="form-input" placeholder="Enter your password" required>
                            <button type="button" class="password-toggle"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>

                    <div class="form-group" style="text-align: right;">
                        <a href="/user/forgot-password.php" class="link" style="font-size: 13px;">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>

                <!-- Register Form -->
                <form class="auth-form" id="registerForm" method="POST" action="">
                    <input type="hidden" name="action" value="register">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <div class="input-group">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="full_name" class="form-input" placeholder="Enter your full name" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" class="form-input" placeholder="Enter your email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number <span style="color: var(--text-muted); font-weight: 400;">(Optional)</span></label>
                        <div class="input-group">
                            <i class="fas fa-phone input-icon"></i>
                            <input type="tel" name="phone" class="form-input" placeholder="Enter phone number">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password" class="form-input" placeholder="Min. 6 characters" required minlength="6">
                            <button type="button" class="password-toggle"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="confirm_password" class="form-input" placeholder="Confirm your password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
