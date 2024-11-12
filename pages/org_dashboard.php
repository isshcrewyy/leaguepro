<?php
session_start();
$action = '';

// Check session or cookie
if (!isset($_SESSION['organizer_id'])) {
    if (isset($_COOKIE['organizer_id'])) {
        $_SESSION['organizer_id'] = $_COOKIE['organizer_id'];
    } else {
        header("Location: login.php");
        exit();
    }
}

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "leaguedb";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to execute statement and handle errors
function executeStatement($stmt) {
    if ($stmt->execute()) {
        echo "<script>alert('Record processed successfully.')</script>";
    } else {
        echo "<script>alert('Error processing record: " . $stmt->error . "')</script>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    $table = $_POST['table'];
    $stmt = null;

    // Handle add action
    if ($action == 'add') {
        switch ($table) {
            case 'coach':
                $stmt = $conn->prepare("INSERT INTO coach (name, experience, age, club_id) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("siii", $_POST['name'], $_POST['experience'], $_POST['age'], $_POST['club_id']);
                break;

            case 'club':
                    $stmt = $conn->prepare("INSERT INTO club (name, location, founded_year, league_id) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssii", $_POST['name'], $_POST['location'], $_POST['founded_year'], $_POST['league_id']);
                break;

            case 'game':
                $stmt = $conn->prepare("INSERT INTO game (league_id, home_club_id, away_club_id, date, time, score_home, score_away) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iiissss", $_POST['league_id'], $_POST['home_club_id'], $_POST['away_club_id'], $_POST['date'], $_POST['time'], $_POST['score_home'], $_POST['score_away']);
                break;

            case 'leaderboard':
            $gameQuery = "SELECT home_club_id, away_club_id, score_home, score_away, league_id FROM game WHERE match_id = ?";
            $stmt = $conn->prepare($gameQuery);
            $stmt->bind_param("i", $match_id);
            $stmt->execute();
            $gameResult = $stmt->get_result();
            
            if ($gameResult->num_rows > 0) {
                $game = $gameResult->fetch_assoc();
            
                // Replace these variables with your column names
                $homeClubId = $game['home_club_id'];
                $awayClubId = $game['away_club_id'];
                $score_home = $game['score_home'];
                $score_away = $game['score_away'];
                $leagueId = $game['league_id'];
            
                // Continue with the rest of the logic using the switch case
                $homePoints = 0;
                $awayPoints = 0;
                $homeWin = 0;
                $awayWin = 0;
                $homeDraw = 0;
                $awayDraw = 0;
                $homeLoss = 0;
                $awayLoss = 0;
                
                // Calculate goal difference
                $homeGoalDifference = $score_home - $score_away;
                $awayGoalDifference = $score_away - $score_home;
                
                switch (true) {
                    case $score_home > $score_away:
                        $homePoints = 3;
                        $homeWin = 1;
                        $awayLoss = 1;
                        break;
                    case $score_home < $score_away:
                        $awayPoints = 3;
                        $awayWin = 1;
                        $homeLoss = 1;
                        break;
                    case $score_home == $score_away:
                        $homePoints = 1;
                        $awayPoints = 1;
                        $homeDraw = 1;
                        $awayDraw = 1;
                        break;
                }
                
                // Prepare the update query
                $stmt = $conn->prepare($updateLeaderboardQuery);
                
                // Update home team leaderboard
                $stmt->bind_param(
                    "iiiiiiii", 
                    $homePoints, 
                    $homeWin, 
                    $homeLoss, 
                    $homeDraw, 
                    $score_home, 
                    $homeGoalDifference, 
                    $homeClubId, 
                    $leagueId
                );
                $stmt->execute();
                
                // Update away team leaderboard
                $stmt->bind_param(
                    "iiiiiiii", 
                    $awayPoints, 
                    $awayWin, 
                    $awayLoss, 
                    $awayDraw, 
                    $score_away, 
                    $awayGoalDifference, 
                    $awayClubId, 
                    $leagueId
                );
                $stmt->execute();
            }
            case 'league':

                $leagueName = $_POST['league_name'];

                // Check if league name already exists
                $checkStmt = $conn->prepare("SELECT COUNT(*) FROM league WHERE league_name = ?");
                $checkStmt->bind_param("s", $leagueName);
                $checkStmt->execute();
                $checkStmt->bind_result($count);
                $checkStmt->fetch();
                $checkStmt->close();

                if ($count > 0) {
                    echo '<script>alert("League name already registered.")</script>';
                    break; // Exit the switch if league name exists
                }

                // Ensure the session has the organizer_id
                if (isset($_SESSION['organizer_id'])) {
                    // Prepare and execute the insert statement
                    $stmt = $conn->prepare("INSERT INTO league (league_name, season, start_date, end_date, user_id) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssi", $leagueName, $_POST['season'], $_POST['start_date'], $_POST['end_date'], $_SESSION['organizer_id']);
                } else {
                    echo '<script>alert("Organizer ID not set in session.")</script>';
                    break; // Exit the switch if organizer ID is not set
                }
                break;
            case 'player':
                $stmt = $conn->prepare("INSERT INTO player (name, club_id, position, age) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("siis", $_POST['name'], $_POST['club_id'], $_POST['position'], $_POST['age']);
                break;
        }

        // Execute statement only if it's prepared
        if ($stmt) {
            executeStatement($stmt);
            $stmt->close(); // Close the statement here
        }
    }

    // Handle remove action
    if ($action == 'remove') {
        $id = $_POST['id'];
        switch ($table) {
            case 'coach':
                $stmt = $conn->prepare("DELETE FROM coach WHERE coach_id = ?");
                break;
            case 'club':
                $stmt = $conn->prepare("DELETE FROM club WHERE club_id = ?");
                break;
            case 'player':
                $stmt = $conn->prepare("DELETE FROM player WHERE player_id = ?");
                break;
            case 'league':
                $stmt = $conn->prepare("DELETE FROM league WHERE league_id = ?");
                break;
            case 'game':
                $stmt = $conn->prepare("DELETE FROM game WHERE match_id = ?");
        }
        if ($stmt) {
            $stmt->bind_param("i", $id);
            executeStatement($stmt);
            $stmt->close(); // Close the statement after execution
        }
    }
}

$conn->close();
//--------------------------------------------------------------------------------------------------------------------------------------------------
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Dashboard</title>
    <link rel="stylesheet" href="../assests/css/dashboardstyle.css">
    <script src="scripts.js" defer></script>
</head>
<body>
<div class="button-container-3">
            
            <button  onclick="window.location.href='index.php'">LeaguePro</button>
           
        </div>
    <h1>Organizer Dashboard</h1>
    <h3 style="text-align: center;">
    Welcome,  <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?>
</h3>


    <div class="logout">
        <form action="logout.php" method="post">
            <input type="submit" value="Logout">
        </form>
    </div>

    <h2>Add League</h2>
    <div class="section-container">
    <div class="section">
    <form method="POST" action="org_dashboard.php">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="table" value="league">
        <label for="league_name">League Name:</label>
        <input type="text" name="league_name" required>
        <label for="season">Season:</label>
        <input type="text" name="season" required>
        <label for="start_date">Start Date:</label>
        <input type="date" name="start_date" required>
        <label for="end_date">End Date:</label>
        <input type="date" name="end_date" required>
        <label for="user_id">User ID:</label>
        <input type="number" name="user_id" value="<?php echo $_SESSION['organizer_id']; ?>" required readonly>
        <input type="submit" value="Add League">
    </form>
</div>
<h2>Remove League</h2>

<div class="remove-section">
    <form method="POST" action="org_dashboard.php" onsubmit="return confirm('Are you sure you want to remove this record?');">
        <input type="hidden" name="action" value="remove">
        <input type="hidden" name="table" value="league">
        <label for="id">League ID:</label>
        <input type="number" name="id" required>
        <input type="submit" value="Remove League">
    </form>
</div>
</div>
    <h2>Add Club</h2>
<div class="section-container">
    <div class="section">
        <form method="POST" action="org_dashboard.php">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="table" value="club">
            <label for="name">Club Name:</label>
            <input type="text" name="name" required>
            <label for="location">Location:</label>
            <input type="text" name="location" required>
            <label for="founded_year">Founded Year:</label>
            <input type="number" name="founded_year" required>
            <label for="league_id">League ID:</label>
            <input type="number" name="league_id" required>
            <input type="submit" value="Add Club">
        </form>
    </div>
    <h2>Remove Club</h2>

    <div class="remove-section">
        <form method="POST" action="org_dashboard.php" onsubmit="return confirm('Are you sure you want to remove this record?');">
            <input type="hidden" name="action" value="remove">
            <input type="hidden" name="table" value="club">
            <label for="id">Club ID:</label>
            <input type="number" name="id" required>
            <input type="submit" value="Remove Club">
        </form>
    </div>
</div>

<h2>Add Coach</h2>
<div class="section-container">
<div class="section">
<form method="POST" action="org_dashboard.php">
    <input type="hidden" name="action" value="add">
    <input type="hidden" name="table" value="coach">
    <label for="name">Coach Name:</label>
    <input type="text" name="name" required>
    <label for="experience">Experience:</label>
    <input type="number" name="experience" required>
    <label for="age">Age:</label>
    <input type="number" name="age" required>
    <label for="club_id">Club ID:</label>
    <input type="number" name="club_id" required>
    <input type="submit" value="Add Coach">
</form>
</div>
<h2>Remove Coach</h2>
<div class="remove-section">
<form method="POST" action="org_dashboard.php" onsubmit="return confirm('Are you sure you want to remove this coach?');">
    <input type="hidden" name="action" value="remove">
    <input type="hidden" name="table" value="coach">
    <label for="club_id">Club ID:</label>
    <input type="number" name="club_id" required>
    <input type="submit" value="Remove Coach">
</form>
</div>
</div>
<h2>Add Player</h2>
<div class="section-container">
    <div class="section">
<form method="POST" action="org_dashboard.php">
    <input type="hidden" name="action" value="add">
    <input type="hidden" name="table" value="player">
    <label for="name">Player Name:</label>
    <input type="text" name="name" required>
    <label for="age">Age:</label>
    <input type="number" name="age" required>
    <label for="position">Position:</label>
    <input type="text" name="position" required>
    <label for="club_id">Club ID:</label>
    <input type="number" name="club_id" required>
    <input type="submit" value="Add Player">
</form>
</div>
<h2>Remove Player</h2>
<div class="remove-section">


<form method="POST" action="org_dashboard.php" onsubmit="return confirm('Are you sure you want to remove this player?');">
    <input type="hidden" name="action" value="remove">
    <input type="hidden" name="table" value="player">
    <label for="id">Player ID:</label>
    <input type="number" name="id" required>
    <input type="submit" value="Remove Player">
</form>
</div>
</div>
    <h2>Add Game</h2>
<div class="section-container">
    <div class="section">
        <form method="POST" action="org_dashboard.php">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="table" value="game">
            <label for="league_id">League ID:</label>
            <input type="number" name="league_id" required>
            <label for="home_club_id">Home Club ID:</label>
            <input type="number" name="home_club_id" required>
            <label for="away_club_id">Away Club ID:</label>
            <input type="number" name="away_club_id" required>
            <label for="date">Date:</label>
            <input type="date" name="date" required>
            <label for="time">Time:</label>
            <input type="time" name="time" required>
            <label for="score_home">Home Score:</label>
            <input type="text" name="score_home" required>
            <label for="score_away">Away Score:</label>
            <input type="text" name="score_away" required>
            <input type="submit" value="Add Game">
        </form>
    </div>
    <h2>Remove Game</h2>
    <div class="remove-section">
        <form method="POST" action="org_dashboard.php" onsubmit="return confirm('Are you sure you want to remove this record?');">
            <input type="hidden" name="action" value="remove">
            <input type="hidden" name="table" value="game">
            <label for="id">Game ID:</label>
            <input type="number" name="id" required>
            <input type="submit" value="Remove Game">
        </form>
    </div>
</div>
    

    
</body>
</html>
