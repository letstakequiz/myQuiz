# Updevix Quiz Platform

A complete production-ready Quiz Web Application built with PHP + MySQL + HTML + CSS + JavaScript. Designed for deployment on Hostinger shared hosting.

## Features

- **User Authentication** - Registration, Login, Logout with PHP sessions
- **OTP Verification** - Password reset via email OTP (PHPMailer + Hostinger SMTP)
- **Quiz System** - MCQ, Aptitude, and Coding questions with timer
- **Result Evaluation** - Detailed score breakdown with answer review
- **Admin Panel** - Full quiz/question/user/result management
- **Excel Upload** - Import questions via CSV files
- **Dark Mode** - Toggle between light and dark themes
- **Responsive Design** - Mobile-first, matching Updevix design language
- **Security** - SQL injection protection, XSS prevention, CSRF tokens, password hashing

## Tech Stack

- **Backend:** PHP 7.4+ (Core PHP, PDO)
- **Database:** MySQL 5.7+ / MariaDB 10.3+
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Email:** PHPMailer with SMTP
- **Fonts:** Inter (Google Fonts)
- **Icons:** Font Awesome 6

## Project Structure

```
/
├── admin/                  # Admin panel
│   ├── includes/           # Admin header/footer
│   ├── index.php           # Admin dashboard
│   ├── login.php           # Admin login
│   ├── logout.php          # Admin logout
│   ├── quizzes.php         # Manage quizzes
│   ├── questions.php       # Manage questions
│   ├── upload.php          # Upload questions (CSV)
│   ├── users.php           # Manage users
│   ├── results.php         # View all results
│   └── result-detail.php   # View result detail
├── assets/
│   ├── css/
│   │   ├── style.css       # Main stylesheet
│   │   └── admin.css       # Admin panel styles
│   ├── js/
│   │   └── app.js          # Main JavaScript
│   ├── images/             # Image assets
│   └── sample_questions.csv# Sample CSV template
├── config/
│   ├── database.php        # Database configuration
│   ├── constants.php       # Application constants
│   └── smtp.php            # SMTP email configuration
├── database/
│   └── updevix_quiz.sql    # Database schema + sample data
├── includes/
│   ├── session.php         # Session management
│   ├── functions.php       # Helper functions
│   ├── email.php           # Email functions
│   ├── header.php          # Page header template
│   └── footer.php          # Page footer template
├── uploads/
│   └── questions/          # Uploaded question files
├── user/
│   ├── dashboard.php       # User dashboard
│   ├── quiz.php            # Take quiz page
│   ├── result.php          # View result
│   ├── history.php         # Quiz history
│   ├── forgot-password.php # Password reset flow
│   └── logout.php          # User logout
├── vendor/
│   └── PHPMailer/          # PHPMailer library
├── .htaccess               # Apache configuration
├── index.php               # Landing page (Login/Register)
└── README.md               # This file
```

## Installation Guide

### Step 1: Hosting Setup (Hostinger)

1. Log in to your Hostinger control panel (hPanel)
2. Go to **File Manager** or connect via **FTP**
3. Navigate to `public_html` directory
4. Upload all project files to `public_html`

### Step 2: Create MySQL Database

1. In hPanel, go to **Databases** > **MySQL Databases**
2. Create a new database (e.g., `updevix_quiz`)
3. Create a database user and assign it to the database
4. Note down: database name, username, password

### Step 3: Import Database

1. In hPanel, go to **Databases** > **phpMyAdmin**
2. Select your database
3. Click **Import** tab
4. Choose the file `database/updevix_quiz.sql`
5. Click **Go** to import

### Step 4: Configure Database Connection

1. Open `config/database.php`
2. Update the following values:

```php
define('DB_HOST', 'localhost');        // Usually 'localhost' on Hostinger
define('DB_NAME', 'your_database');    // Your database name
define('DB_USER', 'your_username');    // Your database username
define('DB_PASS', 'your_password');    // Your database password
```

### Step 5: Configure SMTP Email

1. Open `config/smtp.php`
2. Update with your Hostinger email credentials:

```php
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 465);
define('SMTP_ENCRYPTION', 'ssl');
define('SMTP_USERNAME', 'noreply@yourdomain.com');
define('SMTP_PASSWORD', 'your_email_password');
define('SMTP_FROM_EMAIL', 'noreply@yourdomain.com');
define('SMTP_FROM_NAME', 'Updevix Quiz');
```

### Step 6: Set Application URL

1. Open `config/constants.php`
2. Update the `APP_URL`:

```php
define('APP_URL', 'https://yourdomain.com');
```

### Step 7: Set File Permissions

```bash
chmod 755 uploads/
chmod 755 uploads/questions/
```

### Step 8: Test the Application

1. Visit your domain in a browser
2. Register a new user account
3. Login and try taking a quiz
4. Access admin panel at `/admin/login.php`

## Default Admin Credentials

```
Username: admin
Password: Admin@123
```

**Important:** Change the default admin password after first login!

## CSV Upload Format

When uploading questions via CSV, use the following column format:

| Column | Description | Required |
|--------|------------|----------|
| Question | Question text | Yes |
| Option A | First option | For MCQ |
| Option B | Second option | For MCQ |
| Option C | Third option | For MCQ |
| Option D | Fourth option | For MCQ |
| Correct Answer | A/B/C/D or text | Yes |
| Question Type | mcq/aptitude/coding | Yes |
| Marks | Points (default: 1) | No |
| Language | For coding questions | No |

A sample CSV file is included at `assets/sample_questions.csv`.

## Security Features

- **SQL Injection Protection** - All queries use PDO prepared statements
- **XSS Prevention** - All output is sanitized with `htmlspecialchars()`
- **CSRF Protection** - Token-based form validation
- **Password Hashing** - bcrypt with cost factor 12
- **Session Security** - HTTP-only cookies, strict mode, regeneration
- **File Upload Validation** - Type and size checks
- **Directory Protection** - `.htaccess` rules prevent direct access to sensitive directories

## Browser Support

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+
- Mobile browsers (iOS Safari, Chrome for Android)

## License

This project is built for UpDevix. All rights reserved.
