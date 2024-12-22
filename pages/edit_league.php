<?php
session_start();
include('db_connection.php'); // Include your database connection file

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

// Fetch league details when a league is selected via AJAX
if (isset($_GET['league_id'])) {
    $league_id = $_GET['league_id'];

    $query = "SELECT * FROM league WHERE league_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $league_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
        exit();
    } else {
        echo json_encode(null);
        exit();
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $league_id = $_POST['league_id'] ?? null;

    if (!$league_id) {
        echo "Please select a league.";
        exit();
    }

    if (isset($_POST['update'])) {
        $league_name = $_POST['league_name'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $season = $_POST['season'];

        if ($league_name && $start_date && $end_date && $season) {
            $update_query = "UPDATE league SET league_name = ?, start_date = ?, end_date = ?, season = ? WHERE league_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("sssii", $league_name, $start_date, $end_date, $season, $league_id);

            if ($update_stmt->execute()) {
                echo "League details updated successfully!";
            } else {
                echo "Error updating league: " . $conn->error;
            }
        } else {
            echo "All fields are required for updating.";
        }
    } elseif (isset($_POST['delete_league'])) {
        // Delete league
        $delete_query = "DELETE FROM league WHERE league_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $league_id);

        if ($delete_stmt->execute()) {
            if ($delete_stmt->affected_rows > 0) {
                echo "League deleted successfully!";
            } else {
                echo "No league found with that ID.";
            }
        } else {
            echo "Error deleting league: " . $conn->error;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit or Remove League</title>
    <link rel="stylesheet" href="../assests/css/leagueEdit.css"> <!-- Include your CSS file -->
    <script>
        // Fetch league details dynamically on dropdown change
        function fetchLeagueDetails(leagueId) {
            if (leagueId) {
                fetch(`edit_league.php?league_id=${leagueId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data) {
                            document.getElementById("league_name").value = data.league_name;
                            document.getElementById("start_date").value = data.start_date;
                            document.getElementById("end_date").value = data.end_date;
                            document.getElementById("season").value = data.season;
                            document.getElementById("update_fields").style.display = "block";
                        } else {
                            alert("No details found for the selected league.");
                            document.getElementById("update_fields").style.display = "none";
                        }
                    })
                    .catch(error => console.error('Error fetching league details:', error));
            } else {
                document.getElementById("update_fields").style.display = "none";
            }
        }

        // Disable input validation for delete action
        function disableFieldsForDelete() {
            document.getElementById("league_name").disabled = true;
            document.getElementById("start_date").disabled = true;
            document.getElementById("end_date").disabled = true;
            document.getElementById("season").disabled = true;
        }

        // Re-enable fields after delete (in case user cancels action)
        function enableFields() {
            document.getElementById("league_name").disabled = false;
            document.getElementById("start_date").disabled = false;
            document.getElementById("end_date").disabled = false;
            document.getElementById("season").disabled = false;
        }
    </script>
</head>
<body>
    
<nav class="navbar">
        <a href="org_dashboard.php" class="logo">Organizer</a>
        <span class="menu-toggle" onclick="toggleMenu()">☰</span>
        <ul id="nav-links">
                <li><a href="edit_league.php">Edit League</a></li>
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
        <h2>Edit or Remove League</h2>
        
        <form action="edit_league.php" method="POST">
            <!-- Dropdown for selecting league -->
            <label for="league">Select League:</label>
            <select id="league" name="league_id" required onchange="fetchLeagueDetails(this.value)">
                <option value="">Select a League</option>
                <?php
                    // Fetch leagues for the dropdown
                    $query = "SELECT league_id, league_name FROM league";
                    $result = $conn->query($query);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['league_id'] . "'>" . htmlspecialchars($row['league_name']) . "</option>";
                        }
                    } else {
                        echo "<option value=''>No leagues available</option>";
                    }
                ?>
            </select>

            <!-- Update League Fields (Hidden Initially) -->
            <div id="update_fields" style="display:none;">
                <label for="league_name">League Name:</label>
                <input type="text" id="league_name" name="league_name" placeholder="Enter League Name" required>

                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" required>

                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date" required>

                <label for="season">Season:</label>
                <input type="text" id="season" name="season" placeholder="Enter Season" required>
            </div>

            <!-- Buttons -->
            <div class="button-container">
                <button type="submit" class="update" name="update">Update League</button>
                <button type="submit" class="delete" name="delete_league" 
                        onclick="disableFieldsForDelete(); return confirm('Are you sure you want to delete this league?');">Delete League</button>
            </div>
        </form>
    </div>
</body>
</html>
