<?php
include 'config.php';

$message = '';
$message_type = '';

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = trim($_GET['token']);
    
    try {
        // Find user with this verification token
        $stmt = $pdo->prepare("SELECT id, username, email, is_verified, created_at FROM users WHERE verification_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            if ($user['is_verified'] == 1) {
                $message = "Your email has already been verified. You can now log in to your account.";
                $message_type = 'success';
            } else {
                // Check if token is not expired (24 hours)
                $created_time = strtotime($user['created_at']);
                $current_time = time();
                $time_diff = $current_time - $created_time;
                
                if ($time_diff > 86400) { // 24 hours = 86400 seconds
                    $message = "Verification link has expired. Please register again or contact support.";
                    $message_type = 'error';
                } else {
                    // Mark user as verified
                    $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
                    if ($stmt->execute([$user['id']])) {
                        $message = "Email verified successfully! You can now log in to your account.";
                        $message_type = 'success';
                    } else {
                        $message = "Verification failed. Please try again or contact support.";
                        $message_type = 'error';
                    }
                }
            }
        } else {
            $message = "Invalid verification link. Please check your email or contact support.";
            $message_type = 'error';
        }
    } catch (Exception $e) {
        $message = "An error occurred during verification. Please try again.";
        $message_type = 'error';
        error_log("Verification error: " . $e->getMessage());
    }
} else {
    $message = "Invalid verification link.";
    $message_type = 'error';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - <?php echo $site_config['site_name']; ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .verification-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .verification-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .success-icon { color: #28a745; }
        .error-icon { color: #dc3545; }
        .verification-message {
            margin: 20px 0;
            padding: 15px;
            border-radius: 5px;
            font-size: 16px;
            line-height: 1.5;
        }
        .verification-message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .verification-message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .login-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .login-link:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="verification-container">
            <div class="verification-icon <?php echo $message_type; ?>-icon">
                <?php if ($message_type == 'success'): ?>
                    ✓
                <?php else: ?>
                    ✗
                <?php endif; ?>
            </div>
            
            <h2>Email Verification</h2>
            
            <div class="verification-message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            
            <?php if ($message_type == 'success'): ?>
                <a href="index.html" class="login-link">Go to Login</a>
            <?php else: ?>
                <a href="index.html" class="login-link">Back to Registration</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 