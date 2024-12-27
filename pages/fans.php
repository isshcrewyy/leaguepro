<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$dbname = "leaguedb";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$leagues = [];
$leagueQuery = "SELECT league_id, League_name, season FROM league";
$result = $conn->query($leagueQuery);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $leagues[] = $row;
    }
} else {
    echo "No leagues found in the fdfddf.";
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fans Page - Select a League</title>
    <link rel="stylesheet" href="../assests/css/fanStyle.css">
</head>
<body>
<div class="button-container-3">
            
            <button  onclick="window.location.href='index.php'">LeaguePro</button>
           
        </div>
    <header>
        <h1>Welcome, Fans! Choose a League</h1>
    </header>

    <div class="container">
    
        <h2>Select a League to View Details</h2>
        <ul>
            <?php foreach ($leagues as $league): ?>
                <li>
                    <a href="view_league.php?league_id=<?php echo urlencode($league['league_id']); ?>">
                        <?php echo htmlspecialchars($league['League_name']) . " - Season: " . htmlspecialchars($league['season']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="button-group">
            <a href="view_leaderboard.php" class="btn">Leaderboard</a>
    </div>
</body>
</html>
