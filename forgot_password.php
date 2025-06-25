<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load PHPMailer with proper namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Autoload PHPMailer classes
require_once 'PHPMailer/Exception.php';
require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    
    $errors = [];
    
    // Validation
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    
    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($errors)) {
        // Check if user exists with this email
        $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour
            
            // Save reset token to database
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
            if ($stmt->execute([$reset_token, $reset_expires, $user['id']])) {
                // Send reset email
                $email_sent = sendResetEmail($user['username'], $email, $reset_token);
                
                if ($email_sent) {
                    $response = [
                        'success' => true, 
                        'message' => 'Password reset link sent to your email. Please check your inbox.'
                    ];
                } else {
                    $response = [
                        'success' => false, 
                        'message' => 'Could not send reset email. Please try again later.'
                    ];
                }
            } else {
                $response = ['success' => false, 'message' => 'Database error. Please try again.'];
            }
        } else {
            // Don't reveal if email exists or not for security
            $response = [
                'success' => true, 
                'message' => 'If an account with this email exists, a password reset link has been sent.'
            ];
        }
    } else {
        $response = ['success' => false, 'message' => implode(', ', $errors)];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Function to send password reset email
function sendResetEmail($username, $user_email, $reset_token) {
    global $email_config, $site_config;
    
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $email_config['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $email_config['smtp_username'];
        $mail->Password = $email_config['smtp_password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $email_config['smtp_port'];
        
        // Enable debug output for troubleshooting
        $mail->SMTPDebug = 0;
        
        // Recipients
        $mail->setFrom($email_config['from_email'], $email_config['from_name']);
        $mail->addAddress($user_email, $username);
        
        // Reset link
        $reset_link = $site_config['base_url'] . '/reset_password.php?token=' . $reset_token;
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request - ' . $site_config['site_name'];
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="background-color: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
                <h2 style="margin: 0;">' . $site_config['site_name'] . '</h2>
            </div>
            <div style="background-color: #f8f9fa; padding: 30px; border-radius: 0 0 5px 5px;">
                <h3 style="color: #333; margin-top: 0;">Hello ' . htmlspecialchars($username) . '!</h3>
                <p style="color: #666; line-height: 1.6;">We received a request to reset your password. Click the button below to create a new password:</p>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . $reset_link . '" style="background-color: #dc3545; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">Reset Password</a>
                </div>
                
                <p style="color: #666; line-height: 1.6;">If the button above doesn\'t work, you can copy and paste this link into your browser:</p>
                <p style="background-color: #e9ecef; padding: 10px; border-radius: 3px; word-break: break-all; font-size: 12px; color: #495057;">' . $reset_link . '</p>
                
                <p style="color: #666; line-height: 1.6;"><strong>This link will expire in 1 hour for security reasons.</strong></p>
                
                <hr style="border: none; border-top: 1px solid #dee2e6; margin: 30px 0;">
                
                <p style="color: #999; font-size: 12px; margin-bottom: 0;">If you didn\'t request a password reset, please ignore this email. Your password will remain unchanged.</p>
            </div>
        </div>';
        
        $mail->AltBody = 'Hello ' . $username . '! We received a request to reset your password. Please visit: ' . $reset_link . ' to create a new password. This link expires in 1 hour.';
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log error for debugging
        error_log("Password reset email failed for $user_email: " . $mail->ErrorInfo);
        return false;
    }
}
?> 