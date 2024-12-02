<?php
session_start();

// Database connection details
$host = "localhost";
$user = "root";
$password = "";
$dbname = "leaguedb";

// Connection
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch Club Details
$clubQuery = "SELECT * FROM club WHERE league_id = ?";
$clubStmt = $conn->prepare($clubQuery);
$clubStmt->bind_param("i", $_GET['league_id']); // assuming league_id is passed via GET
$clubStmt->execute();
$clubResult = $clubStmt->get_result();
$clubs = [];
while ($row = $clubResult->fetch_assoc()) {
    $clubs[] = $row;
}
$clubStmt->close();

// Fetch Leaderboard Data
$leaderboardQuery = "SELECT * FROM leaderboard WHERE league_id = ?";
$leaderboardStmt = $conn->prepare($leaderboardQuery);
$leaderboardStmt->bind_param("i", $_GET['league_id']);
$leaderboardStmt->execute();
$leaderboardResult = $leaderboardStmt->get_result();
$leaderboard = [];
while ($row = $leaderboardResult->fetch_assoc()) {
    $leaderboard[] = $row;
}
$leaderboardStmt->close();

// Fetch Players Data
$players = [];
foreach ($clubs as $club) {
    $playerQuery = "SELECT * FROM player WHERE club_id = ?";
    $playerStmt = $conn->prepare($playerQuery);
    $playerStmt->bind_param("i", $club['club_id']);
    $playerStmt->execute();
    $playerResult = $playerStmt->get_result();
    while ($row = $playerResult->fetch_assoc()) {
        $players[$club['club_id']][] = $row;
    }
    $playerStmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>League Details</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>
<div class="button-container-3">
            
            <button  onclick="window.location.href='index.php'">LeaguePro</button>
           
        </div>
    <header>
        <h1>League Details</h1>
        
    </header>

    <div class="container">
        <h2>Clubs in this League</h2>
        <div class="card-container">
            <?php foreach ($clubs as $club): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($club['name']); ?></h3>
                    <p>Location: <?php echo htmlspecialchars($club['location']); ?></p>
                    <p>Founded: <?php echo htmlspecialchars($club['founded_year']); ?></p>
                    <div class="button-group">
                        <a href="view_league.php?league_id=<?php echo $_GET['league_id']; ?>&club_id=<?php echo $club['club_id']; ?>">View Players</a>
                        <a href="view_league.php?league_id=<?php echo $_GET['league_id']; ?>&view=leaderboard">View Leaderboard</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (isset($_GET['view']) && $_GET['view'] == 'leaderboard'): ?>
            <h2>Leaderboard</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Club</th>
                            <th>Points</th>
                            <th>Wins</th>
                            <th>Losses</th>
                            <th>Draws</th>
                            <th>Goal Difference</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leaderboard as $entry): ?>
                            <tr>
                                <?php 
                                    // Find club name by club_id
                                    $clubName = '';
                                    foreach ($clubs as $club) {
                                        if ($club['club_id'] == $entry['club_id']) {
                                            $clubName = $club['name'];
                                            break;
                                        }
                                    }
                                ?>
                                <td><?php echo htmlspecialchars($clubName); ?></td>
                                <td><?php echo htmlspecialchars($entry['points']); ?></td>
                                <td><?php echo htmlspecialchars($entry['wins']); ?></td>
                                <td><?php echo htmlspecialchars($entry['losses']); ?></td>
                                <td><?php echo htmlspecialchars($entry['draws']); ?></td>
                                <td><?php echo htmlspecialchars($entry['goal_difference']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['club_id'])): ?>
            <?php 
                $clubName = '';
                foreach ($clubs as $club) {
                    if ($club['club_id'] == $_GET['club_id']) {
                        $clubName = $club['name'];
                        break;
                    }
                }
            ?>
            <h2>Players in <?php echo htmlspecialchars($clubName); ?></h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Age</th>
                            <th>Position</th>
                            <th>Phone</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($players[$_GET['club_id']])): ?>
                            <?php foreach ($players[$_GET['club_id']] as $player): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($player['name']); ?></td>
                                    <td><?php echo htmlspecialchars($player['age']); ?></td>
                                    <td><?php echo htmlspecialchars($player['position']); ?></td>
                                    <td><?php echo htmlspecialchars($player['phone_number']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4">No players available for this club.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
