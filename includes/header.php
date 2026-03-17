<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php';
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? sanitize($pageTitle) . ' | ' . APP_NAME : APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <?php if (isset($extraCSS)) echo $extraCSS; ?>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container navbar-content">
            <a href="/" class="logo">
                <span class="logo-up">Up</span><span class="logo-devix">Devix</span> <span class="logo-quiz">Quiz</span>
            </a>
            <div class="nav-links">
                <?php if (isLoggedIn()): ?>
                    <a href="/user/dashboard.php" class="nav-link">Dashboard</a>
                    <a href="/user/history.php" class="nav-link">My Results</a>
                    <span class="nav-user">
                        <i class="fas fa-user-circle"></i> <?php echo sanitize($_SESSION['user_name']); ?>
                    </span>
                    <a href="/user/logout.php" class="btn btn-outline btn-sm">Logout</a>
                <?php else: ?>
                    <a href="/index.php" class="nav-link">Home</a>
                    <a href="/index.php#login" class="btn btn-primary btn-sm">Login</a>
                <?php endif; ?>
                <button class="theme-toggle" id="themeToggle" title="Toggle dark mode">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
            <button class="mobile-toggle" id="mobileToggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>
    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <?php if (isLoggedIn()): ?>
            <a href="/user/dashboard.php" class="mobile-link">Dashboard</a>
            <a href="/user/history.php" class="mobile-link">My Results</a>
            <a href="/user/logout.php" class="mobile-link">Logout</a>
        <?php else: ?>
            <a href="/index.php" class="mobile-link">Home</a>
            <a href="/index.php#login" class="mobile-link">Login</a>
            <a href="/index.php#register" class="mobile-link">Register</a>
        <?php endif; ?>
    </div>
    <main class="main-content">
