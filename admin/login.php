<?php
/**
 * Updevix Quiz Platform - Admin Login
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

if (isAdminLoggedIn()) {
    header('Location: /admin/');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Please enter username and password.';
        } else {
            $result = loginAdmin($username, $password);
            if ($result['success']) {
                setAdminSession($result['admin']['id'], $result['admin']['username'], $result['admin']['full_name']);
                header('Location: /admin/');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
}

$csrf_token = generateCSRFToken();

// Apply saved theme
$savedTheme = 'light';
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $savedTheme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <div class="admin-login-page">
        <div class="admin-login-card animate-scale-in">
            <div class="logo" style="justify-content: center; font-size: 28px; margin-bottom: 8px;">
                <span class="logo-up">Up</span><span class="logo-devix" style="color: var(--text-primary);">Devix</span>
            </div>
            <p class="admin-login-subtitle">
                <i class="fas fa-shield-alt"></i> Admin Panel Login
            </p>

            <?php if ($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo sanitize($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="form-group">
                    <label class="form-label">Username or Email</label>
                    <div class="input-group">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" name="username" class="form-input" placeholder="Enter username or email" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" class="form-input" placeholder="Enter password" required>
                        <button type="button" class="password-toggle"><i class="fas fa-eye"></i></button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">
                    <i class="fas fa-sign-in-alt"></i> Login to Admin Panel
                </button>
            </form>

            <div class="text-center mt-3">
                <a href="/" class="link"><i class="fas fa-arrow-left"></i> Back to Website</a>
            </div>
        </div>
    </div>
    <script src="/assets/js/app.js"></script>
</body>
</html>
