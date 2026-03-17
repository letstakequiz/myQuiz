<?php
/**
 * Updevix Quiz Platform - Forgot Password
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

$error = '';
$success = '';
$step = 'email'; // email -> otp -> reset

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'send_otp') {
            $email = sanitize($_POST['email'] ?? '');
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } else {
                // Check if user exists
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if (!$user) {
                    $error = 'No account found with this email address.';
                } else {
                    $otp = generateOTP($email, 'password_reset');

                    // Try to send email (will fail silently if SMTP not configured)
                    try {
                        require_once __DIR__ . '/../includes/email.php';
                        sendOTPEmail($email, $user['full_name'], $otp);
                    } catch (Exception $e) {
                        // SMTP not configured - show OTP in success message for development
                        error_log("Email send failed: " . $e->getMessage());
                    }

                    $_SESSION['reset_email'] = $email;
                    $step = 'otp';
                    $success = 'A verification code has been sent to your email.';
                }
            }
        }

        if ($action === 'verify_otp') {
            $email = $_SESSION['reset_email'] ?? '';
            $otp = sanitize($_POST['otp'] ?? '');

            if (empty($otp)) {
                $error = 'Please enter the OTP code.';
                $step = 'otp';
            } elseif (verifyOTP($email, $otp, 'password_reset')) {
                $_SESSION['otp_verified'] = true;
                $step = 'reset';
                $success = 'OTP verified successfully. Set your new password.';
            } else {
                $error = 'Invalid or expired OTP. Please try again.';
                $step = 'otp';
            }
        }

        if ($action === 'reset_password') {
            $email = $_SESSION['reset_email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($_SESSION['otp_verified'])) {
                $error = 'Please verify OTP first.';
                $step = 'email';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters.';
                $step = 'reset';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
                $step = 'reset';
            } else {
                if (resetPassword($email, $password)) {
                    unset($_SESSION['reset_email'], $_SESSION['otp_verified']);
                    header('Location: /index.php?success=password_reset');
                    exit;
                } else {
                    $error = 'Failed to reset password. Please try again.';
                    $step = 'reset';
                }
            }
        }
    }
}

// Restore step from session
if (isset($_SESSION['reset_email']) && $step === 'email' && empty($error)) {
    $step = 'otp';
}
if (isset($_SESSION['otp_verified']) && $step !== 'email') {
    $step = 'reset';
}

$pageTitle = 'Forgot Password';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card animate-fade-in">
        <?php if ($step === 'email'): ?>
            <h2>Forgot Password</h2>
            <p class="auth-subtitle">Enter your email to receive a verification code</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo sanitize($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="action" value="send_otp">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" class="form-input" placeholder="Enter your registered email" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-paper-plane"></i> Send Verification Code
                </button>
            </form>

        <?php elseif ($step === 'otp'): ?>
            <h2>Enter OTP</h2>
            <p class="auth-subtitle">Enter the 6-digit code sent to your email</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo sanitize($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo sanitize($success); ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="action" value="verify_otp">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="form-group">
                    <label class="form-label">Verification Code</label>
                    <div class="input-group">
                        <i class="fas fa-shield-alt input-icon"></i>
                        <input type="text" name="otp" class="form-input" placeholder="Enter 6-digit OTP" maxlength="6" required style="letter-spacing: 4px; font-size: 18px; text-align: center;">
                    </div>
                    <p class="form-hint">Code expires in <?php echo OTP_EXPIRY_MINUTES; ?> minutes</p>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-check"></i> Verify OTP
                </button>
            </form>

        <?php elseif ($step === 'reset'): ?>
            <h2>Reset Password</h2>
            <p class="auth-subtitle">Create a new password for your account</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo sanitize($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo sanitize($success); ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" class="form-input" placeholder="Min. 6 characters" required minlength="6">
                        <button type="button" class="password-toggle"><i class="fas fa-eye"></i></button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="confirm_password" class="form-input" placeholder="Confirm password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-save"></i> Reset Password
                </button>
            </form>
        <?php endif; ?>

        <div class="text-center mt-3">
            <a href="/index.php" class="link"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
