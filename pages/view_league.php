<?php
session_start();

if (!isset($_GET['league_id'])) {
    echo "No league ID provided in the URL.";
    exit();
}

$league_id = $_GET['league_id'];
$host = "localhost";
$user = "root"; 
$password = ""; 
$dbname = "leaguedb"; 

// Establish the database connection
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch league details
$leagueDetails = [];
$leagueQuery = "SELECT league_name, season, start_date, end_date FROM league WHERE league_id = ?";
$stmt = $conn->prepare($leagueQuery);
$stmt->bind_param("i", $league_id);
$stmt->execute();
$leagueDetails = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch clubs in the selected league
$clubs = [];
$clubQuery = "SELECT club_id, name FROM club WHERE league_id = ?";
$stmt = $conn->prepare($clubQuery);
$stmt->bind_param("i", $league_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $clubs[] = $row;
}
$stmt->close();

// Fetch leaderboard data
$leaderboard = [];
$leaderboardQuery = "SELECT club.name AS club_name, leaderboard.points, leaderboard.wins, leaderboard.losses, leaderboard.draws, leaderboard.goal_difference
                     FROM leaderboard 
                     JOIN club ON leaderboard.club_id = club.club_id 
                     WHERE leaderboard.league_id = ?";
$stmt = $conn->prepare($leaderboardQuery);
$stmt->bind_param("i", $league_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $leaderboard[] = $row;
}
$stmt->close();

// Fetch players for each club in the league
$players = [];
foreach ($clubs as $club) {
    $club_id = $club['club_id'];
    $playerQuery = "SELECT name, age, position FROM player WHERE club_id = ?";
    $stmt = $conn->prepare($playerQuery);
    $stmt->bind_param("i", $club_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $players[$club['name']][] = $row;
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
    <title>League View</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>
    <header>
        <h1>League Details - <?php echo htmlspecialchars($leagueDetails['league_name']); ?></h1>
        <p>Season: <?php echo htmlspecialchars($leagueDetails['season']); ?></p>
        <p>Start Date: <?php echo htmlspecialchars($leagueDetails['start_date']); ?></p>
        <p>End Date: <?php echo htmlspecialchars($leagueDetails['end_date']); ?></p>
    </header>

    <div class="container">
        <h2>Clubs in this League</h2>
        <ul>
            <?php foreach ($clubs as $club): ?>
                <li><?php echo htmlspecialchars($club['name']); ?></li>
            <?php endforeach; ?>
        </ul>

        <h2>Leaderboard</h2>
        <?php if (!empty($leaderboard)): ?>
            <table>
                <tr>
                    <th>Club</th>
                    <th>Points</th>
                    <th>Wins</th>
                    <th>Losses</th>
                    <th>Draws</th>
                    <th>Goal Difference</th>
                </tr>
                <?php foreach ($leaderboard as $entry): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($entry['club_name']); ?></td>
                        <td><?php echo htmlspecialchars($entry['points']); ?></td>
                        <td><?php echo htmlspecialchars($entry['wins']); ?></td>
                        <td><?php echo htmlspecialchars($entry['losses']); ?></td>
                        <td><?php echo htmlspecialchars($entry['draws']); ?></td>
                        <td><?php echo htmlspecialchars($entry['goal_difference']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No leaderboard data available for this league.</p>
        <?php endif; ?>

        <h2>Players</h2>
        <?php foreach ($players as $clubName => $clubPlayers): ?>
            <h3><?php echo htmlspecialchars($clubName); ?></h3>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Position</th>
                </tr>
                <?php foreach ($clubPlayers as $player): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($player['name']); ?></td>
                        <td><?php echo htmlspecialchars($player['age']); ?></td>
                        <td><?php echo htmlspecialchars($player['position']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endforeach; ?>
    </div>
</body>
</html>
