<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (isset($_SESSION['organizer_id'])) {
    echo "Redirecting to dashboard...";
    header("Location: org_dashboard.php");
    exit();
}

$host = "localhost";
$user = "root"; 
$password = ""; 
$dbname = "leaguedb";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT userId, password FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($userId, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['organizer_id'] = $userId;

            // Check if "Remember Me" was selected
            if (isset($_POST['remember_me'])) {
                // Set a cookie to expire in 30 days
                setcookie("organizer_id", $userId, time() + (30 * 24 * 60 * 60), "/"); // 30 days
            }

            header("Location: org_dashboard.php");
            exit();
        } else {
            $message = "Incorrect password. Please try again.";
        }
    } else {
        $message = "No account found with that email.";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Login</title>
    <link rel="stylesheet" href="loginstyle.css">
</head>
<body> 
    <div class="container">
        <h1>Organizer Login</h1>
        <?php if ($message): ?>
            <div class="message">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form method="POST" autocomplete="off">
            <label for="email">Email</label>
            <input type="email" name="email" required>

            <label for="password">Password</label>
            <input type="password" name="password" required>

            <label for="remember_me">
                <input type="checkbox" name="remember_me" value="1"> Remember Me
            </label>

            <button type="submit">Login</button>
            <p class="register-prompt">Don't have an account? <a href="registration.php">Register here</a></p>
        </form>
    </div>
</body>
</html>
