<?php
/**
 * Updevix Quiz Platform - Email Functions using PHPMailer
 */

require_once __DIR__ . '/../config/smtp.php';
require_once __DIR__ . '/../vendor/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/PHPMailer/SMTP.php';
require_once __DIR__ . '/../vendor/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Send email using PHPMailer
 */
function sendEmail($toEmail, $toName, $subject, $htmlBody) {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);
        $mail->CharSet = 'UTF-8';
        
        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully.'];
    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
        return ['success' => false, 'message' => 'Email could not be sent. ' . $mail->ErrorInfo];
    }
}

/**
 * Send OTP Email with branded template
 */
function sendOTPEmail($toEmail, $toName, $otp) {
    $subject = 'Your Updevix Quiz Verification Code';
    
    $htmlBody = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin: 0; padding: 0; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f4f8;">
        <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff;">
            <!-- Header -->
            <div style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); padding: 30px 40px; text-align: center;">
                <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 700; letter-spacing: 1px;">
                    <span style="color: #6c63ff;">Up</span>Devix Quiz
                </h1>
                <p style="color: #a0aec0; margin: 8px 0 0 0; font-size: 14px;">Your Learning Platform</p>
            </div>
            
            <!-- Body -->
            <div style="padding: 40px;">
                <h2 style="color: #1a1a2e; margin: 0 0 10px 0; font-size: 22px;">Verification Code</h2>
                <p style="color: #4a5568; line-height: 1.6; margin: 0 0 25px 0;">
                    Hello <strong>' . htmlspecialchars($toName) . '</strong>,
                </p>
                <p style="color: #4a5568; line-height: 1.6; margin: 0 0 25px 0;">
                    You requested a verification code for your Updevix Quiz account. Use the code below to complete your action:
                </p>
                
                <!-- OTP Box -->
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 25px; text-align: center; margin: 30px 0;">
                    <p style="color: #e2e8f0; margin: 0 0 10px 0; font-size: 14px; text-transform: uppercase; letter-spacing: 2px;">Your OTP Code</p>
                    <h1 style="color: #ffffff; margin: 0; font-size: 42px; letter-spacing: 12px; font-weight: 700;">' . $otp . '</h1>
                </div>
                
                <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 0 8px 8px 0; margin: 25px 0;">
                    <p style="color: #856404; margin: 0; font-size: 14px;">
                        <strong>Important:</strong> This code expires in <strong>' . OTP_EXPIRY_MINUTES . ' minutes</strong>. Do not share this code with anyone.
                    </p>
                </div>
                
                <p style="color: #4a5568; line-height: 1.6; margin: 25px 0 0 0;">
                    If you did not request this code, please ignore this email or contact our support team.
                </p>
            </div>
            
            <!-- Footer -->
            <div style="background-color: #f7fafc; padding: 25px 40px; text-align: center; border-top: 1px solid #e2e8f0;">
                <p style="color: #718096; margin: 0 0 5px 0; font-size: 13px;">
                    &copy; ' . date('Y') . ' Updevix Quiz. All rights reserved.
                </p>
                <p style="color: #a0aec0; margin: 0; font-size: 12px;">
                    This is an automated email. Please do not reply.
                </p>
            </div>
        </div>
    </body>
    </html>';
    
    return sendEmail($toEmail, $toName, $subject, $htmlBody);
}
