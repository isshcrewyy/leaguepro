<?php
// Start the session
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['userId'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Ensure the user is approved
if (!isset($_SESSION['status']) || $_SESSION['status'] !== 'approved') {
    header("Location: org_dashboard.php");
    exit();
}
$name = $_SESSION['name'];
$userId = $_SESSION['userId'];

// Connect to the database
require 'db_connection.php';

// Fetch available leagues for the dropdown
$leagueQuery = "SELECT league_id, league_name FROM league";
$stmt = $conn->prepare($leagueQuery);
$stmt->execute();
$leagueResult = $stmt->get_result();

// Handle form submission to get the selected league
$selected_league = isset($_GET['league_id']) ? $_GET['league_id'] : null;

// Fetch games for the selected league
$games = [];
if ($selected_league) {
    $sql = "SELECT * FROM game WHERE league_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $selected_league);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $games[] = $row;
        }
        $stmt->close();
    }
}

// Calculate leaderboard data
$leaderboard = [];
foreach ($games as $game) {
    $home_club_id = $game['home_club_id'];
    $away_club_id = $game['away_club_id'];
    $score_home = $game['score_home'];
    $score_away = $game['score_away'];

    // Initialize home and away team if not already present
    if (!isset($leaderboard[$home_club_id])) {
        $leaderboard[$home_club_id] = [
            'matches_played' => 0,
            'wins' => 0,
            'losses' => 0,
            'draws' => 0,
            'goals_scored' => 0,
            'goals_against' => 0,
            'goal_difference' => 0,
            'points' => 0,
            'club_name' => ''
        ];
    }
    if (!isset($leaderboard[$away_club_id])) {
        $leaderboard[$away_club_id] = [
            'matches_played' => 0,
            'wins' => 0,
            'losses' => 0,
            'draws' => 0,
            'goals_scored' => 0,
            'goals_against' => 0,
            'goal_difference' => 0,
            'points' => 0,
            'club_name' => ''
        ];
    }

    // Check if scores are valid numbers
    if (is_numeric($score_home) && is_numeric($score_away)) {
        // Update matches played and goals scored
        $leaderboard[$home_club_id]['matches_played']++;
        $leaderboard[$away_club_id]['matches_played']++;
        $leaderboard[$home_club_id]['goals_scored'] += $score_home;
        $leaderboard[$away_club_id]['goals_scored'] += $score_away;
        $leaderboard[$home_club_id]['goals_against'] += $score_away;
        $leaderboard[$away_club_id]['goals_against'] += $score_home;

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
}

// Fetch club names
$club_ids = array_keys($leaderboard);
if (!empty($club_ids)) {
    $placeholders = implode(',', array_fill(0, count($club_ids), '?'));
    $types = str_repeat('i', count($club_ids));
    $sql = "SELECT club_id, c_name FROM club WHERE club_id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$club_ids);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $leaderboard[$row['club_id']]['club_name'] = $row['c_name'];
        }
        $stmt->close();
    }
}

// Sort leaderboard by points (highest first), then by goal difference
uasort($leaderboard, function ($a, $b) {
    if ($b['points'] != $a['points']) {
        return $b['points'] - $a['points'];
    }
    return $b['goal_difference'] - $a['goal_difference'];
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <link rel="stylesheet" href="../assests/css/leaderboard_style.css">
    <style>
        /* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body and Container */
body {
    font-family: 'Roboto', sans-serif;
    background-color: #f4f4f4;
    color: #333;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    min-height: 100vh;
}

/* Header styling */
h2 {
    text-align: center;
    color: #2c3e50;
    margin-top: 20px;
}

.breadcrumb {
    margin: 20px 0;
    padding: 10px 16px;
    background-color: #f9f9f9;
    border-radius: 5px;
    position: absolute;
    left: 20px;
    top: 20px;
}

.breadcrumb a {
    color: #3498db;
    text-decoration: none;
    padding: 0 5px;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.breadcrumb span {
    padding: 0 5px;
}

/* Form styling */
form {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin: 20px 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 80%;
    max-width: 500px;
}

form label {
    font-size: 18px;
    margin-bottom: 10px;
    color: #2c3e50;
}

form select {
    padding: 10px;
    font-size: 16px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    width: 100%;
    max-width: 300px;
}

form button {
    padding: 10px 20px;
    background-color: #2980b9;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease;
}

form button:hover {
    background-color: #3498db;
}

/* Table styling */
table {
    width: 80%;
    max-width: 900px;
    margin-top: 30px;
    border-collapse: collapse;
    border-radius: 8px;
    overflow: hidden;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

table, th, td {
    border: 1px solid #ccc;
}

th, td {
    padding: 12px;
    text-align: left;
    font-size: 16px;
}

th {
    background-color: #2980b9;
    color: white;
}

tr:nth-child(even) {
    background-color: #f9f9f9;
}

tr:hover {
    background-color: #ecf0f1;
}

/* Responsive styling */
@media (max-width: 768px) {
    form {
        width: 90%;
    }

    table {
        width: 100%;
        font-size: 14px;
    }

    th, td {
        padding: 10px;
    }
}
    </style>
</head>
<body>

<h2>Select League</h2>

<form action="" method="get">
    <label for="league_id">Choose a League:</label>
    <select name="league_id" id="league_id">
        <option value="">--Select a League--</option>
        <?php
        // Populate the dropdown with available leagues
        if ($leagueResult->num_rows > 0) {
            while ($league = $leagueResult->fetch_assoc()) {
                $selected = $league['league_id'] == $selected_league ? 'selected' : '';
                echo "<option value='{$league['league_id']}' $selected>{$league['league_name']}</option>";
            }
        }
        ?>
    </select>
    <button type="submit">View Leaderboard</button>
</form>

<!-- Breadcrumb Navigation -->
<div class="breadcrumb">
    <a href="index.php">Home</a> <span>&gt;</span>
    <a href="fans.php">Fans</a> <span>&gt;</span>
    <span>Leaderboard</span>
</div>

<?php
// Display leaderboard if a league is selected
if (!empty($leaderboard)) {
    echo "<h2>Leaderboard for Selected League</h2>";
    echo "<table>
            <tr>
                <th>Rank</th>
                <th>Club Name</th>
                <th>Matches Played</th>
                <th>Wins</th>
                <th>Losses</th>
                <th>Draws</th>
                <th>Goals Scored</th>
                <th>Goals Against</th>
                <th>Goal Difference</th>
                <th>Points</th>
            </tr>";

    // Fetch and display each row of data
    $rank = 1;
    foreach ($leaderboard as $club_id => $data) {
        echo "<tr>
                <td>{$rank}</td>
                <td>{$data['club_name']}</td>
                <td>{$data['matches_played']}</td>
                <td>{$data['wins']}</td>
                <td>{$data['losses']}</td>
                <td>{$data['draws']}</td>
                <td>{$data['goals_scored']}</td>
                <td>{$data['goals_against']}</td>
                <td>{$data['goal_difference']}</td>
                <td>{$data['points']}</td>
              </tr>";
        $rank++;
    }

    echo "</table>";
} elseif ($selected_league) {
    echo "No leaderboard data found for the selected league.";
}

// Close the connection
$conn->close();
?>

<script>
    function toggleMenu() {
        const navLinks = document.getElementById('nav-links');
        navLinks.classList.toggle('active');
    }
</script>

</body>
</html>