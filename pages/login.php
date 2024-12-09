<?php
session_start();
require '../src/PHPMailer.php';
require '../src/SMTP.php';
require '../src/Exception.php';

require 'db_connection.php'; // Include your database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize message variable
$message = '';
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Fetch user details
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] === 'approved') {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['userId'];
            $_SESSION['name'] = htmlspecialchars($user['name']);
            $_SESSION['email'] = htmlspecialchars($user['email']);
            header('Location: org_dashboard.php');
            exit();
        } else {
            $message = 'Your account is not approved yet. Please wait for approval.';
        }
    } else {
        $message = 'Invalid email or password.';
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Login</title>
    <link rel="stylesheet" href="../assests/css/loginstyle.css">
</head>
<body> 
    <div class="button-container-3">
        <button onclick="window.location.href='index.php'">LeaguePro</button>
    </div>
        
    <div class="container">
        <h1>Organizer Login</h1>
        <?php if ($message): ?>
            <div class="message">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <label for="email">Email</label>
            <input type="email" name="email" required>

            <label for="password">Password</label>
            <input type="password" name="password" required>

            <label for="remember_me">
                <input type="checkbox" name="remember_me"> Remember me
            </label>

            <button type="submit" name="login">Login</button>
            <p class="register-prompt">Don't have an account? <a href="registration.php">Register here</a></p>
        </form>
    </div>
</body>
</html>
