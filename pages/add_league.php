<?php
session_start();
require_once 'db_connection.php';

// Ensure the organizer ID exists in the session
if (!isset($_SESSION['userId'])) {
    echo '<script>alert("Organizer ID not set. Please log in."); window.location.href = "login.php";</script>';
    exit;
}

$userId = $_SESSION['userId'];
$selectedLeagueId = isset($_GET['league_id']) ? $_GET['league_id'] : '';

// Fetch leagues created by the logged-in user
$sql = "SELECT * FROM league WHERE userId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$leagues = $result->fetch_all(MYSQLI_ASSOC);

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add') {
        $league_name = trim($_POST['league_name']);
        $season = $_POST['season'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        // Check if league name already exists for this user
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM league WHERE league_name = ? AND userId = ?");
        $checkStmt->bind_param("si", $league_name, $userId);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count > 0) {
            echo '<script>alert("League name already registered.");</script>';
        } else {
            // Insert league into the database
            $stmt = $conn->prepare("INSERT INTO league (league_name, season, start_date, end_date, userId) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $league_name, $season, $start_date, $end_date, $userId);

            if ($stmt->execute()) {
                echo '<script>alert("League added successfully.");</script>';
            } else {
                echo '<script>alert("Failed to add league: ' . $stmt->error . '");</script>';
            }
            $stmt->close();
        }
    } elseif ($action === 'remove') {
        $leagueId = $_POST['id'];

        // Remove league from the database
        $stmt = $conn->prepare("DELETE FROM league WHERE league_id = ? AND userId = ?");
        $stmt->bind_param("ii", $leagueId, $userId);

        if ($stmt->execute()) {
            echo '<script>alert("League removed successfully.");</script>';
        } else {
            echo '<script>alert("Failed to remove league: ' . $stmt->error . '");</script>';
        }
        $stmt->close();
    }

    // Handle club addition
    if ($action === 'add_club') {
        $name = trim($_POST['name']);
        $location = trim($_POST['location']);
        $founded_year = intval($_POST['founded_year']);
        $league_id = intval($_POST['league_id']);

        // Check if the club name already exists in the selected league
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM club WHERE name = ? AND league_id = ?");
        $checkStmt->bind_param("si", $name, $league_id);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count > 0) {
            echo '<script>alert("Club name already exists in this league.");</script>';
        } else {
            // Add the new club
            $stmt = $conn->prepare("INSERT INTO club (name, location, founded_year, league_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssii", $name, $location, $founded_year, $league_id);
            if ($stmt->execute()) {
                echo '<script>alert("Club added successfully.");</script>';
            } else {
                echo '<script>alert("Failed to add club: ' . $stmt->error . '");</script>';
            }
            $stmt->close();
        }
    }
}

// Fetch clubs associated with the selected league
$clubs = [];
if ($selectedLeagueId) {
    $sql = "SELECT * FROM club WHERE league_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $selectedLeagueId);
    $stmt->execute();
    $clubs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Dashboard</title>
    <link rel="stylesheet" href="../assests/css/leagueAdd.css">
</head>
<body>

<nav class="navbar">
<a href="org_dashboard.php" class="logo">Organizer</a>

    <span class="menu-toggle" onclick="toggleMenu()">☰</span>
    <ul id="nav-links">
    <li><a href="add_league.php">Add League</a></li>
      <li><a href="team.php">Your team</a></li>
      <li><a href="add_game.php">Add Game</a></li>
      <li><a href="#leaderboard">Update Leaderboard</a></li>
      <li><a href="#logout">Logout</a></li>
    </ul>
</nav>

<script>
    function toggleMenu() {
        const navLinks = document.getElementById('nav-links');
        navLinks.classList.toggle('active');
    }
</script>

<h2>Add League</h2>
<form method="POST" action="add_league.php">
    <input type="hidden" name="action" value="add">
    <label for="league_name">League Name:</label>
    <input type="text" name="league_name" required>
    <label for="season">Season:</label>
    <input type="text" name="season" required>
    <label for="start_date">Start Date:</label>
    <input type="date" name="start_date" required>
    <label for="end_date">End Date:</label>
    <input type="date" name="end_date" required>
    <label for="userId">User ID:</label>
    <input type="number" name="userId" value="<?php echo $_SESSION['userId']; ?>" required readonly>
    <input type="submit" value="Add League">
</form>


<h2>Your Leagues</h2>
<table border="1">
    <thead>
        <tr>
            <th>League ID</th>
            <th>League Name</th>
            <th>Season</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($leagues)): ?>
            <?php foreach ($leagues as $league): ?>
                <tr>
                    <td><?php echo htmlspecialchars($league['league_id']); ?></td>
                    <td><?php echo htmlspecialchars($league['league_name']); ?></td>
                    <td><?php echo htmlspecialchars($league['season']); ?></td>
                    <td><?php echo htmlspecialchars($league['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($league['end_date']); ?></td>
                    <td>
                        <!-- Edit Link -->
                        <a href="edit_league.php?league_id=<?php echo $league['league_id']; ?>">Edit</a>

                        <!-- Remove button (submit the form to remove league) -->
                        <form method="POST" action="add_league.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to remove this league?');">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="id" value="<?php echo $league['league_id']; ?>">
                            <input type="submit" value="Remove" />
                        </form>
                    </td>

                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No leagues found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
<br>

<?php if (!empty($leagues)): ?>
    <!-- Form for adding clubs -->
    <form method="POST" action="add_league.php">
        <input type="hidden" name="action" value="add_club">
        <h1>Club Section</h1>
        <label for="league_id">Select League:</label>
        <select name="league_id" required onchange="window.location.href = '?league_id=' + this.value;">
            <option value="">--Select League--</option>
            <?php foreach ($leagues as $league): ?>
                <option value="<?php echo $league['league_id']; ?>" <?php echo ($selectedLeagueId == $league['league_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($league['league_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if ($selectedLeagueId): ?>
            <h2>Clubs in this League</h2>
            <ul>
                <?php foreach ($clubs as $club): ?>
                    <li><?php echo htmlspecialchars($club['name']); ?> - <?php echo htmlspecialchars($club['club_id']); ?></li>
                <?php endforeach; ?>
            </ul>

            <!-- Club details form -->
            <h2>Add New Club</h2>
            <label for="name">Club Name:</label>
            <input type="text" name="name" required>
            <label for="location">Location:</label>
            <input type="text" name="location" required>
            <label for="founded_year">Founded Year:</label>
            <input type="number" name="founded_year" required>
            <input type="submit" value="Add Club">
        <?php endif; ?>
    </form>
<?php endif; ?>
</body>
</html>
