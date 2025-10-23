<?php
/**
 * P.I.M.P - Email Service
 * Handles all email operations including verification
 */

namespace PIMP\Services;

use PIMP\Models\UserModel;
use PIMP\Services\Database\MySQLDatabase;
use PIMP\Core\Config;
use Exception;

class EmailService
{
    private $db;
    private $userModel;
    private $fromEmail;
    private $fromName;

    public function __construct(MySQLDatabase $db)
    {
        $this->db = $db;
        $this->userModel = new UserModel($db);
        $this->fromEmail = 'noreply@pimp-platform.com';
        $this->fromName = 'P.I.M.P Business Repository';
    }

    /**
     * Send verification email
     * 
     * @param string $email
     * @param string $name
     * @param string $token
     * @return bool
     */
    public function sendVerificationEmail(string $email, string $name, string $token): bool
    {
        try {
            $subject = 'Verify Your P.I.M.P Account';
            $verificationUrl = Config::url("/verify-email?token={$token}");

            $htmlBody = $this->getVerificationEmailTemplate($name, $verificationUrl);
            $textBody = "Hi {$name},\n\nPlease verify your email by clicking this link: {$verificationUrl}\n\nThis link will expire in 24 hours.";

            return $this->sendEmail($email, $subject, $htmlBody, $textBody);

        } catch (Exception $e) {
            error_log("Failed to send verification email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify email with token
     * 
     * @param string $token
     * @return array
     */
    public function verifyEmail(string $token): array
    {
        try {
            // Find user with this token
            $setting = $this->db->fetchOne(
                "SELECT user_id, setting_value 
                 FROM user_settings 
                 WHERE setting_key = 'verification_token'",
                []
            );

            if (!$setting) {
                return [
                    'success' => false,
                    'error' => 'Invalid verification token',
                    'status' => 'invalid'
                ];
            }

            $tokenData = json_decode($setting['setting_value'], true);

            // Check if token matches
            if ($tokenData['token'] !== $token) {
                return [
                    'success' => false,
                    'error' => 'Invalid verification token',
                    'status' => 'invalid'
                ];
            }

            // Check if token expired
            if (strtotime($tokenData['expires_at']) < time()) {
                return [
                    'success' => false,
                    'error' => 'Verification token has expired',
                    'status' => 'expired'
                ];
            }

            // Update user status
            $this->db->query(
                "UPDATE users SET status = 'active', email_verified = 1 WHERE id = ?",
                [$setting['user_id']]
            );

            // Delete verification token
            $this->db->query(
                "DELETE FROM user_settings WHERE user_id = ? AND setting_key = 'verification_token'",
                [$setting['user_id']]
            );

            // Log audit
            $this->logAudit($setting['user_id'], 'email_verified');

            return [
                'success' => true,
                'message' => 'Email verified successfully',
                'status' => 'success'
            ];

        } catch (Exception $e) {
            error_log("Email verification error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'An error occurred during verification',
                'status' => 'error'
            ];
        }
    }

    /**
     * Resend verification email
     * 
     * @param string $email
     * @return array
     */
    public function resendVerificationEmail(string $email): array
    {
        try {
            $user = $this->userModel->findByEmail($email);

            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'User not found'
                ];
            }

            if ($user['email_verified']) {
                return [
                    'success' => false,
                    'error' => 'Email is already verified'
                ];
            }

            // Check cooldown (60 seconds between resends)
            $lastResend = $this->getLastResendTime($user['id']);
            if ($lastResend && (time() - $lastResend) < 60) {
                $remaining = 60 - (time() - $lastResend);
                return [
                    'success' => false,
                    'error' => "Please wait {$remaining} seconds before requesting another email",
                    'cooldown' => $remaining
                ];
            }

            // Generate new token
            $token = $this->generateVerificationToken($user['id']);

            // Send email
            $nameData = json_decode($user['name_json'], true);
            $firstName = $nameData['first_name'] ?? 'User';
            
            $sent = $this->sendVerificationEmail($email, $firstName, $token);

            if ($sent) {
                // Update last resend time
                $this->setLastResendTime($user['id']);

                return [
                    'success' => true,
                    'message' => 'Verification email sent successfully'
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to send verification email'
            ];

        } catch (Exception $e) {
            error_log("Resend verification email error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'An error occurred while sending email'
            ];
        }
    }

    /**
     * Send password reset email
     * 
     * @param string $email
     * @return array
     */
    public function sendPasswordResetEmail(string $email): array
    {
        try {
            $user = $this->userModel->findByEmail($email);

            if (!$user) {
                // Don't reveal that user doesn't exist
                return [
                    'success' => true,
                    'message' => 'If an account exists with this email, you will receive a password reset link'
                ];
            }

            // Generate reset token
            $resetToken = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $this->db->query(
                "INSERT INTO user_settings (user_id, setting_key, setting_value, data_type)
                 VALUES (?, 'password_reset_token', ?, 'string')
                 ON DUPLICATE KEY UPDATE setting_value = ?",
                [
                    $user['id'],
                    json_encode(['token' => $resetToken, 'expires_at' => $expiresAt]),
                    json_encode(['token' => $resetToken, 'expires_at' => $expiresAt])
                ]
            );

            // Send reset email
            $nameData = json_decode($user['name_json'], true);
            $firstName = $nameData['first_name'] ?? 'User';
            $resetUrl = Config::url("/reset-password?token={$resetToken}");

            $subject = 'Reset Your P.I.M.P Password';
            $htmlBody = $this->getPasswordResetEmailTemplate($firstName, $resetUrl);
            $textBody = "Hi {$firstName},\n\nClick here to reset your password: {$resetUrl}\n\nThis link will expire in 1 hour.";

            $this->sendEmail($email, $subject, $htmlBody, $textBody);

            return [
                'success' => true,
                'message' => 'If an account exists with this email, you will receive a password reset link'
            ];

        } catch (Exception $e) {
            error_log("Password reset email error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'An error occurred while sending email'
            ];
        }
    }

    /**
     * Send welcome email
     * 
     * @param string $email
     * @param string $name
     * @return bool
     */
    public function sendWelcomeEmail(string $email, string $name): bool
    {
        try {
            $subject = 'Welcome to P.I.M.P Business Repository';
            $htmlBody = $this->getWelcomeEmailTemplate($name);
            $textBody = "Welcome to P.I.M.P, {$name}!\n\nThank you for joining our community.";

            return $this->sendEmail($email, $subject, $htmlBody, $textBody);

        } catch (Exception $e) {
            error_log("Failed to send welcome email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email using PHP mail() function
     * 
     * @param string $to
     * @param string $subject
     * @param string $htmlBody
     * @param string $textBody
     * @return bool
     */
    private function sendEmail(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        $headers = [
            'From' => "{$this->fromName} <{$this->fromEmail}>",
            'Reply-To' => $this->fromEmail,
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Mailer' => 'PHP/' . phpversion()
        ];

        $headerString = '';
        foreach ($headers as $key => $value) {
            $headerString .= "{$key}: {$value}\r\n";
        }

        // In production, use a proper email service like SendGrid, Mailgun, etc.
        return mail($to, $subject, $htmlBody, $headerString);
    }

    /**
     * Get verification email template
     * 
     * @param string $name
     * @param string $verificationUrl
     * @return string
     */
    private function getVerificationEmailTemplate(string $name, string $verificationUrl): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f9fafb; }
                .button { display: inline-block; padding: 12px 30px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>P.I.M.P Business Repository</h1>
                </div>
                <div class='content'>
                    <h2>Welcome, {$name}!</h2>
                    <p>Thank you for registering with P.I.M.P. To complete your registration, please verify your email address by clicking the button below:</p>
                    <p style='text-align: center;'>
                        <a href='{$verificationUrl}' class='button'>Verify Email Address</a>
                    </p>
                    <p>Or copy and paste this link into your browser:</p>
                    <p style='word-break: break-all; color: #2563eb;'>{$verificationUrl}</p>
                    <p>This link will expire in 24 hours.</p>
                    <p>If you didn't create an account with P.I.M.P, you can safely ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2025 P.I.M.P Business Repository. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Get password reset email template
     * 
     * @param string $name
     * @param string $resetUrl
     * @return string
     */
    private function getPasswordResetEmailTemplate(string $name, string $resetUrl): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f9fafb; }
                .button { display: inline-block; padding: 12px 30px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>P.I.M.P Business Repository</h1>
                </div>
                <div class='content'>
                    <h2>Password Reset Request</h2>
                    <p>Hi {$name},</p>
                    <p>We received a request to reset your password. Click the button below to create a new password:</p>
                    <p style='text-align: center;'>
                        <a href='{$resetUrl}' class='button'>Reset Password</a>
                    </p>
                    <p>Or copy and paste this link into your browser:</p>
                    <p style='word-break: break-all; color: #2563eb;'>{$resetUrl}</p>
                    <p>This link will expire in 1 hour.</p>
                    <p>If you didn't request a password reset, you can safely ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2025 P.I.M.P Business Repository. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Get welcome email template
     * 
     * @param string $name
     * @return string
     */
    private function getWelcomeEmailTemplate(string $name): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f9fafb; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome to P.I.M.P!</h1>
                </div>
                <div class='content'>
                    <h2>Hi {$name},</h2>
                    <p>Your email has been verified and your account is now active!</p>
                    <p>You can now:</p>
                    <ul>
                        <li>Write and read reviews</li>
                        <li>Save your favorite businesses</li>
                        <li>Receive notifications about businesses you follow</li>
                        <li>Contribute to our trusted community</li>
                    </ul>
                    <p>Thank you for joining P.I.M.P Business Repository!</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2025 P.I.M.P Business Repository. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Generate verification token
     * 
     * @param int $userId
     * @return string
     */
    private function generateVerificationToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $this->db->query(
            "INSERT INTO user_settings (user_id, setting_key, setting_value, data_type)
             VALUES (?, 'verification_token', ?, 'string')
             ON DUPLICATE KEY UPDATE setting_value = ?",
            [
                $userId,
                json_encode(['token' => $token, 'expires_at' => $expiresAt]),
                json_encode(['token' => $token, 'expires_at' => $expiresAt])
            ]
        );

        return $token;
    }

    /**
     * Get last resend time
     * 
     * @param int $userId
     * @return int|null
     */
    private function getLastResendTime(int $userId): ?int
    {
        $setting = $this->db->fetchOne(
            "SELECT setting_value FROM user_settings WHERE user_id = ? AND setting_key = 'last_resend_time'",
            [$userId]
        );

        return $setting ? (int)json_decode($setting['setting_value']) : null;
    }

    /**
     * Set last resend time
     * 
     * @param int $userId
     * @return void
     */
    private function setLastResendTime(int $userId): void
    {
        $this->db->query(
            "INSERT INTO user_settings (user_id, setting_key, setting_value, data_type)
             VALUES (?, 'last_resend_time', ?, 'number')
             ON DUPLICATE KEY UPDATE setting_value = ?",
            [$userId, json_encode(time()), json_encode(time())]
        );
    }

    /**
     * Log audit event
     * 
     * @param int $userId
     * @param string $action
     * @return void
     */
    private function logAudit(int $userId, string $action): void
    {
        $this->db->query(
            "INSERT INTO audit_logs (event_type, entity_type, entity_id, action, ip_address, user_agent)
             VALUES (?, 'user', ?, ?, ?, ?)",
            [
                'email_verification',
                $userId,
                $action,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]
        );
    }
}
