<?php
// Start the session
session_start();

// Ensure that the user is logged in
if (!isset($_SESSION['userId'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Connect to the database
require 'db_connection.php';

// Get the userId from the session
$userId = $_SESSION['userId'];

// Query to check the user's details
$stmt = $conn->prepare("SELECT userId, name, email, created_at, status FROM user WHERE userId = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Ensure the user is approved
    if ($user['status'] !== 'approved') {
        session_destroy();
        header("Location: login.php");
        exit();
    }
} else {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Query to get the league details
$league_stmt = $conn->prepare("SELECT league_id, league_name, season, duration, location FROM league WHERE userId = ?");
$league_stmt->bind_param("i", $userId);
$league_stmt->execute();
$league_result = $league_stmt->get_result();

// Fetch league data
$league = $league_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Dashboard</title>
    <link rel="stylesheet" href="../assests/css/dashboardstyle.css">
</head>
<body>

    <h1>Organizer Dashboard</h1>

    <nav class="navbar">
        <a href="org_dashboard.php" class="logo">Organizer</a>
        <span class="menu-toggle" onclick="toggleMenu()">☰</span>
        <ul id="nav-links">
        <li><a href="club.php">Your Clubs</a></li>
            <li><a href="team.php">Your Team</a></li>
            <li><a href="add_game.php">Add Game</a></li>
            <li><a href="leaderboard.php">Leaderboard</a></li>
            <li>
                <form action="logout.php" method="post" style="display:inline;">
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </li>
        </ul>
    </nav>

    <div class="details-container">
        <!-- User Details Section -->
        <div class="details-section">
            <h2>User Details</h2>
            <p><strong>User ID:</strong> <?php echo htmlspecialchars($user['userId']); ?></p>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Created At:</strong> <?php echo htmlspecialchars($user['created_at']); ?></p>
        </div>

        <!-- League Details Section -->
        <div class="details-section league-details">
            <h2>League Details</h2>
            <?php if ($league): ?>
            <p><strong>League ID:</strong> <?php echo htmlspecialchars($league['league_id']); ?></p>
            <p><strong>League Name:</strong> <?php echo htmlspecialchars($league['league_name']); ?></p>
            <p><strong>Season:</strong> <?php echo htmlspecialchars($league['season']); ?></p>
            <p><strong>Duration:</strong> <?php echo htmlspecialchars($league['duration']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($league['location']); ?></p>
            <?php else: ?>
            <p>No league details available.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
