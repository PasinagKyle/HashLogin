<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load PHPMailer with proper namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    // Validation
    if (empty($username)) $errors[] = "Username is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($password)) $errors[] = "Password is required";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    if (strlen($password) < 5) $errors[] = "Password must be at least 5 characters";
    
    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Check if username or email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Username or email already exists";
        }
    }
    
    // If no errors, register the user
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $verification_token = bin2hex(random_bytes(32)); // Generate verification token
        $created_at = date('Y-m-d H:i:s');
        
        // Insert user with verification token
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, verification_token, is_verified, created_at, updated_at) VALUES (?, ?, ?, ?, 0, ?, ?)");
        if ($stmt->execute([$username, $email, $password_hash, $verification_token, $created_at, $created_at])) {
            // Send verification email
            $email_sent = sendVerificationEmail($username, $email, $verification_token);
            
            if ($email_sent) {
                $response = [
                    'success' => true, 
                    'message' => 'Registration successful! Please check your email to verify your account.'
                ];
            } else {
                // If email fails, still create account but inform user
            $response = [
                'success' => true, 
                    'message' => 'Registration successful! However, verification email could not be sent. Please contact support.'
            ];
            }
        } else {
            $errorInfo = $stmt->errorInfo();
            $response = ['success' => false, 'message' => 'Registration failed: ' . $errorInfo[2]];
        }
    } else {
        $response = ['success' => false, 'message' => implode(', ', $errors)];
    }
    
    // Ensure proper JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Function to send verification email
function sendVerificationEmail($username, $user_email, $verification_token) {
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
        $mail->SMTPDebug = 0; // Set to 2 for debugging
        
        // Recipients
        $mail->setFrom($email_config['from_email'], $email_config['from_name']);
        $mail->addAddress($user_email, $username);
        
        // Verification link
        $verification_link = $site_config['base_url'] . '/verify.php?token=' . $verification_token;
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email - ' . $site_config['site_name'];
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="background-color: #007bff; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
                <h2 style="margin: 0;">' . $site_config['site_name'] . '</h2>
            </div>
            <div style="background-color: #f8f9fa; padding: 30px; border-radius: 0 0 5px 5px;">
                <h3 style="color: #333; margin-top: 0;">Hello ' . htmlspecialchars($username) . '!</h3>
                <p style="color: #666; line-height: 1.6;">Thank you for registering with us! To complete your registration, please verify your email address by clicking the button below:</p>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . $verification_link . '" style="background-color: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">Verify Email Address</a>
                </div>
                
                <p style="color: #666; line-height: 1.6;">If the button above doesn\'t work, you can copy and paste this link into your browser:</p>
                <p style="background-color: #e9ecef; padding: 10px; border-radius: 3px; word-break: break-all; font-size: 12px; color: #495057;">' . $verification_link . '</p>
                
                <p style="color: #666; line-height: 1.6;">This verification link will expire in 24 hours for security reasons.</p>
                
                <hr style="border: none; border-top: 1px solid #dee2e6; margin: 30px 0;">
                
                <p style="color: #999; font-size: 12px; margin-bottom: 0;">If you didn\'t create an account with us, please ignore this email.</p>
            </div>
        </div>';
        
        $mail->AltBody = 'Hello ' . $username . '! Thank you for registering with ' . $site_config['site_name'] . '. Please verify your email by visiting: ' . $verification_link;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log error for debugging
        error_log("Email sending failed for $user_email: " . $mail->ErrorInfo);
        return false;
    }
}
?> 