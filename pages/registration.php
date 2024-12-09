<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db_connection.php'; 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Handle form submission
if (isset($_POST['register'])) {
    // Check if POST data is available and clean it
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $send_email = isset($_POST['send_email']) ? true : false; // Check if 'Send email' is selected

    $message = '';

    if (empty($name) || empty($email) || empty($password)) {
        echo "<script>alert('All fields are required.');</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.');</script>";
    } elseif ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        // Check if the email already exists
        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
        if ($stmt === false) {
            die('Error preparing statement: ' . $conn->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Email is already registered!');</script>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO user (name, email, password, status) VALUES (?, ?, ?, 'pending')");
            if ($stmt === false) {
                die('Error preparing statement: ' . $conn->error);
            }
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                // If 'Send email' is selected, send email, else skip it
                if ($send_email) {
                    try {
                        if (sendEmailToOrganizer($email)) {
                            echo "<script>alert('Registration successful! Please wait for admin approval.');</script>";
                        } else {
                            echo "<script>alert('Registration successful, but email notification failed.');</script>";
                        }
                    } catch (Exception $e) {
                        echo "<script>alert('Registration successful, but email failed.');</script>";
                    }
                } else {
                    echo "<script>alert('Registration successful! Please wait for admin approval.');</script>";
                }
                
                // Redirect to login page after successful registration
                header('Location: login.php');
                exit();
            } else {
                echo "<script>alert('Error during registration.');</script>";
            }

            // Close the prepared statement
            $stmt->close();
        }

        // Close the initial prepared statement
        $stmt->close();
    }
}

// Email function
function sendEmailToOrganizer($organizerEmail) {
    require '../src/vendor/autoload.php'; // Ensure this path is correct
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = getenv('EMAIL_USER');
        $mail->Password = getenv('EMAIL_PASSWORD');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom(getenv('EMAIL_USER'), 'LeaguePro Admin');
        $mail->addAddress($organizerEmail);
        $mail->isHTML(true);
        $mail->Subject = 'Registration Successful!';
        $mail->Body = "<p>Your registration is under review.</p>";
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false; // If mail fails, it won't affect the registration process
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="../assests/css/regstyle.css">
</head>
<body>
<div class="container">
    <h1>Organizer Registration</h1>
    <form method="POST">
        <label for="username">Username</label>
        <input type="text" name="name" required>

        <label for="email">Email</label>
        <input type="email" name="email" required>

        <label for="password">Password</label>
        <input type="password" name="password" required>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" name="confirm_password" required>

        <!-- Optional Email Checkbox -->
        <label for="send_email">
            <input type="checkbox" name="send_email" value="1">
            Send email for faster response
        </label>

        <button type="submit" name="register">Register</button>
    </form>
</div>
</body>
</html>
