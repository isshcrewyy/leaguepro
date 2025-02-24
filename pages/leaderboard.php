<?php
// Start the session
session_start();
$name = $_SESSION['name'];
$userId = $_SESSION['userId'];

// Ensure that the user is logged in
if (!isset($_SESSION['userId'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Connect to the database
require 'db_connection.php';

// Fetch the league ID from a request or set it dynamically

 // Assuming it's passed as a query parameter

// Fetch all games from the database for the selected league
$query = "SELECT * FROM game WHERE created_by = ? ";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Initialize an empty array to store the leaderboard data
$leaderboard = [];

// Iterate through each game and calculate points for each team
while ($row = $result->fetch_assoc()) {
    $home_club_id = $row['home_club_id'];
    $away_club_id = $row['away_club_id'];
    $score_home = $row['score_home'];
    $score_away = $row['score_away'];
    $league_id = $row['league_id'];

    // Initialize home and away team if not already present
    if (!isset($leaderboard[$home_club_id])) {
        $leaderboard[$home_club_id] = [
            'matches_played' => 0,
            'wins' => 0,
            'losses' => 0,
            'draws' => 0,
            'goals_scored' => 0,
            'goal_difference' => 0,
            'points' => 0
        ];
    }
    if (!isset($leaderboard[$away_club_id])) {
        $leaderboard[$away_club_id] = [
            'matches_played' => 0,
            'wins' => 0,
            'losses' => 0,
            'draws' => 0,
            'goals_scored' => 0,
            'goal_difference' => 0,
            'points' => 0
        ];
    }

    // Update matches played and goals scored
    $leaderboard[$home_club_id]['matches_played']++;
    $leaderboard[$away_club_id]['matches_played']++;
    $leaderboard[$home_club_id]['goals_scored'] += $score_home;
    $leaderboard[$away_club_id]['goals_scored'] += $score_away;

    // Calculate goals difference
    $leaderboard[$home_club_id]['goal_difference'] += ($score_home - $score_away);
    $leaderboard[$away_club_id]['goal_difference'] += ($score_away - $score_home);

    // Calculate points, wins, losses, and draws
    if ($score_home > $score_away) {
        // Home wins
        $leaderboard[$home_club_id]['points'] += 3;
        $leaderboard[$home_club_id]['wins']++;
        $leaderboard[$away_club_id]['losses']++;
    } elseif ($score_home < $score_away) {
        // Away wins
        $leaderboard[$away_club_id]['points'] += 3;
        $leaderboard[$away_club_id]['wins']++;
        $leaderboard[$home_club_id]['losses']++;
    } else {
        // Draw
        $leaderboard[$home_club_id]['points'] += 1;
        $leaderboard[$away_club_id]['points'] += 1;
        $leaderboard[$home_club_id]['draws']++;
        $leaderboard[$away_club_id]['draws']++;
    }
}

// Sort leaderboard by points (highest first)
uasort($leaderboard, function ($a, $b) {
    return $b['points'] - $a['points'];
});

// Save the leaderboard to the database
foreach ($leaderboard as $club_id => $data) {
    $query = "SELECT * FROM leaderboard WHERE club_id = ? AND league_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $club_id, $league_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing row
        $updateQuery = "UPDATE leaderboard SET 
            matches_played = ?,
            wins = ?,
            losses = ?,
            draws = ?,
            goals_scored = ?,
            goal_difference = ?,
            points = ?
            WHERE club_id = ? AND league_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param(
            "iiiiiiisi",
            $data['matches_played'],
            $data['wins'],
            $data['losses'],
            $data['draws'],
            $data['goals_scored'],
            $data['goal_difference'],
            $data['points'],
            $club_id,
            $league_id
        );
        $stmt->execute();
    } else {
        // Insert new row
        $insertQuery = "INSERT INTO leaderboard (club_id, league_id, matches_played, wins, losses, draws, goals_scored, goal_difference, points, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param(
            "siiiiiiiss",
            $club_id,
            $league_id,
            $data['matches_played'],
            $data['wins'],
            $data['losses'],
            $data['draws'],
            $data['goals_scored'],
            $data['goal_difference'],
            $data['points'],
            $name
        );
        $stmt->execute();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <link rel="stylesheet" href="../assests/css/leaderboard_style.css">
</head>
<body>

<nav class="navbar">
    <a href="org_dashboard.php" class="logo">Organizer</a>
    <span class="menu-toggle" onclick="toggleMenu()">☰</span>
    <ul id="nav-links">
        <li><a href="club.php">Your Clubs</a></li>
        <li><a href="team.php">Your Team</a></li>
        <li><a href="add_game.php">Add Game</a></li>
        <li><a href="leaderboard.php" class="active">Leaderboard</a></li>
        <li>
            <form action="logout.php" method="post" style="display:inline;">
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </li>
    </ul>
</nav>

<h2>Leaderboard</h2>
<div class="leaderboard-container">
    <table class="leaderboard-table">
        <thead>
            <tr>
                <th>Rank</th>
                <th>League ID</th>
                <th>Club ID</th>
                <th>Matches Played</th>
                <th>Wins</th>
                <th>Losses</th>
                <th>Draws</th>
                <th>Goals Scored</th>
                <th>Goal Difference</th>
                <th>Points</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $rank = 1;
            foreach ($leaderboard as $club_id => $data) : ?>
                <tr>
                    <td><?php echo $rank++; ?></td>
                    <td><?php echo htmlspecialchars($league_id); ?></td>
                    <td><?php echo htmlspecialchars($club_id); ?></td>
                    <td><?php echo $data['matches_played']; ?></td>
                    <td><?php echo $data['wins']; ?></td>
                    <td><?php echo $data['losses']; ?></td>
                    <td><?php echo $data['draws']; ?></td>
                    <td><?php echo $data['goals_scored']; ?></td>
                    <td><?php echo $data['goal_difference']; ?></td>
                    <td><?php echo $data['points']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function toggleMenu() {
        const navLinks = document.getElementById('nav-links');
        navLinks.classList.toggle('active');
    }
</script>

</body>
</html>
