<?php
/**
 * IdleLand Email / SMTP Configuration
 * 
 * To send real emails (e.g. via Gmail, Outlook, or Brevo):
 * 1. Set 'smtp_user' to your email address (e.g., youraccount@gmail.com)
 * 2. Set 'smtp_pass' to your 16-character App Password (from Google Account > Security > App Passwords)
 * 3. Set 'from_email' to match your smtp_user email
 */

return [
    // Driver: 'smtp' (recommended for Windows / XAMPP) or 'mail' (PHP mail() function)
    'driver'      => 'smtp',

    // SMTP Server (Default: Gmail SMTP)
    'smtp_host'   => 'smtp.gmail.com',
    'smtp_port'   => 587,
    'smtp_secure' => 'tls', // 'tls' for 587, 'ssl' for 465

    // SMTP Credentials
    'smtp_user'   => '', // Enter your SMTP email address here
    'smtp_pass'   => '', // Enter your SMTP / App password here

    // Sender Information
    'from_email'  => 'noreply@idleland.local',
    'from_name'   => 'IdleLand Gardening',
];
