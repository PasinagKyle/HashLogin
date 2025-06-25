<?php
// Database configuration
$host = 'localhost';
$dbname = 'itpclogin';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// PHPMailer configuration
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

// Email configuration - UPDATE THESE WITH YOUR ACTUAL EMAIL SETTINGS
$email_config = [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'h2onuclear@gmail.com', // ⚠️ CHANGE THIS to your real Gmail address
    'smtp_password' => 'wooi rnuy kwbo ptgk', // ⚠️ CHANGE THIS to your Gmail app password (16 characters)
    'from_email' => 'h2onuclear@gmail.com', // ⚠️ CHANGE THIS to your real Gmail address
    'from_name' => 'ITPC Login System' // ⚠️ CHANGE THIS to your website name
];

// Site configuration
$site_config = [
    'base_url' => 'http://localhost/itpclogin', // CHANGE THIS to your actual domain
    'site_name' => 'ITPC Login System'
];
?> 