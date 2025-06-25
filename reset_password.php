<?php
include 'config.php';

$message = '';
$message_type = '';
$token_valid = false;
$token = '';

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = trim($_GET['token']);
    
    try {
        // Find user with this reset token
        $stmt = $pdo->prepare("SELECT id, username, email, reset_expires FROM users WHERE reset_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Check if token is not expired
            $expires_time = strtotime($user['reset_expires']);
            $current_time = time();
            
            if ($current_time <= $expires_time) {
                $token_valid = true;
            } else {
                $message = "Password reset link has expired. Please request a new one.";
                $message_type = 'error';
            }
        } else {
            $message = "Invalid password reset link.";
            $message_type = 'error';
        }
    } catch (Exception $e) {
        $message = "An error occurred. Please try again.";
        $message_type = 'error';
        error_log("Reset password error: " . $e->getMessage());
    }
} else {
    $message = "Invalid password reset link.";
    $message_type = 'error';
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $token_valid) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    // Validation
    if (empty($new_password)) $errors[] = "New password is required";
    if (strlen($new_password) < 6) $errors[] = "Password must be at least 6 characters";
    if ($new_password !== $confirm_password) $errors[] = "Passwords do not match";
    
    if (empty($errors)) {
        try {
            // Hash the new password
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password and clear reset token
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?");
            if ($stmt->execute([$password_hash, $token])) {
                $message = "Password reset successfully! You can now login with your new password.";
                $message_type = 'success';
                $token_valid = false; // Hide the form
            } else {
                $message = "Password reset failed. Please try again.";
                $message_type = 'error';
            }
        } catch (Exception $e) {
            $message = "An error occurred during password reset.";
            $message_type = 'error';
            error_log("Password reset error: " . $e->getMessage());
        }
    } else {
        $message = implode(', ', $errors);
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo $site_config['site_name']; ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .reset-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .reset-icon {
            font-size: 48px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success-icon { color: #28a745; }
        .error-icon { color: #dc3545; }
        .reset-message {
            margin: 20px 0;
            padding: 15px;
            border-radius: 5px;
            font-size: 16px;
            line-height: 1.5;
        }
        .reset-message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .reset-message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .reset-form {
            margin-top: 20px;
        }
        .reset-form input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .reset-btn {
            width: 100%;
            padding: 12px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }
        .reset-btn:hover {
            background-color: #c82333;
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
        <div class="reset-container">
            <div class="reset-icon <?php echo $message_type; ?>-icon">
                <?php if ($message_type == 'success'): ?>
                    ✓
                <?php else: ?>
                    🔒
                <?php endif; ?>
            </div>
            
            <h2>Reset Password</h2>
            
            <?php if (!empty($message)): ?>
                <div class="reset-message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($token_valid): ?>
                <form method="POST" class="reset-form">
                    <input type="password" name="new_password" placeholder="New Password" required minlength="6">
                    <input type="password" name="confirm_password" placeholder="Confirm New Password" required minlength="6">
                    <button type="submit" class="reset-btn">Reset Password</button>
                </form>
            <?php endif; ?>
            
            <a href="index.html" class="login-link">Back to Login</a>
        </div>
    </div>
</body>
</html> 