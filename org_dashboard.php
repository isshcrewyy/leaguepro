<?php
session_start();
$action = ''; // Default initialization

// Check if the session is set; otherwise, check the cookie
if (!isset($_SESSION['organizer_id'])) {
    if (isset($_COOKIE['organizer_id'])) {
        $_SESSION['organizer_id'] = $_COOKIE['organizer_id'];
    } else {
        header("Location: login.php");
        exit();
    }
}

// Ensure the session is valid; if not, redirect to login
if (!isset($_SESSION['organizer_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection details
$host = "localhost";
$user = "root";
$password = "";
$dbname = "leaguedb";

$conn = new mysqli($host, $user, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    $table = $_POST['table'];
    $stmt = null;

    if ($action == 'add') {
        // Prepare and execute insertion based on the table
        if ($table == 'coach') {
            $stmt = $conn->prepare("INSERT INTO coach (name, experience, age) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $_POST['name'], $_POST['experience'], $_POST['age']);
        } elseif ($table == 'club') {
            if (!empty($_POST['coach_id'])) {
                $stmt = $conn->prepare("INSERT INTO club (name, location, founded_year, coach_id, league_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssiii", $_POST['name'], $_POST['location'], $_POST['founded_year'], $_POST['coach_id'], $_POST['league_id']);
            } else {
                echo "Please provide a valid Coach ID.";
            }
        } elseif ($table == 'game') {
            $stmt = $conn->prepare("INSERT INTO game (league_id, home_club_id, away_club_id, date, time, score_home, score_away) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiissss", $_POST['league_id'], $_POST['home_club_id'], $_POST['away_club_id'], $_POST['date'], $_POST['time'], $_POST['score_home'], $_POST['score_away']);
        } elseif ($table == 'leaderboard') {
            $stmt = $conn->prepare("INSERT INTO leaderboard (league_id, club_id, points, wins, losses, draws, goals, goal_difference) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiiiiiii", $_POST['league_id'], $_POST['club_id'], $_POST['points'], $_POST['wins'], $_POST['losses'], $_POST['draws'], $_POST['goals'], $_POST['goal_difference']);
        } elseif($table == 'league') {
            $leagueName = $_POST['league_name'];
            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM league WHERE league_name = ?");
            $checkStmt->bind_param("s", $leagueName);
            $checkStmt->execute();
            $checkStmt->bind_result($count);
            $checkStmt->fetch();
            $checkStmt->close();

            if ($count > 0) {
                echo '<script>alert("already registered name")</script>';
                return; // Stop execution if league name already exists
            }

            // Proceed to insert if the league name is unique
            $stmt = $conn->prepare("INSERT INTO league (league_name, season, start_date, end_date, user_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $leagueName, $_POST['season'], $_POST['start_date'], $_POST['end_date'], $_POST['user_id']);
        }
        } elseif ($table == 'player') {
            $stmt = $conn->prepare("INSERT INTO player (name, club_id, position, age) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("siis", $_POST['name'], $_POST['club_id'], $_POST['position'], $_POST['age']);
        }

        // Execute and check for errors
        if ($stmt) {
            if ($stmt->execute()) {
                echo "Record added successfully.";
            } else {
                echo "Error adding record: " . $stmt->error;
            }
            $stmt->close();
        }
    } elseif ($action == 'remove') {
        $id = $_POST['id'];
        // Prepare deletion based on the table
        if ($table == 'coach') {
            $stmt = $conn->prepare("DELETE FROM coach WHERE coach_id = ?");
            $stmt->bind_param("i", $id);
        } elseif ($table == 'club') {
            $stmt = $conn->prepare("DELETE FROM club WHERE club_id = ?");
            $stmt->bind_param("i", $id);
        } elseif ($table == 'player') {
            $stmt = $conn->prepare("DELETE FROM player WHERE player_id = ?");
            $stmt->bind_param("i", $id);
        } elseif ($table == 'league') {
            $stmt = $conn->prepare("DELETE FROM league WHERE league_id = ?");
            $stmt->bind_param("i", $id);
        }

        // Execute and check for errors
        if ($stmt) {
            if ($stmt->execute()) {
                echo "Record removed successfully.";
            } else { 
                echo "Error removing record: " . $stmt->error;
            }
            $stmt->close();
        }
    }


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Dashboard</title>
    <link rel="stylesheet" href="dashboardstyle.css">
    <script src="scripts.js" defer></script>
</head>
<body>
    <h1>Organizer Dashboard</h1>
    <h2 style="text-align: center;">Welcome, User <?php echo $_SESSION['organizer_id']; ?></h2> <!-- Centered welcome message -->
    
    <div class="logout">
        <form action="logout.php" method="post">
            <input type="submit" value="Logout">
        </form>
    </div>

    <h2>Add League</h2>
    <form method="POST" action="org_dashboard.php" >
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

    <h2>Add Club</h2>
    <form method="POST" action="org_dashboard.php">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="table" value="club">
        <label for="name">Club Name:</label>
        <input type="text" name="name" required>
        <label for="location">Location:</label>
        <input type="text" name="location" required>
        <label for="founded_year">Founded Year:</label>
        <input type="number" name="founded_year" required>
        <label for="coach_id">Coach ID:</label>
        <input type="number" name="coach_id" required>
        <label for="league_id">League ID:</label>
        <input type="number" name="league_id" required>
        <input type="submit" value="Add Club">
    </form>

    <h2>Add Coach</h2>
    <form method="POST" action="org_dashboard.php">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="table" value="coach">
        <label for="name">Name:</label>
        <input type="text" name="name" required>
        <label for="experience">Experience:</label>
        <input type="text" name="experience" required>
        <label for="age">Age:</label>
        <input type="number" name="age" required>
        <input type="submit" value="Add Coach">
    </form>

    <h2>Add Player</h2>
    <form method="POST" action="org_dashboard.php">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="table" value="player">
        <label for="name">Player Name:</label>
        <input type="text" name="name" required>
        <label for="club_id">Club ID:</label>
        <input type="number" name="club_id" required>
        <label for="position">Position:</label>
        <input type="text" name="position" required>
        <label for="age">Age:</label>
        <input type="number" name="age" required>
        <input type="submit" value="Add Player">
    </form>

    <h2>Remove Coach</h2>
    <form method="POST" action="org_dashboard.php">
        <input type="hidden" name="action" value="remove">
        <input type="hidden" name="table" value="coach">
        <label for="coach_id">Coach ID:</label>
        <input type="number" name="id" required>
        <input type="submit" value="Remove Coach">
    </form>

    <h2>Remove Club</h2>
    <form method="POST" action="org_dashboard.php">
        <input type="hidden" name="action" value="remove">
        <input type="hidden" name="table" value="club">
        <label for="club_id">Club ID:</label>
        <input type="number" name="id" required>
        <input type="submit" value="Remove Club">
    </form>

    <h2>Remove Player</h2>
    <form method="POST" action="org_dashboard.php">
        <input type="hidden" name="action" value="remove">
        <input type="hidden" name="table" value="player">
        <label for="player_id">Player ID:</label>
        <input type="number" name="id" required>
        <input type="submit" value="Remove Player">
    </form>
</body>
</html>
