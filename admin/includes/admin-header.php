<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? sanitize($pageTitle) . ' | Admin' : 'Admin Panel'; ?> - <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <a href="/admin/" class="logo">
                    <span class="logo-up">Up</span><span class="logo-devix">Devix</span>
                </a>
                <span style="font-size: 11px; color: #718096; display: block; margin-top: 4px;">Admin Panel</span>
            </div>
            <nav class="sidebar-nav">
                <a href="/admin/" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="/admin/quizzes.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) === 'quizzes.php' ? 'active' : ''; ?>">
                    <i class="fas fa-clipboard-list"></i> Quizzes
                </a>
                <a href="/admin/questions.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) === 'questions.php' ? 'active' : ''; ?>">
                    <i class="fas fa-question-circle"></i> Questions
                </a>
                <a href="/admin/upload.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) === 'upload.php' ? 'active' : ''; ?>">
                    <i class="fas fa-upload"></i> Upload Questions
                </a>
                <a href="/admin/users.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="/admin/results.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) === 'results.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i> Results
                </a>
            </nav>
            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <i class="fas fa-user-shield"></i>
                    <span><?php echo sanitize($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                </div>
                <a href="/admin/logout.php" class="sidebar-link" style="color: #ef4444;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="admin-main">
            <header class="admin-topbar">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div style="display: flex; align-items: center; gap: 16px; margin-left: auto;">
                    <button class="theme-toggle" id="themeToggle" title="Toggle dark mode">
                        <i class="fas fa-moon"></i>
                    </button>
                    <span class="nav-user">
                        <i class="fas fa-user-circle"></i> <?php echo sanitize($_SESSION['admin_name'] ?? 'Admin'); ?>
                    </span>
                </div>
            </header>
            <div class="admin-content">
