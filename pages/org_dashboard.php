<?php 
session_start();

// Check for organizer login
if (!isset($_SESSION['userId'])) {
    if (isset($_COOKIE['userId'])) {
        $_SESSION['userId'] = $_COOKIE['userId'];
    } else {
        header("Location: login.php");
        exit();
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
    <script src="scripts.js" defer></script>
</head>
<body>
    <div class="button-container-3">
        <button onclick="window.location.href='index.php'">LeaguePro</button>
    </div>

    <nav class="navbar">
        <a href="org_dashboard.php" class="logo">Organizer</a>
        <span class="menu-toggle" onclick="toggleMenu()">☰</span>
        <ul id="nav-links">
            <li><a href="add_league.php">Add League</a></li>
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

    <h1>Organizer Dashboard</h1>
    <h3 style="text-align: center;">
        Welcome, <?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Guest'; ?>
    </h3>
</body>
</html>
