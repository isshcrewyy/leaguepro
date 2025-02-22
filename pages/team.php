<?php
// Database connection
include 'db_connection.php';
session_start();

if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

$name = $_SESSION['name'];

// SQL query to fetch players and coaches with prepared statements
$stmt_players = $conn->prepare("SELECT player_id, p_name, age, position, club_id, phone_number FROM player WHERE created_by = ?");
$stmt_players->bind_param("s", $name);
$stmt_players->execute();
$players_result = $stmt_players->get_result();

$stmt_coaches = $conn->prepare("SELECT coach_id, co_name, age, experience, club_id, phone_number FROM coach WHERE created_by = ?");
$stmt_coaches->bind_param("s", $name);
$stmt_coaches->execute();
$coaches_result = $stmt_coaches->get_result();

// Create arrays to store data
$players = [];
$coaches = [];

while ($row = $players_result->fetch_assoc()) {
    $players[] = $row;
}

while ($row = $coaches_result->fetch_assoc()) {
    $coaches[] = $row;
}

  // Handle Remove Request
  if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'remove') {
    if (!isset($_POST['type']) || !isset($_POST['id'])) {
        echo "<script>alert('Invalid request!'); window.history.back();</script>";
        exit();
    }

    $type = $_POST['type'];
    $id = intval($_POST['id']); 

    if ($type === 'player') {
        $stmt = $conn->prepare("DELETE FROM player WHERE player_id = ?");
    } elseif ($type === 'coach') {
        $stmt = $conn->prepare("DELETE FROM coach WHERE coach_id = ?");
    } else {
        echo "<script>alert('Invalid type!'); window.history.back();</script>";
        exit();
    }

    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Record removed successfully!'); window.location.href = 'team.php';</script>";
    } else {
        echo "<script>alert('Error removing record.'); window.history.back();</script>";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate that type is set
    if (!isset($_POST['type'])) {
        echo "<script>alert('Form type not specified!'); window.history.back();</script>";
        exit();
    }

    $type = $_POST['type'];

    if ($type === 'player') {
        // Validate player form data
        if (!isset($_POST['p_name']) || empty($_POST['p_name'])) {
            echo "<script>alert('Player name is required!'); window.history.back();</script>";
            exit();
        }

        // Get and sanitize player data
        $player_name = trim($_POST['p_name']);
        $age = isset($_POST['age']) ? (int)$_POST['age'] : 0;
        $position = isset($_POST['position']) ? trim($_POST['position']) : '';
        $club_id = isset($_POST['club_id']) ? trim($_POST['club_id']) : '';
        $phone_number = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';

        // Validate required fields
        if (empty($player_name) || empty($position) || empty($club_id)) {
            echo "<script>alert('All required fields must be filled!'); window.history.back();</script>";
            exit();
        }

        try {
            $stmt = $conn->prepare("INSERT INTO player (p_name, age, position, club_id, phone_number, created_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sissss", $player_name, $age, $position, $club_id, $phone_number, $name);

            if ($stmt->execute()) {
                echo "<script>alert('Player added successfully!'); window.location.href = 'team.php';</script>";
            } else {
                throw new Exception("Error executing query");
            }
        } catch (Exception $e) {
            echo "<script>alert('Error adding player: " . htmlspecialchars($e->getMessage()) . "'); window.history.back();</script>";
        }

    } elseif ($type === 'coach') {
        // Validate coach form data
        if (!isset($_POST['co_name']) || empty($_POST['co_name'])) {
            echo "<script>alert('Coach name is required!'); window.history.back();</script>";
            exit();
        }

        // Get and sanitize coach data
        $coach_name = trim($_POST['co_name']);
        $experience = isset($_POST['experience']) ? (int)$_POST['experience'] : 0;
        $age = isset($_POST['age']) ? (int)$_POST['age'] : 0;
        $club_id = isset($_POST['club_id']) ? trim($_POST['club_id']) : '';
        $phone_number = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';

        // Validate required fields
        if (empty($coach_name) || empty($club_id)) {
            echo "<script>alert('All required fields must be filled!'); window.history.back();</script>";
            exit();
        }

        try {
            $stmt = $conn->prepare("INSERT INTO coach (co_name, experience, age, club_id, phone_number, created_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siisss", $coach_name, $experience, $age, $club_id, $phone_number, $name);

            if ($stmt->execute()) {
                echo "<script>alert('Coach added successfully!'); window.location.href = 'team.php';</script>";
            } else {
                throw new Exception("Error executing query");
            }
        } catch (Exception $e) {
            echo "<script>alert('Error adding coach: " . htmlspecialchars($e->getMessage()) . "'); window.history.back();</script>";
        }
    }
  

}

// Don't close the connection here as we need it for the HTML part
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
                <span class="display-value"><?php echo htmlspecialchars($player['p_name']); ?></span>
                <input type="text" class="edit-input" value="<?php echo htmlspecialchars($player['p_name']); ?>" style="display: none;">
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
                <form method="POST" action="team.php" onsubmit="return confirm('Are you sure you want to remove this player?');">
    <input type="hidden" name="action" value="remove">
    <input type="hidden" name="type" value="player">
    <input type="hidden" name="id" value="<?php echo $player['player_id']; ?>">
    <input type="submit" class="remove-button" value="Remove">
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
                <span class="display-value"><?php echo htmlspecialchars($coach['co_name']); ?></span>
                <input type="text" class="edit-input" value="<?php echo htmlspecialchars($coach['co_name']); ?>" style="display: none;">
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
        <label for="p_name">Player Name *</label>
        <input type="text" name="p_name" required>

        <label for="player_age">Age *</label>
        <input type="number" id="player_age" name="age" placeholder="Enter Age" required />

        <label for="position">Position *</label>
        <input type="text" id="position" name="position" placeholder="Forward, Wing, etc." required />

        <label for="club_id">Club ID *</label>
        <select name="club_id" id="club_id" required>
            <option value="">Select Club</option>
            <?php
            $stmt = $conn->prepare("SELECT club_id, c_name FROM club WHERE created_by = ?");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                echo "<option value='" . $row['club_id'] . "'>" . htmlspecialchars($row['c_name']) . "</option>";
            }
            ?>
        </select>

        <label for="phone_number">Phone Number *</label>
        <input type="text" id="phone_number" name="phone_number" placeholder="Enter Phone Number" required />

        <button type="submit" class="btn-submit">Add Player</button>
        </form>


        <!-- Add Coach Form -->
        <form id="coachForm" method="POST" action="team.php" style="display: none;">
        <input type="hidden" name="type" value="coach">
        <label for="co_name">Coach Name *</label>
        <input type="text" name="co_name" required>

        <label for="experience">Experience *</label>
        <input type="text" id="experience" name="experience" placeholder="Enter Experience" required />

        <label for="coach_age">Age *</label>
        <input type="number" id="coach_age" name="age" placeholder="Enter Age" required />

        <label for="club_id_coach">Club ID *</label>
        <select name="club_id" id="club_id_coach" required>
            <option value="">Select Club</option>
            <?php
            $stmt = $conn->prepare("SELECT club_id, c_name FROM club WHERE created_by = ?");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                echo "<option value='" . $row['club_id'] . "'>" . htmlspecialchars($row['c_name']) . "</option>";
            }
            ?>
        </select>
       

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
