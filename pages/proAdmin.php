<?php
session_start();
require_once 'db_connection.php'; // Ensure this file contains your database connection setup

// Handle Registration
if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Email is already registered!');</script>";
        } else {
            // Insert into the database
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO admin (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                echo "<script>alert('Admin registered successfully!');</script>";
            } else {
                echo "<script>alert('Error during registration.');</script>";
            }
        }
    }
}

// Handle Logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: proAdmin.php");
    exit;
}

// Handle Login
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user details from database
    $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Successful login
        $_SESSION['admin_id'] = $user['admin_id'];
        $_SESSION['admin_name'] = $user['name'];
        $_SESSION['admin_email'] = $user['email'];
        header("Location: proAdmin.php"); // Redirect to admin page
        exit;
    } else {
        echo "<script>alert('Invalid email or password');</script>";
    }
}

// Handle user Approve/Deny Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $userId = (int) $_GET['id']; // Ensure ID is an integer
    $action = $_GET['action'];

    if ($action == 'approve') {
        $updateQuery = "UPDATE user SET status = 'approved' WHERE userId = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            header("Location: proAdmin.php"); // Redirect after successful action
            exit;
        }
    } elseif ($action == 'deny') {
        $deleteQuery = "DELETE FROM user WHERE userId = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            header("Location: proAdmin.php"); // Redirect after successful action
            exit;
        }
    }
}

// If logged in, allow form actions
if (isset($_SESSION['admin_id'])) {
    if (isset($_GET['form_action']) && isset($_GET['form_id'])) {
        $form_id = (int)$_GET['form_id'];
        $formAction = $_GET['form_action'];

        switch ($formAction) {
            case 'approve':
                // Approve the form
                $query = "UPDATE form SET status = 'approved' WHERE form_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $form_id);
                if ($stmt->execute()) {
                    echo "Form approved.";
                    header("Location: proAdmin.php"); // Redirect after successful action
                    exit;
                }
                break;

            case 'delete':
                // Delete the form
                $query = "DELETE FROM form WHERE form_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $form_id);
                if ($stmt->execute()) {
                    echo "Form deleted.";
                    header("Location: proAdmin.php"); // Redirect after successful action
                    exit;
                }
                break;

            default:
                echo "Invalid action.";
                break;
        }
    }
} else {
    echo ".";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assests/css/admin.css"> <!-- Link to external CSS -->
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="registration-container">
        <h2>Admin Portal</h2>

        <?php if (!isset($_SESSION['admin_id'])): ?>
            <!-- Registration Form -->
            <h3>Register</h3>
            <form method="POST" action="">
                <label>Name:</label>
                <input type="text" name="name" required>
                <br>
                <label>Email:</label>
                <input type="email" name="email" required>
                <br>
                <label>Password:</label>
                <input type="password" name="password" required>
                <br>
                <label>Confirm Password:</label>
                <input type="password" name="confirm_password" required>
                <br>
                <button type="submit" name="register">Register</button>
            </form>

            <hr>
            <h3>Already Registered?</h3>
            <h4>/</h4>
            <!-- Login Form -->
            <h3>Login</h3>
            <form method="POST" action="">
                <label>Email:</label>
                <input type="email" name="email" required>
                <br>
                <label>Password:</label>
                <input type="password" name="password" required>
                <br>
                <button type="submit" name="login">Login</button>
            </form>
        <?php else: ?>
    </div>
        <div class="conatiner">
            <!-- Admin Dashboard -->
            <h3>Welcome, <?php echo $_SESSION['admin_name']; ?>!</h3>
            <p>You are logged in as an Admin.</p>
            <form action="logout.php" method="post" style="display:inline;">
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
 

            <h4>Manage Users (Organizers)</h4>

            <!-- Flex container for Pending and Approved Users Tables -->
            <div class="table-container">
                <!-- Pending Users Table -->
                <div>
                    <h5>Pending Users</h5>
                    <?php
                    $query = "SELECT * FROM user WHERE status = 'pending'";
                    $result = mysqli_query($conn, $query);

                    if ($result && mysqli_num_rows($result) > 0): ?>
                        <table border="1">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $result->fetch_assoc()): ?>
                                <tr>
                                <td><?php echo htmlspecialchars($user['userId']); ?></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <a href="?action=approve&id=<?php echo $user['userId']; ?>">Approve</a> |
                                        <a href="?action=deny&id=<?php echo $user['userId']; ?>" 
                                           onclick="return confirm('Are you sure you want to deny this user? This action cannot be undone.');">
                                           Deny
                                        </a> |
                                        <a href="?edit=<?php echo $user['userId']; ?>">Edit</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                        </table>
                    <?php else: ?>
                        <p>No users pending approval.</p>
                    <?php endif; ?>
                </div>

                <!-- Approved Users Table -->
                <div>
                    <h5>Approved Users</h5>
                    <?php
                    $query = "SELECT * FROM user WHERE status = 'approved'";
                    $result = mysqli_query($conn, $query);

                    if ($result && mysqli_num_rows($result) > 0): ?>
                        <table border="1">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $result->fetch_assoc()): ?>
                                <tr>
                                   <td><?php echo htmlspecialchars($user['userId']); ?></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                    <a href="?edit=<?php echo $user['userId']; ?>">Edit</a>  | <a href="?action=deny&id=<?php echo $user['userId']; ?>" 
                                           onclick="return confirm('Are you sure you want to deny this user? This action cannot be undone.');">
                                          Delete
                                        </a> 
                                        
                                       
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                        </table>
                    <?php else: ?>
                        <p>No approved users.</p>
                    <?php endif; ?>
                </div>
            </div>
            <h4>Manage Forms</h4>

            <!-- Pending Forms Table -->
            <div>
                <h5>Pending Forms</h5>
                <?php
                $query = "SELECT * FROM form WHERE status = 'pending'";
                $result = mysqli_query($conn, $query);

                if ($result && mysqli_num_rows($result) > 0): ?>
                    <table border="1">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>League Name</th>
                            <th>Duration</th>
                            <th>Max Teams</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Experience</th>
                            <th>Location</th>
                            <th>Rules</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($form = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($form['userId']); ?></td>
                                <td><?php echo htmlspecialchars($form['league_name']); ?></td>
                                <td><?php echo htmlspecialchars($form['duration']); ?></td>
                                <td><?php echo htmlspecialchars($form['max_teams']); ?></td>
                                <td><?php echo htmlspecialchars($form['start_date']); ?></td>
                                <td><?php echo htmlspecialchars($form['end_date']); ?></td>
                                <td><?php echo htmlspecialchars($form['experience']); ?></td>
                                <td><?php echo htmlspecialchars($form['location']); ?></td>
                                <td><?php echo htmlspecialchars($form['rules']); ?></td>
                                <td><?php echo htmlspecialchars($form['status']); ?></td>
                                <td>
                                    <a href="?form_action=approve&form_id=<?php echo $form['form_id']; ?>">Approve</a> |
                                    
                                    <a href="?form_action=delete&form_id=<?php echo $form['form_id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this form?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    </table>
                <?php else: ?>
                    <p>No forms pending approval.</p>
                <?php endif; ?>
            </div>

            <!-- Approved Forms Table -->
            <div>
                <h5>Approved Forms</h5>
                <?php
                $query = "SELECT * FROM form WHERE status = 'approved'";
                $result = mysqli_query($conn, $query);

                if ($result && mysqli_num_rows($result) > 0): ?>
                    <table border="1">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>League Name</th>
                            <th>Duration</th>
                            <th>Max Teams</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Experience</th>
                            <th>Location</th>
                            <th>Rules</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($form = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($form['userId']); ?></td>
                                <td><?php echo htmlspecialchars($form['league_name']); ?></td>
                                <td><?php echo htmlspecialchars($form['duration']); ?></td>
                                <td><?php echo htmlspecialchars($form['max_teams']); ?></td>
                                <td><?php echo htmlspecialchars($form['start_date']); ?></td>
                                <td><?php echo htmlspecialchars($form['end_date']); ?></td>
                                <td><?php echo htmlspecialchars($form['experience']); ?></td>
                                <td><?php echo htmlspecialchars($form['location']); ?></td>
                                <td><?php echo htmlspecialchars($form['rules']); ?></td>
                                <td><?php echo htmlspecialchars($form['status']); ?></td>
                                <td>
                                    <a href="?form_action=delete&id=<?php echo $form['form_id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this form?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    </table>
                <?php else: ?>
                    <p>No approved forms.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    
    
        </div>
</body>
</html>
