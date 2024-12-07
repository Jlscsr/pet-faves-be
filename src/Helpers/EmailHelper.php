<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use RuntimeException;

class EmailHelper
{
    // Email configuration settings for Gmail
    private static $smtpHost = 'smtp.gmail.com';
    private static $smtpPort = 587;
    private static $smtpUsername = 'hagcpetfaves@gmail.com'; // Your Gmail account
    private static $smtpPassword = 'icbz jlcx hzss nkrv';     // Your Gmail App Password
    private static $smtpFromEmail = 'hagcpetfaves@gmail.com';
    private static $smtpFromName = 'PetFaves';

    // Function to send email for account verification
    public static function sendEmailForAccountVerification(string $toEmail, string $subject, string $code): void
    {
        self::sendEmail($toEmail, $subject, self::getHTMLBodyForAccountVerification($code));
    }

    // Function to send email for password reset
    public static function sendEmailForPasswordReset(string $toEmail, string $subject, string $token): void
    {
        self::sendEmail($toEmail, $subject, self::getHTMLBodyForForgotPassword($token));
    }

    public static function sendInquiryEmail(string $userName, string $userEmail, string $phoneNo, string $message): void
    {
        $toEmail = self::$smtpFromEmail; // Send to the company email
        $subject = "New Inquiry from Contact Us: $userName";

        $htmlBody = self::getHTMLBodyForInquiry($userName, $userEmail, $phoneNo, $message);

        self::sendEmailWithCustomFrom($toEmail, $subject, $htmlBody, $userEmail, $userName);
    }

    // Private function to send email
    private static function sendEmail(string $toEmail, string $subject, string $htmlBody): void
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = self::$smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = self::$smtpUsername;
            $mail->Password   = self::$smtpPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = self::$smtpPort;

            // Recipients
            $mail->setFrom(self::$smtpFromEmail, self::$smtpFromName);
            $mail->addAddress($toEmail);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags($htmlBody);

            // Send the email
            $mail->send();
        } catch (Exception $e) {
            throw new RuntimeException("Message could not be sent. Mailer Error: {$e->getMessage()}");
        }
    }

    private static function sendEmailWithCustomFrom(string $toEmail, string $subject, string $htmlBody, string $fromEmail, string $fromName): void
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = self::$smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = self::$smtpUsername;
            $mail->Password   = self::$smtpPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = self::$smtpPort;

            // Set the 'from' email and name to the user's email and name
            $mail->setFrom(self::$smtpFromEmail, self::$smtpFromName); // Still use PetFaves as the sender for security

            // Set 'Reply-To' to user's email
            $mail->addReplyTo($fromEmail, $fromName);

            // Recipients
            $mail->addAddress($toEmail);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags($htmlBody);

            // Send the email
            $mail->send();
        } catch (Exception $e) {
            throw new RuntimeException("Message could not be sent. Mailer Error: {$e->getMessage()}");
        }
    }

    // Generate the HTML body for account verification email
    private static function getHTMLBodyForAccountVerification(string $code): string
    {
        return '
        <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 0; margin: 0; }
                    .container { max-width: 600px; margin: auto; padding: 20px; background: #fff; border-radius: 8px; }
                    h1 { color: #333; }
                    p { color: #555; }
                    .code { font-size: 24px; color: #007bff; font-weight: bold; padding: 10px; background: #f4f4f4; border-radius: 8px; }
                    .footer { margin-top: 20px; font-size: 12px; color: #888; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>Activate Your Account</h1>
                    <p>Please use the code below to activate your account:</p>
                    <div class="code">' . $code . '</div>
                    <p>If you did not request this, please ignore this email.</p>
                    <div class="footer">© 2024 PetFaves. All Rights Reserved.</div>
                </div>
            </body>
        </html>';
    }

    // Generate the HTML body for password reset email
    private static function getHTMLBodyForForgotPassword(string $token): string
    {
        return '
        <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 0; margin: 0; }
                    .container { max-width: 600px; margin: auto; padding: 20px; background: #fff; border-radius: 8px; }
                    h1 { color: #333; }
                    p { color: #555; }
                    .link { font-size: 16px; color: #007bff; font-weight: bold; text-decoration: none; }
                    .footer { margin-top: 20px; font-size: 12px; color: #888; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>Reset Your Password</h1>
                    <p>Click the link below to reset your password:</p>
                    <a class="link" href="https://pet-faves-2c3c8.web.app/resetPassword?token=' . $token . '">Reset Password</a>
                    <p>If you did not request this, please ignore this email.</p>
                    <div class="footer">© 2024 PetFaves. All Rights Reserved.</div>
                </div>
            </body>
        </html>';
    }

    private static function getHTMLBodyForInquiry(string $userName, string $userEmail, string $phoneNo, string $message): string
    {
        return '
        <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 0; margin: 0; }
                    .container { max-width: 600px; margin: auto; padding: 20px; background: #fff; border-radius: 8px; }
                    h1 { color: #333; }
                    p { color: #555; }
                    .footer { margin-top: 20px; font-size: 12px; color: #888; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>New Inquiry from Contact Us</h1>
                    <p><strong>Name:</strong> ' . htmlspecialchars($userName) . '</p>
                    <p><strong>Email:</strong> ' . htmlspecialchars($userEmail) . '</p>
                    <p><strong>Phone No.:</strong> ' . htmlspecialchars($phoneNo) . '</p>
                    <p><strong>Message:</strong></p>
                    <p>' . nl2br(htmlspecialchars($message)) . '</p>
                    <div class="footer">© 2024 PetFaves. All Rights Reserved.</div>
                </div>
            </body>
        </html>';
    }
}
