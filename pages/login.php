<?php 
session_start();
require 'db_connection.php'; // Include your database connection
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Initialize message variable
$message = '';

if (isset($_POST['login'])) {
    // Get form input
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check if email and password are provided
    if (empty($email) || empty($password)) {
        $message = 'Email or password is missing.';
    } else {
        // Fetch user details from database
        $stmt = $conn->prepare("SELECT userId, name, email, password, status FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                $userId = $user['userId'];

                // Store session data
                session_regenerate_id(true);
                $_SESSION['userId'] = $userId;
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['status'] = $user['status']; // Store user status

                // Redirect based on approval status
                if ($_SESSION['status'] === 'approved') {
                    header('Location: org_dashboard.php'); // Full access
                } else {
                    header('Location: limited_dashboard.php'); // Restricted access
                }
                exit();
            } else {
                $message = 'Invalid email or password.';
            }
        } else {
            $message = "No user found with that email.";
        }
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

            

            <button type="submit" name="login">Login</button>
            <p class="register-prompt">Don't have an account? <a href="registration.php">Register here</a></p>
        </form>
    </div>
</body>
</html>