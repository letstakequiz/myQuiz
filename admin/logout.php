<?php
/**
 * Updevix Quiz Platform - Admin Logout
 */

require_once __DIR__ . '/../includes/session.php';

logout();
header('Location: /admin/login.php');
exit;
