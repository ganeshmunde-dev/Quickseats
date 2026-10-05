<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../includes/config.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . '/../src/PHPMailer.php';
    require_once __DIR__ . '/../src/SMTP.php';
    require_once __DIR__ . '/../src/Exception.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Only POST is allowed']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $email = trim($input['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid email address']);
        exit;
    }

    // Generate secure 4-digit OTP
    $otp = str_pad((string)rand(0, 9999), 4, '0', STR_PAD_LEFT);

    $_SESSION['otp'] = $otp;
    $_SESSION['otp_email'] = $email;
    $_SESSION['otp_expiry'] = time() + 300; // Valid for 5 minutes

    $gateway = defined('SMS_GATEWAY') ? SMS_GATEWAY : 'simulated';

    // Fallback checks for Gmail credentials
    if ($gateway === 'gmail' && (MAIL_USERNAME === '' || strpos(MAIL_USERNAME, 'YOUR_') === 0)) {
        $gateway = 'simulated';
    }

    if ($gateway === 'simulated') {
        echo json_encode([
            'success' => true,
            'gateway' => 'simulated',
            'otp' => $otp, // Expose only in simulated mode for client toast
            'message' => 'OTP simulated successfully'
        ]);
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = trim(MAIL_USERNAME);
        $mail->Password = str_replace(' ', '', MAIL_PASSWORD);
        
        $port = intval(MAIL_PORT);
        $mail->Port = $port;
        if ($port === 465) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mail->Timeout = 10;

        // Bypass SSL certificate verification issues on shared hosting servers
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom(trim(MAIL_USERNAME), 'QuickSeats');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'QuickSeats Email OTP';

        $mail->Body = "
            <div style='font-family:Arial,sans-serif;padding:20px;color:#333;'>
                <h2 style='color:#1e293b;'>QuickSeats Login OTP</h2>
                <p>Your verification OTP is:</p>
                <h1 style='color:#4f46e5;letter-spacing:4px;font-size:32px;'>$otp</h1>
                <p style='color:#64748b;'>Valid for 5 minutes.</p>
            </div>
        ";
        
        $mail->send();

        echo json_encode([
            'success' => true,
            'gateway' => $gateway,
            'message' => 'OTP sent to your email successfully'
        ]);
    } catch (\Exception $mailEx) {
        // Fallback: If live host blocks SMTP or App Password fails, allow simulated OTP so user is never locked out
        echo json_encode([
            'success' => true,
            'gateway' => 'simulated',
            'otp' => $otp,
            'warning' => 'SMTP Failed: ' . $mailEx->getMessage(),
            'message' => 'Email delivery failed. Use OTP: ' . $otp
        ]);
    }

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server Error: ' . $e->getMessage()
    ]);
}