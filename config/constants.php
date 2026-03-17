<?php
/**
 * Updevix Quiz Platform - Application Constants
 */

// Application Info
define('APP_NAME', 'Updevix Quiz');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost'); // Update with your domain

// Session Settings
define('SESSION_LIFETIME', 3600); // 1 hour in seconds

// OTP Settings
define('OTP_LENGTH', 6);
define('OTP_EXPIRY_MINUTES', 10);

// File Upload Settings
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_EXCEL_TYPES', ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Quiz Settings
define('DEFAULT_QUIZ_DURATION', 30); // minutes
define('MIN_PASSING_PERCENTAGE', 40);
