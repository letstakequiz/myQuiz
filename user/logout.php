<?php
/**
 * Updevix Quiz Platform - User Logout
 */

require_once __DIR__ . '/../includes/session.php';

logout();
header('Location: /index.php?success=logout');
exit;
