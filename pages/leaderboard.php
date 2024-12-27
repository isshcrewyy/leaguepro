<?php
// Start the session
session_start();
$name = $_SESSION['name'];

// Ensure that the user is logged in
if (!isset($_SESSION['userId'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Connect to the database
require 'db_connection.php';

// Fetch all games from the database
$query = "SELECT * FROM game WHERE created_by = '$name' ";
$result = $conn->query($query);

// Initialize an empty array to store the leaderboard data
$leaderboard = [];

// Iterate through each game and calculate points for each team
while ($row = $result->fetch_assoc()) {
    $home_club_id = $row['home_club_id'];
    $away_club_id = $row['away_club_id'];
    $score_home = $row['score_home'];
    $score_away = $row['score_away'];

    // Initialize home and away team if not already present
    if (!isset($leaderboard[$home_club_id])) {
        $leaderboard[$home_club_id] = ['points' => 0, 'matches_played' => 0];
    }
    if (!isset($leaderboard[$away_club_id])) {
        $leaderboard[$away_club_id] = ['points' => 0, 'matches_played' => 0];
    }

    // Update matches played
    $leaderboard[$home_club_id]['matches_played']++;
    $leaderboard[$away_club_id]['matches_played']++;

    // Calculate points for home and away teams
    if ($score_home > $score_away) {
        // Home wins
        $leaderboard[$home_club_id]['points'] += 3;
    } elseif ($score_home < $score_away) {
        // Away wins
        $leaderboard[$away_club_id]['points'] += 3;
    } else {
        // Draw
        $leaderboard[$home_club_id]['points'] += 1;
        $leaderboard[$away_club_id]['points'] += 1;
    }
}

// Sort leaderboard by points (highest first)
uasort($leaderboard, function ($a, $b) {
    return $b['points'] - $a['points'];
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <link rel="stylesheet" href="../assests/css/navbar.css">
</head>
<body>

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

<script>
    function toggleMenu() {
        const navLinks = document.getElementById('nav-links');
        navLinks.classList.toggle('active');
    }
</script>

<h2>Leaderboard</h2>
<table border="1">
    <thead>
        <tr>
            <th>Rank</th>
            <th>Club ID</th>
            <th>Matches Played</th>
            <th>Points</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $rank = 1;
    foreach ($leaderboard as $club_id => $data) : ?>
        <tr>
            <td><?php echo $rank++; ?></td>
            <td><?php echo htmlspecialchars($club_id); ?></td>
            <td><?php echo $data['matches_played']; ?></td>
            <td><?php echo $data['points']; ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
