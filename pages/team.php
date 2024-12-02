<?php
// Database connection
include 'db_connection.php';


// SQL query to fetch players and coaches
$sql_players = "SELECT player_id, name, age, position, club_id, phone_number FROM player";
$sql_coaches = "SELECT coach_id, name, age, experience, club_id, phone_number FROM coach";

// Execute queries
$players_result = $conn->query($sql_players);
$coaches_result = $conn->query($sql_coaches);

// Create an associative array to store data
$players = [];
$coaches = [];

if ($players_result->num_rows > 0) {
    while ($row = $players_result->fetch_assoc()) {
        $players[] = $row;
    }
}

if ($coaches_result->num_rows > 0) {
    while ($row = $coaches_result->fetch_assoc()) {
        $coaches[] = $row;
    }
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $type = $_POST['type']; // Determines whether it's a player or a coach

    if ($type === 'player') {
        // Player form data
        $name = $_POST['name'];
        $age = $_POST['age'];
        $position = $_POST['position'];
        $club_id = $_POST['club_id'];
        $phone_number = $_POST['phone_number'];

        // Insert into player table
        $stmt = $conn->prepare("INSERT INTO player (name, age, position, club_id, phone_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisss", $name, $age, $position, $club_id, $phone_number);

        if ($stmt->execute()) {
            echo "<script>alert('Player added successfully!'); window.location.href = 'team.php';</script>";
        } else {
            echo "<script>alert('Error adding player: " . $stmt->error . "'); window.history.back();</script>";
        }
    } elseif ($type === 'coach') {
        // Coach form data
        $name = $_POST['name'];
        $experience = $_POST['experience'];
        $age = $_POST['age'];
        $club_id = $_POST['club_id'];
        $phone_number = $_POST['phone_number'];

        // Insert into coach table
        $stmt = $conn->prepare("INSERT INTO coach (name, experience, age, club_iD, phone_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siiss", $name, $experience, $age, $club_id, $phone_number);

        if ($stmt->execute()) {
            echo "<script>alert('Coach added successfully!'); window.location.href = 'team.php';</script>";
        } else {
            echo "<script>alert('Error adding coach: " . $stmt->error . "'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Invalid form submission!'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
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
    <span class="menu-toggle" onclick="toggleMenu()">☰</span> <!-- Hamburger Icon -->
    <ul id="nav-links" class="hidden">
        <li><a href="add_league.php">Add League</a></li>
        <li><a href="team.php">Your Team</a></li>
        <li><a href="add_game.php">Add Game</a></li>
        <li><a href="#leaderboard">Update Leaderboard</a></li>
        <li><a href="#logout">Logout</a></li>
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
                    <tr>
                    <td><?php echo $player['player_id']; ?></td>
                        <td><?php echo $player['name']; ?></td>
                        <td><?php echo $player['age']; ?></td>
                        <td><?php echo $player['position']; ?></td>
                        <td><?php echo $player['club_id']; ?></td>
                        <td><?php echo $player['phone_number']; ?></td>
                        <td>
    
    <a href="edit_team.php?player_id=<?php echo $player['player_id']; ?>">Edit</a>
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
                    <tr>
                    <td><?php echo $coach['coach_id']; ?></td>
                        <td><?php echo $coach['name']; ?></td>
                        <td><?php echo $coach['age']; ?></td>
                        <td><?php echo $coach['experience']; ?></td>
                        <td><?php echo $coach['club_id']; ?></td>
                        <td><?php echo $coach['phone_number']; ?></td>
                        <td>
    <!-- Edit Button -->
    <a href="edit_team.php?coach_id=<?php echo $coach['coach_id']; ?>">Edit</a>
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
        <form id="playerForm" style="display: block;">
            <label for="player_name">Player Name *</label>
            <input type="text" id="player_name" name="name" placeholder="Enter Name" required />

            <label for="age">Age *</label>
            <input type="number" id="age" name="age" placeholder="Enter Age" required />

            <label for="position">Position *</label>
            <input type="text" id="position" name="position" placeholder="Forward, Wing, etc." required />

            <label for="club_id">Club ID *</label>
            <input type="number" id="club_id" name="club_id" placeholder="Enter Club ID" required />

            <label for="phone_number">Phone Number *</label>
            <input type="text" id="phone_number" name="phone_number" placeholder="Enter Phone Number" required />
            
            
            <button type="submit" class="btn-submit">Add Player</button>
        </form>

        <!-- Add Coach Form -->
        <form id="coachForm" style="display: none;">
            <label for="coach_name">Coach Name *</label>
            <input type="text" id="coach_name" name="name" placeholder="Enter Name" required />

            <label for="experience">Experience *</label>
            <input type="text" id="experience" name="experience" placeholder="Enter Experience" required />

            <label for="age">Age *</label>
            <input type="number" id="age" name="age" placeholder="Enter Age" required />

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
