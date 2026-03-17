<?php
/**
 * Updevix Quiz Platform - SMTP Email Configuration
 * Update these values with your Hostinger SMTP credentials
 */

define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 465);
define('SMTP_ENCRYPTION', 'ssl'); // 'ssl' for port 465, 'tls' for port 587
define('SMTP_USERNAME', 'noreply@yourdomain.com'); // Your Hostinger email
define('SMTP_PASSWORD', 'your_email_password');     // Your email password
define('SMTP_FROM_EMAIL', 'noreply@yourdomain.com');
define('SMTP_FROM_NAME', 'Updevix Quiz');
