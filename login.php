<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    $errors = [];
    
    // Validation
    if (empty($username)) $errors[] = "Username is required";
    if (empty($password)) $errors[] = "Password is required";
    
    if (empty($errors)) {
        // Check if user exists and password is correct
        $stmt = $pdo->prepare("SELECT id, username, password_hash, is_verified, email FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Check if user is verified
            if ($user['is_verified'] == 1) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $response = ['success' => true, 'message' => 'Login successful!'];
            } else {
                // User exists but not verified
                $response = [
                    'success' => false, 
                    'message' => 'Please verify your email address before logging in. Check your email for the verification link.'
                ];
            }
        } else {
            $response = ['success' => false, 'message' => 'Invalid username or password'];
        }
    } else {
        $response = ['success' => false, 'message' => implode(', ', $errors)];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?> 