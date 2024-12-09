<?php
session_start();
require_once 'db_connection.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_register_login_dashboard.php");
    exit;
}

// Fetch all users with 'pending' status
$stmt = $conn->prepare("SELECT * FROM users WHERE status = 'pending'");
$stmt->execute();
$users = $stmt->get_result();

// Fetch all form submissions with 'pending' status
$form_stmt = $conn->prepare("SELECT * FROM form_submissions WHERE status = 'pending'");
$form_stmt->execute();
$forms = $form_stmt->get_result();

// Approve or deny user
if (isset($_GET['approve_user'])) {
    $user_id = $_GET['approve_user'];
    $stmt = $conn->prepare("UPDATE users SET status = 'approved' WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    header("Location: admin_manage.php");
    exit;
}

if (isset($_GET['deny_user'])) {
    $user_id = $_GET['deny_user'];
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    header("Location: admin_manage.php");
    exit;
}

// Approve or deny form submission
if (isset($_GET['approve_form'])) {
    $form_id = $_GET['approve_form'];
    $stmt = $conn->prepare("UPDATE form_submissions SET status = 'approved' WHERE form_id = ?");
    $stmt->bind_param("i", $form_id);
    $stmt->execute();
    header("Location: admin_manage.php");
    exit;
}

if (isset($_GET['deny_form'])) {
    $form_id = $_GET['deny_form'];
    $stmt = $conn->prepare("DELETE FROM form_submissions WHERE form_id = ?");
    $stmt->bind_param("i", $form_id);
    $stmt->execute();
    header("Location: admin_manage.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Manage Users and Forms</title>
</head>
<body>
    <h3>Admin Dashboard</h3>
    
    <!-- User Management Section -->
    <h4>Manage Users (Organizers)</h4>
    <table border="1">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <a href="?approve_user=<?php echo $user['user_id']; ?>">Approve</a> |
                        <a href="?deny_user=<?php echo $user['user_id']; ?>">Deny</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <hr>

    <!-- Form Verification Section -->
    <h4>Verify Forms for Organizers</h4>
    <table border="1">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Form Details</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($form = $forms->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($form['name']); ?></td>
                    <td><?php echo htmlspecialchars($form['email']); ?></td>
                    <td><?php echo htmlspecialchars($form['form_details']); ?></td>
                    <td>
                        <a href="?approve_form=<?php echo $form['form_id']; ?>">Approve</a> |
                        <a href="?deny_form=<?php echo $form['form_id']; ?>">Deny</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <br>
    <a href="admin_register_login_dashboard.php">Back to Dashboard</a>
</body>
</html>
