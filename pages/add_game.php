<?php
// Start the session
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['userId'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Ensure the user is approved
if (!isset($_SESSION['status']) || $_SESSION['status'] !== 'approved') {
    header("Location: org_dashboard.php");
    exit();
}
$name = $_SESSION['name'];


// Connect to the database
require 'db_connection.php';

// Get the userId from the session
$userId = $_SESSION['userId'];


  // Handle Remove Request
  if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'remove') {
    if (!isset($_POST['type']) || !isset($_POST['id'])) {
        echo "<script>alert('Invalid request!'); window.history.back();</script>";
        exit();
    }

    $type = $_POST['type'];
    $id = intval($_POST['id']); 

    if ($type === 'game') {
        $stmt = $conn->prepare("DELETE FROM game WHERE match_id = ?");
    } else {
        echo "<script>alert('Invalid type!'); window.history.back();</script>";
        exit();
    }

    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Record removed successfully!'); window.location.href = 'add_game.php';</script>";
    } else {
        echo "<script>alert('Error removing record.'); window.history.back();</script>";
    }
}

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
        $stmt->bind_param("iiissssi", $league_id, $home_club_id, $away_club_id, $date, $time, $score_home, $score_away, $userId);
        $stmt->execute();

        header("Location: " . $_SERVER['PHP_SELF']);
    exit();
    }

    // Handling form submission for removing a game
    if (isset($_POST['action']) && $_POST['action'] == 'remove') {
        $game_id = $_POST['id'];

        // Delete query to remove a game
        $stmt = $conn->prepare("DELETE FROM game WHERE match_id = ?");
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['action']) && $_POST['action'] == 'update') {
            $match_id = $_POST['match_id'];
            $score_home = $_POST['score_home'];
            $score_away = $_POST['score_away'];
    
            $stmt = $conn->prepare("UPDATE game SET score_home = ?, score_away = ? WHERE match_id = ?");
            $stmt->bind_param("iii", $score_home, $score_away, $match_id);
    
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => $stmt->error]);
            }
            exit;
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
$stmt->bind_param("s", $userId);
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
        <?php if (isset($_SESSION['status']) && $_SESSION['status'] === 'approved') : ?>
            <li><a href="club.php">Your Clubs</a></li>
            <li><a href="team.php">Your Team</a></li>
            <li><a href="add_game.php">Add Game</a></li>
            <li><a href="leaderboard.php">Leaderboard</a></li>
        <?php else : ?>
            <li><a href="#">Approval Pending...</a></li>
        <?php endif; ?>
        
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
                <th>League</th>
                <th>Home Club </th>
                <th>Away Club </th>
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
        <td><?php echo htmlspecialchars($game['league_name']); ?></td>
        <td><?php echo htmlspecialchars($game['HomeClub']); ?></td>
        <td><?php echo htmlspecialchars($game['AwayClub']); ?></td>
        <td><?php echo htmlspecialchars($game['date']); ?></td>
        <td><?php echo htmlspecialchars($game['time']); ?></td>
        <td>
            <span class="display-score" id="score_home_<?php echo $game['match_id']; ?>">
                <?php echo htmlspecialchars($game['score_home']); ?>
            </span>
            <input type="number" class="edit-score" id="edit_score_home_<?php echo $game['match_id']; ?>" value="<?php echo $game['score_home']; ?>" style="display: none;">
        </td>
        <td>
            <span class="display-score" id="score_away_<?php echo $game['match_id']; ?>">
                <?php echo htmlspecialchars($game['score_away']); ?>
            </span>
            <input type="number" class="edit-score" id="edit_score_away_<?php echo $game['match_id']; ?>" value="<?php echo $game['score_away']; ?>" style="display: none;">
        </td>
        <td>
            <button class="edit-btn" onclick="editGame(<?php echo $game['match_id']; ?>)">Edit</button>
            <button class="save-btn" onclick="saveGame(<?php echo $game['match_id']; ?>)" style="display: none;">Save</button>
            <form method="POST" action="add_game.php" onsubmit="return confirm('Are you sure you want to remove this record?');" style="display:inline;">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="type" value="game"> 
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
function editGame(match_id) {
    // Hide score display
    document.getElementById("score_home_" + match_id).style.display = "none";
    document.getElementById("score_away_" + match_id).style.display = "none";

    // Show input fields
    document.getElementById("edit_score_home_" + match_id).style.display = "inline-block";
    document.getElementById("edit_score_away_" + match_id).style.display = "inline-block";

    // Toggle buttons
    document.querySelector(`#game_${match_id} .edit-btn`).style.display = "none";
    document.querySelector(`#game_${match_id} .save-btn`).style.display = "inline-block";
}

function saveGame(match_id) {
    let score_home = document.getElementById("edit_score_home_" + match_id).value;
    let score_away = document.getElementById("edit_score_away_" + match_id).value;

    // AJAX request to update the database
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "add_game.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            let response = JSON.parse(xhr.responseText);
            if (response.success) {
                // Update UI without refreshing
                document.getElementById("score_home_" + match_id).innerText = score_home;
                document.getElementById("score_away_" + match_id).innerText = score_away;

                // Show updated scores
                document.getElementById("score_home_" + match_id).style.display = "inline-block";
                document.getElementById("score_away_" + match_id).style.display = "inline-block";

                // Hide input fields
                document.getElementById("edit_score_home_" + match_id).style.display = "none";
                document.getElementById("edit_score_away_" + match_id).style.display = "none";

                // Toggle buttons
                document.querySelector(`#game_${match_id} .edit-btn`).style.display = "inline-block";
                document.querySelector(`#game_${match_id} .save-btn`).style.display = "none";
            } else {
                alert("Error updating game: " + response.error);
            }
        }
    };
    xhr.send("action=update&match_id=" + match_id + "&score_home=" + score_home + "&score_away=" + score_away);
}

// Add this to your existing <script> section
document.addEventListener('DOMContentLoaded', function() {
    // Find all remove game forms
    const removeForms = document.querySelectorAll('form[action="add_game.php"]');
    
    removeForms.forEach(form => {
        if (form.querySelector('input[value="remove"]')) {  // Only target remove forms
            form.addEventListener('submit', function(e) {
                e.preventDefault();  // Prevent normal form submission
                
                if (confirm('Are you sure you want to remove this record?')) {
                    const formData = new FormData(this);
                    
                    fetch('add_game.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        // Find and remove the game row
                        const gameId = this.querySelector('input[name="id"]').value;
                        document.getElementById('game_' + gameId).remove();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error removing game');
                    });
                }
            });
        }
    });
});
</script>
</body>
</html>