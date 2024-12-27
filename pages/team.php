<?php
// Start session at the beginning
session_start();

// Database connection
include 'db_connection.php';

// Check if user is logged in and has necessary session variables
if (!isset($_SESSION['user_id']) || !isset($_SESSION['league_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$league_id = $_SESSION['league_id'];

// Verify user has permission to access this league
$stmt = $conn->prepare("SELECT * FROM league_users WHERE user_id = ? AND league_id = ?");
$stmt->bind_param("ii", $user_id, $league_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("You don't have permission to access this league.");
}

// SQL queries with prepared statements for players and coaches
$sql_players = $conn->prepare("SELECT player_id, name, age, position, club_id, phone_number 
                              FROM player 
                              WHERE league_id = ? AND EXISTS (
                                  SELECT 1 FROM league_users 
                                  WHERE league_users.league_id = player.league_id 
                                  AND league_users.user_id = ?
                              )");

$sql_coaches = $conn->prepare("SELECT coach_id, name, age, experience, club_id, phone_number 
                             FROM coach 
                             WHERE league_id = ? AND EXISTS (
                                 SELECT 1 FROM league_users 
                                 WHERE league_users.league_id = coach.league_id 
                                 AND league_users.user_id = ?
                             )");

// Execute queries with user verification
$sql_players->bind_param("ii", $league_id, $user_id);
$sql_players->execute();
$players_result = $sql_players->get_result();

$sql_coaches->bind_param("ii", $league_id, $user_id);
$sql_coaches->execute();
$coaches_result = $sql_coaches->get_result();

// Create arrays to store data
$players = [];
$coaches = [];

while ($row = $players_result->fetch_assoc()) {
    $players[] = $row;
}

while ($row = $coaches_result->fetch_assoc()) {
    $coaches[] = $row;
}

// Handle player removal
if (isset($_POST['action']) && $_POST['action'] == 'remove' && isset($_POST['type'])) {
    if ($_POST['type'] == 'player') {
        $player_id = $_POST['id'];
        
        // Verify user has permission to remove this player
        $stmt = $conn->prepare("DELETE FROM player 
                              WHERE player_id = ? 
                              AND league_id = ? 
                              AND EXISTS (
                                  SELECT 1 FROM league_users 
                                  WHERE league_users.league_id = player.league_id 
                                  AND league_users.user_id = ?
                              )");
        $stmt->bind_param("iii", $player_id, $league_id, $user_id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            echo "<script>alert('Player removed successfully!'); window.location.href = 'team.php';</script>";
        } else {
            echo "<script>alert('Error: Unable to remove player or permission denied.'); window.history.back();</script>";
        }
    } elseif ($_POST['type'] == 'coach') {
        $coach_id = $_POST['id'];
        
        // Verify user has permission to remove this coach
        $stmt = $conn->prepare("DELETE FROM coach 
                              WHERE coach_id = ? 
                              AND league_id = ? 
                              AND EXISTS (
                                  SELECT 1 FROM league_users 
                                  WHERE league_users.league_id = coach.league_id 
                                  AND league_users.user_id = ?
                              )");
        $stmt->bind_param("iii", $coach_id, $league_id, $user_id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            echo "<script>alert('Coach removed successfully!'); window.location.href = 'team.php';</script>";
        } else {
            echo "<script>alert('Error: Unable to remove coach or permission denied.'); window.history.back();</script>";
        }
    }
}

// Handle form submissions for adding players/coaches
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['type'])) {
    $type = $_POST['type'];

    if ($type === 'player') {
        $name = $_POST['name'];
        $age = $_POST['age'];
        $position = $_POST['position'];
        $club_id = $_POST['club_id'];
        $phone_number = $_POST['phone_number'];

        // Insert player with league_id
        $stmt = $conn->prepare("INSERT INTO player (name, age, position, club_id, phone_number, league_id) 
                              SELECT ?, ?, ?, ?, ?, ? 
                              FROM league_users 
                              WHERE user_id = ? AND league_id = ?");
        $stmt->bind_param("sississi", $name, $age, $position, $club_id, $phone_number, $league_id, $user_id, $league_id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo "<script>alert('Player added successfully!'); window.location.href = 'team.php';</script>";
        } else {
            echo "<script>alert('Error adding player: Permission denied'); window.history.back();</script>";
        }
    } elseif ($type === 'coach') {
        $name = $_POST['name'];
        $experience = $_POST['experience'];
        $age = $_POST['age'];
        $club_id = $_POST['club_id'];
        $phone_number = $_POST['phone_number'];

        // Insert coach with league_id
        $stmt = $conn->prepare("INSERT INTO coach (name, experience, age, club_id, phone_number, league_id) 
                              SELECT ?, ?, ?, ?, ?, ? 
                              FROM league_users 
                              WHERE user_id = ? AND league_id = ?");
        $stmt->bind_param("siissiii", $name, $experience, $age, $club_id, $phone_number, $league_id, $user_id, $league_id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo "<script>alert('Coach added successfully!'); window.location.href = 'team.php';</script>";
        } else {
            echo "<script>alert('Error adding coach: Permission denied'); window.history.back();</script>";
        }
    }
}

// Close all prepared statements
if (isset($stmt)) $stmt->close();
$sql_players->close();
$sql_coaches->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Team</title>
    <link rel="stylesheet" href="../assests/css/playerForm.css">
    <script src="../assests/js/playerScript.js"></script>
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

<div class="container">
    <h1>Your Team</h1>
    
    <!-- Tabs Navigation -->
    <div class="tabs">
        <button id="viewTeamTab" class="tab-button active" onclick="showTab('viewTeam')">View Team</button>
        <button id="addMemberTab" class="tab-button" onclick="showTab('addMember')">Add Player/Coach</button>
    </div>

    <!-- Tab Content: View Team -->
    <div id="viewTeam" class="tab-content active">
      
        <!-- Here you can display your team data (this part would typically be dynamic) -->
         
        <h1>Players and Coaches List</h1>

<div class="container">
    <div class="table-container">
        <h2>Players</h2>
        <table id="playersTable">
            <thead>
                <tr>
                    <th>Player ID</th>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Position</th>
                    <th>Club ID</th>
                    <th>Phone Number</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
    <?php foreach ($players as $player): ?>
        <tr id="player_<?php echo $player['player_id']; ?>">
            <td><?php echo $player['player_id']; ?></td>
            <td>
                <span class="display-value"><?php echo htmlspecialchars($player['name']); ?></span>
                <input type="text" class="edit-input" value="<?php echo htmlspecialchars($player['name']); ?>" style="display: none;">
            </td>
            <td>
                <span class="display-value"><?php echo $player['age']; ?></span>
                <input type="number" class="edit-input" value="<?php echo $player['age']; ?>" style="display: none;">
            </td>
            <td>
                <span class="display-value"><?php echo htmlspecialchars($player['position']); ?></span>
                <input type="text" class="edit-input" value="<?php echo htmlspecialchars($player['position']); ?>" style="display: none;">
            </td>
            <td>
                <span class="display-value"><?php echo $player['club_id']; ?></span>
                <input type="number" class="edit-input" value="<?php echo $player['club_id']; ?>" style="display: none;">
            </td>
            <td>
                <span class="display-value"><?php echo htmlspecialchars($player['phone_number']); ?></span>
                <input type="text" class="edit-input" value="<?php echo htmlspecialchars($player['phone_number']); ?>" style="display: none;">
            </td>
            <td>
                <button class="edit-btn" onclick="toggleEdit(this, 'player', <?php echo $player['player_id']; ?>)">Edit</button>
                <button class="save-btn" onclick="saveChanges(this, 'player', <?php echo $player['player_id']; ?>)" style="display: none;">Save</button>
                <button class="cancel-btn" onclick="cancelEdit(this, 'player', <?php echo $player['player_id']; ?>)" style="display: none;">Cancel</button>
                <form method="POST" action="team.php" onsubmit="return confirm('Are you sure you want to remove this record?');" style="display:inline;">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="id" value="<?php echo $player['player_id']; ?>">
                    <input type="submit" value="Remove">
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
        </table>
    </div>

    <div class="table-container">
        <h2>Coaches</h2>
        <table id="coachesTable">
            <thead>
                <tr>
                    <th>Coach ID</th>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Experience</th>
                    <th>Club ID</th>
                    <th>Phone Number</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
    <?php foreach ($coaches as $coach): ?>
        <tr id="coach_<?php echo $coach['coach_id']; ?>">
            <td><?php echo $coach['coach_id']; ?></td>
            <td>
                <span class="display-value"><?php echo htmlspecialchars($coach['name']); ?></span>
                <input type="text" class="edit-input" value="<?php echo htmlspecialchars($coach['name']); ?>" style="display: none;">
            </td>
            <td>
                <span class="display-value"><?php echo $coach['age']; ?></span>
                <input type="number" class="edit-input" value="<?php echo $coach['age']; ?>" style="display: none;">
            </td>
            <td>
                <span class="display-value"><?php echo $coach['experience']; ?></span>
                <input type="number" class="edit-input" value="<?php echo $coach['experience']; ?>" style="display: none;">
            </td>
            <td>
                <span class="display-value"><?php echo $coach['club_id']; ?></span>
                <input type="number" class="edit-input" value="<?php echo $coach['club_id']; ?>" style="display: none;">
            </td>
            <td>
                <span class="display-value"><?php echo htmlspecialchars($coach['phone_number']); ?></span>
                <input type="text" class="edit-input" value="<?php echo htmlspecialchars($coach['phone_number']); ?>" style="display: none;">
            </td>
            <td>
                <button class="edit-btn" onclick="toggleEdit(this, 'coach', <?php echo $coach['coach_id']; ?>)">Edit</button>
                <button class="save-btn" onclick="saveChanges(this, 'coach', <?php echo $coach['coach_id']; ?>)" style="display: none;">Save</button>
                <button class="cancel-btn" onclick="cancelEdit(this, 'coach', <?php echo $coach['coach_id']; ?>)" style="display: none;">Cancel</button>
                <form method="POST" action="team.php" onsubmit="return confirm('Are you sure you want to remove this record?');" style="display:inline;">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="id" value="<?php echo $coach['coach_id']; ?>">
                    <input type="submit" value="Remove">
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
        </table>
    </div>
</div>
    </div>

    <!-- Tab Content: Add Player/Coach -->
    <div id="addMember" class="tab-content">
        <h2>Add Player or Coach</h2>
        
        <!-- Navigation to toggle between forms -->
        <div class="tabs">
            <button id="addPlayerTabBtn" class="tab-button active" onclick="toggleForm('player')">Add Player</button>
            <button id="addCoachTabBtn" class="tab-button" onclick="toggleForm('coach')">Add Coach</button>
        </div>

        <!-- Add Player Form -->
        <form id="playerForm" method="POST" action="team.php" style="display: block;">
        <input type="hidden" name="type" value="player">
        <label for="player_name">Player Name *</label>
        <input type="text" id="player_name" name="name" placeholder="Enter Name" required />

        <label for="player_age">Age *</label>
        <input type="number" id="player_age" name="age" placeholder="Enter Age" required />

        <label for="position">Position *</label>
        <input type="text" id="position" name="position" placeholder="Forward, Wing, etc." required />

        <label for="club_id">Club ID *</label>
        <input type="number" id="club_id" name="club_id" placeholder="Enter Club ID" required />

        <label for="phone_number">Phone Number *</label>
        <input type="text" id="phone_number" name="phone_number" placeholder="Enter Phone Number" required />

        <button type="submit" class="btn-submit">Add Player</button>
        </form>


        <!-- Add Coach Form -->
        <form id="coachForm" method="POST" action="team.php" style="display: none;">
        <input type="hidden" name="type" value="coach">
        <label for="coach_name">Coach Name *</label>
        <input type="text" id="coach_name" name="name" placeholder="Enter Name" required />

        <label for="experience">Experience *</label>
        <input type="text" id="experience" name="experience" placeholder="Enter Experience" required />

        <label for="coach_age">Age *</label>
        <input type="number" id="coach_age" name="age" placeholder="Enter Age" required />

        <label for="club_id_coach">Club ID *</label>
        <input type="number" id="club_id_coach" name="club_id" placeholder="Enter Club ID" required />

        <label for="phone_number_coach">Phone Number *</label>
        <input type="text" id="phone_number_coach" name="phone_number" placeholder="Enter Phone Number" required />

        <button type="submit" class="btn-submit">Add Coach</button>
        </form>

    </div>
</div>

<!-- JavaScript for Tab Switching and Form Toggling -->
<script>
    function showTab(tabId) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });

        // Deactivate all buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active');
        });

        // Show the selected tab and activate the corresponding button
        document.getElementById(tabId).classList.add('active');
        document.querySelector(`.tab-button[onclick="showTab('${tabId}')"]`).classList.add('active');
    }

    function toggleForm(formType) {
        // Show/Hide the Player and Coach forms based on the selected button
        if (formType === 'player') {
            document.getElementById('playerForm').style.display = 'block';
            document.getElementById('coachForm').style.display = 'none';
            document.getElementById('addPlayerTabBtn').classList.add('active');
            document.getElementById('addCoachTabBtn').classList.remove('active');
        } else if (formType === 'coach') {
            document.getElementById('playerForm').style.display = 'none';
            document.getElementById('coachForm').style.display = 'block';
            document.getElementById('addCoachTabBtn').classList.add('active');
            document.getElementById('addPlayerTabBtn').classList.remove('active');
        }
    }
</script>

<!-- Styling for Tabs -->
<style>
    .tabs {
        margin-bottom: 20px;
    }
    .tab-button {
        padding: 10px 20px;
        background-color: #f4f4f4;
        border: 1px solid #ccc;
        cursor: pointer;
    }
    .tab-button.active {
        background-color: #ddd;
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }
</style>

</body>
</html>
