<?php 
session_start();
require_once 'db_connection.php';
// Check for organizer login
if (!isset($_SESSION['userId'])) {
    if (isset($_COOKIE['userId'])) {
        $_SESSION['userId'] = $_COOKIE['userId'];
    } else {
        header("Location: login.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_league'])) {
    $league_name = trim($_POST['league_name']);
    $season = $_POST['season'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $userId = $_SESSION['userId'];

    if (empty($league_name) || empty($season) || empty($start_date) || empty($end_date)) {
        echo '<script>alert("Please fill all the fields.");</script>';
    } else {
        $stmt = $conn->prepare("INSERT INTO league (league_name, season, start_date, end_date, userId) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $league_name, $season, $start_date, $end_date, $userId);

        if ($stmt->execute()) {
            echo '<script>alert("League added successfully.");</script>';
        } else {
            echo '<script>alert("Failed to add league: ' . $stmt->error . '");</script>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Dashboard</title>
    <link rel="stylesheet" href="../assests/css/dashboardstyle.css">
    <script src="../assests/js/script.js" defer></script>
</head>
<body>
    <div class="button-container-3">
        <button onclick="window.location.href='index.php'">LeaguePro</button>
    </div>

    <!-- Updated Navbar -->
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

    <main class="dashboard-content">
        <h1>Organizer Dashboard</h1>
        <h3>Welcome, <?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Guest'; ?></h3>

        <!-- Centered Create League Button -->
        <div class="create-league-section">
            <p>Ready to manage a league? Start now!</p>
            <button class="create-league-btn" onclick="window.location.href='add_league.php'">Create Your League</button>
            <!-- Trigger Button -->
                <button onclick="showModal()" class="btn">Create Your Own League</button>

                <!-- Popup Modal -->
                <div id="leagueModal" class="modal" style="display: none;">
                    <div class="modal-content">
                        <span class="close" onclick="closeModal()">&times;</span>
                        <h2>Add New League</h2>
                        <form method="POST">
                            <label for="league_name">League Name:</label>
                            <input type="text" name="league_name" id="league_name" required>
                            <label for="season">Season:</label>
                            <input type="text" name="season" id="season" required>
                            <label for="start_date">Start Date:</label>
                            <input type="date" name="start_date" id="start_date" required>
                            <label for="end_date">End Date:</label>
                            <input type="date" name="end_date" id="end_date" required>
                            <button type="submit" name="add_league">Add League</button>
                        </form>
                    </div>
                </div>

        </div>

        <div class="section-grid">
            <section class="recent-activities">
                <h2>Recent Activities</h2>
                <!-- Empty content for now -->
            </section>

            <section class="quick-actions">
                <h2>Quick Actions</h2>
                <!-- Empty content for now -->
            </section>

            <section class="upcoming-matches">
                <h2>Upcoming Matches</h2>
                <!-- Empty content for now -->
            </section>
        </div>
    </main>

    <script>
        function toggleMenu() {
            const navLinks = document.getElementById('nav-links');
            navLinks.classList.toggle('active');
        }
    </script>
</body>
</html>
