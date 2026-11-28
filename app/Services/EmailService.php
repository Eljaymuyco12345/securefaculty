<?php
/**
 * EmailService
 * Pure PHP Gmail SMTP Client (STARTTLS on Port 587) with Zero External Dependencies.
 * Sends 6-digit OTP verification emails for Forgot Password recovery.
 */

class EmailService {
    /**
     * Check whether real Gmail SMTP credentials have been configured (not placeholders).
     */
    public static function isConfigured(): bool {
        $user = defined('SMTP_USER') ? trim(SMTP_USER) : '';
        $pass = defined('SMTP_PASS') ? trim(SMTP_PASS) : '';

        if ($user === '' || $pass === '') {
            return false;
        }
        if ($user === 'YOUR_GMAIL_ADDRESS@gmail.com' || $pass === 'YOUR_16_CHAR_APP_PASSWORD') {
            return false;
        }
        return filter_var($user, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Send a 6-digit OTP password reset email to the user.
     *
     * @return array{success: bool, message: string}
     */
    public static function sendOtpEmail(string $toEmail, string $recipientName, string $otpCode): array {
        if (!self::isConfigured()) {
            return [
                'success' => false,
                'message' => 'Email service is not configured yet. Please set SMTP_USER and SMTP_PASS in app/config.php.'
            ];
        }

        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid recipient email address.'];
        }

        $expiry = defined('OTP_EXPIRY_MINUTES') ? (int)OTP_EXPIRY_MINUTES : 15;
        $subject = "SecureFaculty Password Reset OTP: {$otpCode}";
        $htmlBody = self::buildOtpHtml($recipientName, $otpCode, $expiry);

        return self::sendSmtpEmail($toEmail, $subject, $htmlBody);
    }

    /**
     * Build clean HTML email body for the 6-digit OTP.
     */
    public static function buildOtpHtml(string $recipientName, string $otpCode, int $expiryMinutes = 15): string {
        $safeName = htmlspecialchars($recipientName ?: 'Faculty Member', ENT_QUOTES, 'UTF-8');
        $safeOtp = htmlspecialchars($otpCode, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"></head>
<body style="font-family: 'Segoe UI', Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 24px; color: #0f172a;">
  <div style="max-width: 480px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
    <div style="background: #0d2342; padding: 20px 24px; color: #ffffff;">
      <h2 style="margin: 0; font-size: 18px; font-weight: 700;">SecureFaculty CICI Portal</h2>
      <p style="margin: 4px 0 0; font-size: 12px; color: #93c5fd;">ISUFST Main Campus &bull; Password Recovery</p>
    </div>
    <div style="padding: 24px;">
      <p style="margin-top: 0; font-size: 14px;">Hello <strong>{$safeName}</strong>,</p>
      <p style="font-size: 13px; color: #475569; line-height: 1.5;">
        We received a request to reset your SecureFaculty account password. Use the 6-digit One-Time Password (OTP) below to complete your reset:
      </p>
      <div style="margin: 20px 0; padding: 16px; background: #f8fafc; border: 1px dashed #94a3b8; border-radius: 8px; text-align: center;">
        <span style="font-family: 'Courier New', monospace; font-size: 28px; font-weight: 800; letter-spacing: 6px; color: #0d2342;">{$safeOtp}</span>
      </div>
      <p style="font-size: 12px; color: #64748b; line-height: 1.5;">
        This code will expire in <strong>{$expiryMinutes} minutes</strong>. If you did not request a password reset, you can safely ignore this email.
      </p>
    </div>
    <div style="background: #f8fafc; padding: 12px 24px; border-top: 1px solid #e2e8f0; font-size: 11px; color: #94a3b8; text-align: center;">
      Compliant with RA 10173 (Data Privacy Act of 2012) &bull; Do not share this OTP with anyone.
    </div>
  </div>
</body>
</html>
HTML;
    }

    /**
     * Send an email via Gmail SMTP (STARTTLS on port 587) using pure PHP sockets.
     *
     * @return array{success: bool, message: string}
     */
    public static function sendSmtpEmail(string $toEmail, string $subject, string $htmlBody): array {
        $host = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
        $port = defined('SMTP_PORT') ? (int)SMTP_PORT : 587;
        $user = defined('SMTP_USER') ? SMTP_USER : '';
        $pass = defined('SMTP_PASS') ? SMTP_PASS : '';
        $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'SecureFaculty CICI Portal';

        $errno = 0;
        $errstr = '';
        $socket = @stream_socket_client(
            "tcp://{$host}:{$port}",
            $errno,
            $errstr,
            15,
            STREAM_CLIENT_CONNECT
        );

        if (!$socket) {
            return [
                'success' => false,
                'message' => "Unable to connect to mail server ({$host}:{$port}). Check internet connection. [{$errstr}]"
            ];
        }

        stream_set_timeout($socket, 15);

        try {
            self::expectSmtpCode($socket, 220);

            $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
            self::sendSmtpCommand($socket, "EHLO {$serverName}", 250);

            // Upgrade to TLS via STARTTLS
            self::sendSmtpCommand($socket, "STARTTLS", 220);
            $cryptoOk = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT);
            if (!$cryptoOk) {
                throw new RuntimeException('Failed to establish TLS encryption with Gmail SMTP.');
            }

            self::sendSmtpCommand($socket, "EHLO {$serverName}", 250);

            // Authenticate using AUTH LOGIN
            self::sendSmtpCommand($socket, "AUTH LOGIN", 334);
            self::sendSmtpCommand($socket, base64_encode($user), 334);
            self::sendSmtpCommand($socket, base64_encode($pass), 235);

            // Mail transaction
            self::sendSmtpCommand($socket, "MAIL FROM:<{$user}>", 250);
            self::sendSmtpCommand($socket, "RCPT TO:<{$toEmail}>", 250);
            self::sendSmtpCommand($socket, "DATA", 354);

            $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
            $encodedFrom = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
            $headers = [
                "Date: " . date('r'),
                "From: {$encodedFrom} <{$user}>",
                "To: <{$toEmail}>",
                "Subject: {$encodedSubject}",
                "MIME-Version: 1.0",
                "Content-Type: text/html; charset=UTF-8",
                "Content-Transfer-Encoding: base64",
            ];

            $bodyBase64 = chunk_split(base64_encode($htmlBody));
            $message = implode("\r\n", $headers) . "\r\n\r\n" . $bodyBase64 . "\r\n.\r\n";

            fwrite($socket, $message);
            self::expectSmtpCode($socket, 250);

            @fwrite($socket, "QUIT\r\n");
            @fclose($socket);

            return ['success' => true, 'message' => 'OTP email sent successfully.'];
        } catch (Throwable $e) {
            @fclose($socket);
            return ['success' => false, 'message' => 'SMTP Error: ' . $e->getMessage()];
        }
    }

    private static function sendSmtpCommand($socket, string $command, int $expectedCode): string {
        fwrite($socket, $command . "\r\n");
        return self::expectSmtpCode($socket, $expectedCode);
    }

    private static function expectSmtpCode($socket, int $expectedCode): string {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            // Multi-line SMTP responses have '-' at index 3; final line has ' '
            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }
        $code = (int)substr($response, 0, 3);
        if ($code !== $expectedCode) {
            throw new RuntimeException("Expected SMTP {$expectedCode}, got {$code}: " . trim($response));
        }
        return $response;
    }
}
