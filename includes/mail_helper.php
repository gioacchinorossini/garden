<?php
/**
 * Email Verification & SMTP Helper for IdleLand
 */

class IdleLandSMTP {
    private string $host;
    private int $port;
    private string $secure;
    private string $user;
    private string $pass;
    private int $timeout = 15;
    public string $error = '';

    public function __construct(string $host, int $port, string $secure, string $user, string $pass) {
        $this->host   = $host;
        $this->port   = $port;
        $this->secure = strtolower($secure);
        $this->user   = $user;
        $this->pass   = $pass;
    }

    private function getResponse($socket): string {
        $data = '';
        while ($str = fgets($socket, 515)) {
            $data .= $str;
            if (substr($str, 3, 1) === ' ') {
                break;
            }
        }
        return $data;
    }

    private function sendCmd($socket, string $cmd): string {
        fputs($socket, $cmd . "\r\n");
        return $this->getResponse($socket);
    }

    public function send(string $fromEmail, string $fromName, string $toEmail, string $subject, string $htmlBody): bool {
        $remote = ($this->secure === 'ssl' ? 'ssl://' : '') . $this->host . ':' . $this->port;
        $context = stream_context_create([
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ]);

        $socket = @stream_socket_client($remote, $errno, $errstr, $this->timeout, STREAM_CLIENT_CONNECT, $context);
        if (!$socket) {
            $this->error = "Could not connect to SMTP server ({$this->host}:{$this->port}): {$errstr} ({$errno})";
            return false;
        }

        $res = $this->getResponse($socket);
        if (substr($res, 0, 3) !== '220') {
            $this->error = "SMTP greeting rejected: {$res}";
            fclose($socket);
            return false;
        }

        $hostName = gethostname() ?: 'localhost';
        $res = $this->sendCmd($socket, "EHLO {$hostName}");

        if ($this->secure === 'tls') {
            $res = $this->sendCmd($socket, "STARTTLS");
            if (substr($res, 0, 3) !== '220') {
                $this->error = "STARTTLS command failed: {$res}";
                fclose($socket);
                return false;
            }
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                $this->error = "TLS handshake failed with SMTP server";
                fclose($socket);
                return false;
            }
            $res = $this->sendCmd($socket, "EHLO {$hostName}");
        }

        if (!empty($this->user) && !empty($this->pass)) {
            $res = $this->sendCmd($socket, "AUTH LOGIN");
            if (substr($res, 0, 3) !== '334') {
                $this->error = "AUTH LOGIN command rejected: {$res}";
                fclose($socket);
                return false;
            }
            $res = $this->sendCmd($socket, base64_encode($this->user));
            if (substr($res, 0, 3) !== '334') {
                $this->error = "SMTP Username rejected: {$res}";
                fclose($socket);
                return false;
            }
            $res = $this->sendCmd($socket, base64_encode($this->pass));
            if (substr($res, 0, 3) !== '235') {
                $this->error = "SMTP Authentication failed (check your email & password): {$res}";
                fclose($socket);
                return false;
            }
        }

        $res = $this->sendCmd($socket, "MAIL FROM:<{$fromEmail}>");
        if (substr($res, 0, 3) !== '250') {
            $this->error = "MAIL FROM rejected: {$res}";
            fclose($socket);
            return false;
        }

        $res = $this->sendCmd($socket, "RCPT TO:<{$toEmail}>");
        if (substr($res, 0, 3) !== '250') {
            $this->error = "RCPT TO rejected: {$res}";
            fclose($socket);
            return false;
        }

        $res = $this->sendCmd($socket, "DATA");
        if (substr($res, 0, 3) !== '354') {
            $this->error = "DATA command rejected: {$res}";
            fclose($socket);
            return false;
        }

        $headers = [];
        $headers[] = "Date: " . date('r');
        $headers[] = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>";
        $headers[] = "To: <{$toEmail}>";
        $headers[] = "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=";
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "Content-Transfer-Encoding: base64";

        $message = implode("\r\n", $headers) . "\r\n\r\n" . chunk_split(base64_encode($htmlBody)) . "\r\n.";
        $res = $this->sendCmd($socket, $message);
        if (substr($res, 0, 3) !== '250') {
            $this->error = "Message rejected by server: {$res}";
            fclose($socket);
            return false;
        }

        $this->sendCmd($socket, "QUIT");
        fclose($socket);
        return true;
    }
}

function generate_verification_code(): string {
    return (string)random_int(100000, 999999);
}

/**
 * Sends the verification code to the target email address.
 * 
 * @return array ['success' => bool, 'error' => string]
 */
function send_verification_email(string $to_email, string $code): array {
    $configFile = __DIR__ . '/email_config.php';
    $config = file_exists($configFile) ? require $configFile : [];

    $driver     = $config['driver'] ?? 'smtp';
    $smtpHost   = $config['smtp_host'] ?? 'smtp.gmail.com';
    $smtpPort   = (int)($config['smtp_port'] ?? 587);
    $smtpSecure = $config['smtp_secure'] ?? 'tls';
    $smtpUser   = trim($config['smtp_user'] ?? '');
    $smtpPass   = trim($config['smtp_pass'] ?? '');
    $fromEmail  = !empty($config['from_email']) ? $config['from_email'] : ($smtpUser ?: 'noreply@idleland.local');
    $fromName   = $config['from_name'] ?? 'IdleLand Gardening';

    $subject = "Your IdleLand Verification Code: {$code}";

    $html_message = '
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verification Code</title>
</head>
<body style="margin: 0; padding: 24px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; background-color: #eef5f0; color: #191c1a;">
  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td align="center">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 480px; background-color: #ffffff; border-radius: 20px; border: 1px solid #e1e3e1; padding: 32px; box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
          <tr>
            <td align="center" style="padding-bottom: 16px;">
              <div style="font-size: 26px; font-weight: 700; color: #198754; letter-spacing: -0.5px;">
                Idle<span style="color: #191c1a;">Land</span>
              </div>
              <div style="font-size: 13px; color: #707973; margin-top: 4px;">Community Gardening Platform</div>
            </td>
          </tr>
          <tr>
            <td style="padding: 16px 0;">
              <h2 style="font-size: 18px; font-weight: 600; color: #191c1a; margin: 0 0 8px 0;">Confirm your email address</h2>
              <p style="font-size: 14px; line-height: 1.5; color: #404943; margin: 0 0 20px 0;">
                You are creating an account on IdleLand. Use the verification code below to verify your email address:
              </p>
              
              <div style="background-color: #e8f7ec; border: 1.5px dashed #198754; border-radius: 12px; padding: 18px 24px; text-align: center; margin-bottom: 20px;">
                <span style="font-family: Consolas, Monaco, monospace; font-size: 32px; font-weight: 700; letter-spacing: 8px; color: #0f5132;">' . htmlspecialchars($code) . '</span>
                <div style="font-size: 12px; color: #198754; font-weight: 500; margin-top: 6px;">Expires in 15 minutes</div>
              </div>

              <p style="font-size: 12px; line-height: 1.5; color: #707973; margin: 0;">
                If you did not request this code, you can safely ignore this email.
              </p>
            </td>
          </tr>
          <tr>
            <td style="border-top: 1px solid #e1e3e1; padding-top: 20px; text-align: center;">
              <p style="font-size: 11px; color: #9aa0a6; margin: 0;">
                &copy; ' . date('Y') . ' IdleLand Gardening System. All rights reserved.
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>';

    // 1. Send via SMTP if configured
    if ($driver === 'smtp') {
        if (empty($smtpUser) || empty($smtpPass)) {
            // Check if PHP's built-in mail() can be used as fallback
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: ' . $fromName . ' <' . $fromEmail . '>',
                'Reply-To: ' . $fromEmail,
                'X-Mailer: PHP/' . phpversion()
            ];
            $mailSent = @mail($to_email, $subject, $html_message, implode("\r\n", $headers));
            if ($mailSent) {
                return ['success' => true, 'error' => ''];
            }

            return [
                'success' => false,
                'error'   => 'SMTP credentials are not configured in includes/email_config.php. Please set your SMTP email and App Password to deliver verification emails.'
            ];
        }

        $smtp = new IdleLandSMTP($smtpHost, $smtpPort, $smtpSecure, $smtpUser, $smtpPass);
        $sent = $smtp->send($fromEmail, $fromName, $to_email, $subject, $html_message);

        if ($sent) {
            return ['success' => true, 'error' => ''];
        } else {
            return ['success' => false, 'error' => $smtp->error];
        }
    }

    // 2. Default mail() fallback
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'Reply-To: ' . $fromEmail,
        'X-Mailer: PHP/' . phpversion()
    ];
    $sent = @mail($to_email, $subject, $html_message, implode("\r\n", $headers));
    if ($sent) {
        return ['success' => true, 'error' => ''];
    }

    return [
        'success' => false,
        'error'   => 'Failed to send verification email via system mailer.'
    ];
}
