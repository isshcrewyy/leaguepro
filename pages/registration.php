<?php
session_start();
include 'db_connection.php'; // Include your database connection

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($password)) {
        echo "<script>alert('All fields are required.');</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.');</script>";
    } elseif ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Email is already registered!');</script>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO user (name, email, password, status) VALUES (?, ?, ?, 'pending')");
            $stmt->bind_param("sss", $name, $email, $hashed_password);
            if ($stmt->execute()) {
                $_SESSION['userId'] = $conn->insert_id; // Save the userId for use in the next step
                header('Location: entry_form.php');
                exit();
            } else {
                echo "<script>alert('Error during registration.');</script>";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registration</title>
    <link rel="stylesheet" href="../assests/css/regstyle.css">
</head>
<body>
    <h1>Organizer Registration</h1>
    <form method="POST">
        <label for="name">Name</label>
        <input type="text" name="name" required>

        <label for="email">Email</label>
        <input type="email" name="email" required>

        <label for="password">Password</label>
        <input type="password" name="password" required>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" name="confirm_password" required>

        <button type="submit" name="register">Next</button>
    </form>
</body>
</html>
