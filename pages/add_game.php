<?php
// Start the session
session_start();
$name = $_SESSION['name'];

// Ensure that the user is logged in
if (!isset($_SESSION['userId'])) {
    // If no session, redirect to login
    session_destroy();
    header("Location: login.php");
    exit();
}

// Connect to the database
require 'db_connection.php';

// Get the userId from the session
$userId = $_SESSION['userId'];

// Handling form submission for adding a game
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $league_id = $_POST['league_id'];
        $home_club_id = $_POST['home_club_id'];
        $away_club_id = $_POST['away_club_id'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $score_home = $_POST['score_home'];
        $score_away = $_POST['score_away'];

        // Insert query to add a game
        $stmt = $conn->prepare("INSERT INTO game (league_id, home_club_id, away_club_id, date, time, score_home, score_away, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisssss", $league_id, $home_club_id, $away_club_id, $date, $time, $score_home, $score_away, $name);
        $stmt->execute();
    }

    // Handling form submission for removing a game
    if (isset($_POST['action']) && $_POST['action'] == 'remove') {
        $game_id = $_POST['id'];

        // Delete query to remove a game
        $stmt = $conn->prepare("DELETE FROM game WHERE match_id = ?");
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
    }

    // Handling form submission for updating a game
    if (isset($_POST['action']) && $_POST['action'] == 'update') {
        $match_id = $_POST['match_id'];
        $league_id = $_POST['league_id'];
        $home_club_id = $_POST['home_club_id'];
        $away_club_id = $_POST['away_club_id'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $score_home = $_POST['score_home'];
        $score_away = $_POST['score_away'];

        $stmt = $conn->prepare("UPDATE game SET league_id = ?, home_club_id = ?, away_club_id = ?, date = ?, time = ?, score_home = ?, score_away = ? WHERE match_id = ?");
        $stmt->bind_param("iiissssi", $league_id, $home_club_id, $away_club_id, $date, $time, $score_home, $score_away, $match_id);

        if ($stmt->execute()) {
            if (isset($_POST['ajax'])) {
                echo json_encode(['success' => true]);
                exit;
            }
        } else {
            if (isset($_POST['ajax'])) {
                echo json_encode(['success' => false, 'error' => $stmt->error]);
                exit;
            }
        }
    }
}

// Query to fetch existing games
$games = [];
$sql = "SELECT match_id, 
                l.league_name, 
                ch.c_name AS HomeClub, 
                ca.c_name AS AwayClub, 
                g.date, 
                g.time, 
                g.score_home, 
                g.score_away, 
                g.created_by 
            FROM game g 
            INNER JOIN league l ON l.league_id = g.league_id
            INNER JOIN club ch ON ch.club_id = g.home_club_id
            INNER JOIN club ca ON ca.club_id = g.away_club_id
             WHERE g.created_by = ?;";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $name);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $games[] = $row;
}

// Handling edit form (populating with existing data)
$edit_game = null;
if (isset($_GET['edit'])) {
    $match_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM game WHERE match_id = ?");
    $stmt->bind_param("i", $match_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_game = $result->fetch_assoc();
}

// Query to get unique player names from the database (corrected)
$query = "SELECT DISTINCT club_id, c_name FROM club WHERE created_by = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add or Edit Game</title>
    <link rel="stylesheet" href="../assests/css/navbar.css">
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

<script>
    function toggleMenu() {
        const navLinks = document.getElementById('nav-links');
        navLinks.classList.toggle('active');
    }
</script>
<h2>Add Game</h2>
<div class="section-container">
    <!-- Add Game Form -->
    <div class="section">
        <form method="POST" action="add_game.php">
            <input type="hidden" name="action" value="add">
            <label for="league_id">League ID:</label>
            <select name="league_id" id="league_id" required>
                <option value="">Select a League</option>
                <?php
                $league_stmt = $conn->prepare("SELECT league_id, league_name FROM league WHERE userid = '".$_SESSION['userId']."'");
                $league_stmt->execute();
                $league_result = $league_stmt->get_result();
                while ($league = $league_result->fetch_assoc()) {
                    echo "<option value='" . $league['league_id'] . "'>" . $league['league_name'] . "</option>";
                }
                ?>
                </select>

         
            <!-- Dropdown for Home Club -->
            <label for="home_club_id">Select a Home Club:</label>
            <select name="home_club_id" id="home_club_id" required>
                <option value="">--Select Club--</option>
                <?php
                // Loop through the result and create options for the dropdown
                while ($row = $result->fetch_assoc()) {
                    $club_id = htmlspecialchars($row['club_id']);
                    $c_name = htmlspecialchars($row['c_name']);
                    echo "<option value='$club_id'>$c_name</option>";
                }
                ?>
            </select>

            <!-- Dropdown for Away Club -->
            <label for="away_club_id">Select an Away Club:</label>
            <select name="away_club_id" id="away_club_id" required>
                <option value="">--Select Club--</option>
                <?php
                // Loop through the result and create options for the dropdown
                $result->data_seek(0);  // Reset pointer to start
                while ($row = $result->fetch_assoc()) {
                    $club_id = htmlspecialchars($row['club_id']);
                    $c_name = htmlspecialchars($row['c_name']);
                    echo "<option value='$club_id'>$c_name</option>";
                }
                ?>
            </select>

            <label for="date">Date:</label>
            <input type="date" name="date" required>

            <label for="time">Time:</label>
            <input type="time" name="time" required>

            <label for="score_home">Home Score:</label>
            <input type="text" name="score_home">

            <label for="score_away">Away Score:</label>
            <input type="text" name="score_away">

            <input type="submit" value="Add Game">
        </form>
    </div>
</div>

    <!-- Existing Games Table -->
    <h2>Existing Games</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Match ID</th>
                <th>League ID</th>
                <th>Home Club ID</th>
                <th>Away Club ID</th>
                <th>Date</th>
                <th>Time</th>
                <th>Home Score</th>
                <th>Away Score</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
    <?php foreach ($games as $game) : ?>
        <tr id="game_<?php echo $game['match_id']; ?>">
            <td><?php echo htmlspecialchars($game['match_id']); ?></td>
            <td>
                <span class="display-value"><?php echo htmlspecialchars($game['league_name']); ?></span>
                <input type="number" class="edit-input" value="<?php echo htmlspecialchars($game['league_name']); ?>" style="display: none;">
            </td>
            <td>
                <span class="display-value"><?php echo htmlspecialchars($game['HomeClub']); ?></span>
                <input type="number" class="edit-input" value="<?php echo htmlspecialchars($game['HomeClub']); ?>" style="display: none;">
            </td>
            <td>
                <span class="display-value"><?php echo htmlspecialchars($game['AwayClub']); ?></span>
                <input type="number" class="edit-input" value="<?php echo htmlspecialchars($game['AwayClub']); ?>" style="display: none;">
            </td>
            <td>
                <span class="display-value"><?php echo htmlspecialchars($game['date']); ?></span>
                <input type="date" class="edit-input" value="<?php echo htmlspecialchars($game['date']); ?>" style="display: none;">
            </td>
            <td>
                <span class="display-value"><?php echo htmlspecialchars($game['time']); ?></span>
                <input type="time" class="edit-input" value="<?php echo htmlspecialchars($game['time']); ?>" style="display: none;">
            </td>
            <td>
                <span class="display-value"><?php echo htmlspecialchars($game['score_home']); ?></span>
                <input type="text" class="edit-input" value="<?php echo htmlspecialchars($game['score_home']); ?>" style="display: none;">
            </td>
            <td>
                <span class="display-value"><?php echo htmlspecialchars($game['score_away']); ?></span>
                <input type="text" class="edit-input" value="<?php echo htmlspecialchars($game['score_away']); ?>" style="display: none;">
            </td>
            <td>
                <button class="edit-btn" onclick="toggleEdit(this, <?php echo $game['match_id']; ?>)">Edit</button>
                <button class="save-btn" onclick="saveChanges(this, <?php echo $game['match_id']; ?>)" style="display: none;">Save</button>
                <button class="cancel-btn" onclick="cancelEdit(this, <?php echo $game['match_id']; ?>)" style="display: none;">Cancel</button>
                <form method="POST" action="add_game.php" onsubmit="return confirm('Are you sure you want to remove this record?');" style="display:inline;">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="id" value="<?php echo $game['match_id']; ?>">
                    <input type="submit" value="Remove">
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
    </table>

    <!-- Edit Game Form (This appears only when the Edit button is clicked) -->
    <?php if ($edit_game) : ?>
        <h2>Edit Game</h2>
        <div class="section">
            <form method="POST" action="add_game.php">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="match_id" value="<?php echo $edit_game['match_id']; ?>">
                <label for="league_id">League ID:</label>
                <input type="number" name="league_id" value="<?php echo $edit_game['league_id']; ?>" required>
                <label for="home_club_id">Home Club ID:</label>
                <input type="number" name="home_club_id" value="<?php echo $edit_game['home_club_id']; ?>" required>
                <label for="away_club_id">Away Club ID:</label>
                <input type="number" name="away_club_id" value="<?php echo $edit_game['away_club_id']; ?>" required>
                <label for="date">Date:</label>
                <input type="date" name="date" value="<?php echo $edit_game['date']; ?>" required>
                <label for="time">Time:</label>
                <input type="time" name="time" value="<?php echo $edit_game['time']; ?>" required>
                <label for="score_home">Home Score:</label>
                <input type="text" name="score_home" value="<?php echo $edit_game['score_home']; ?>">
                <label for="score_away">Away Score:</label>
                <input type="text" name="score_away" value="<?php echo $edit_game['score_away']; ?>">
                <input type="submit" value="Update Game">
            </form>
        </div>
    <?php endif; ?>
</div>
<script>
function toggleEdit(button, id) {
    const row = document.getElementById(`game_${id}`);
    const displayValues = row.getElementsByClassName('display-value');
    const editInputs = row.getElementsByClassName('edit-input');
    const editBtn = row.querySelector('.edit-btn');
    const saveBtn = row.querySelector('.save-btn');
    const cancelBtn = row.querySelector('.cancel-btn');

    Array.from(displayValues).forEach(span => span.style.display = 'none');
    Array.from(editInputs).forEach(input => input.style.display = 'inline-block');

    editBtn.style.display = 'none';
    saveBtn.style.display = 'inline-block';
    cancelBtn.style.display = 'inline-block';
}

function cancelEdit(button, id) {
    const row = document.getElementById(`game_${id}`);
    const displayValues = row.getElementsByClassName('display-value');
    const editInputs = row.getElementsByClassName('edit-input');
    const editBtn = row.querySelector('.edit-btn');
    const saveBtn = row.querySelector('.save-btn');
    const cancelBtn = row.querySelector('.cancel-btn');

    Array.from(displayValues).forEach(span => span.style.display = 'inline');
    Array.from(editInputs).forEach((input, index) => {
        input.style.display = 'none';
        input.value = displayValues[index].textContent.trim();
    });

    editBtn.style.display = 'inline-block';
    saveBtn.style.display = 'none';
    cancelBtn.style.display = 'none';
}

function saveChanges(button, id) {
    const row = document.getElementById(`game_${id}`);
    const editInputs = row.getElementsByClassName('edit-input');
    
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('match_id', id);
    formData.append('league_id', editInputs[0].value);
    formData.append('home_club_id', editInputs[1].value);
    formData.append('away_club_id', editInputs[2].value);
    formData.append('date', editInputs[3].value);
    formData.append('time', editInputs[4].value);
    formData.append('score_home', editInputs[5].value);
    formData.append('score_away', editInputs[6].value);

    fetch('add_game.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        try {
            // Update display values
            const displayValues = row.getElementsByClassName('display-value');
            Array.from(editInputs).forEach((input, index) => {
                displayValues[index].textContent = input.value;
            });
            
            // Reset display
            cancelEdit(button, id);
            alert('Game updated successfully!');
        } catch (error) {
            console.error('Error:', error);
            alert('Error updating game');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating game');
    });
}
</script>
</body>
</html>