<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (isset($_SESSION['userId'])) {
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

    $stmt = $conn->prepare("SELECT userId, name, password FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($userId, $name, $hashed_password);
        $stmt->fetch();

        // Debugging output
        echo "Fetched User ID: $userId, Name: $name<br>";

        if (password_verify($password, $hashed_password)) {
            $_SESSION['userId'] = $userId; // Store user ID in session
            $_SESSION['username'] = $name; // Store user name in session

            // Debugging output
            print_r($_SESSION); // Show session before redirection

            // Check if "Remember Me" was selected
            if (isset($_POST['remember_me'])) {
                setcookie("userId", $userId, time() + (30 * 24 * 60 * 60), "/"); // 30 days
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
    <link rel="stylesheet" href="../assests/css/loginstyle.css">
</head>
<body> 
<div class="button-container-3">
            
            <button  onclick="window.location.href='index.php'">LeaguePro</button>
           
        </div>
        
    <div class="container">
        <h1>Organizer Login</h1>
        <?php if ($message): ?>
            <div class="message">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form method="POST" >
            <label for="email">Email</label>
            <input type="email" name="email" required>

            <label for="password">Password</label>
            <input type="password" name="password" required>

           

            <button type="submit">Login</button>
            <p class="register-prompt">Don't have an account? <a href="registration.php">Register here</a></p>
        </form>
    </div>
</body>
</html>
