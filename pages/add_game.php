<?php


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../assests/css/navbar.css">
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
 <h2>Add Game</h2>
<div class="section-container">
    <div class="section">
        <form method="POST" action="org_dashboard.php">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="table" value="game">
            <label for="league_id">League ID:</label>
            <input type="number" name="league_id" required>
            <label for="home_club_id">Home Club ID:</label>
            <input type="number" name="home_club_id" required>
            <label for="away_club_id">Away Club ID:</label>
            <input type="number" name="away_club_id" required>
            <label for="date">Date:</label>
            <input type="date" name="date" required>
            <label for="time">Time:</label>
            <input type="time" name="time" required>
            <label for="score_home">Home Score:</label>
            <input type="text" name="score_home" >
            <label for="score_away">Away Score:</label>
            <input type="text" name="score_away" >
            <input type="submit" value="Add Game">
        </form>
    </div>
    <h2>Remove Game</h2>
    <div class="remove-section">
        <form method="POST" action="org_dashboard.php" onsubmit="return confirm('Are you sure you want to remove this record?');">
            <input type="hidden" name="action" value="remove">
            <input type="hidden" name="table" value="game">
            <label for="id">Game ID:</label>
            <input type="number" name="id" required>
            <input type="submit" value="Remove Game">
        </form>
    </div>
</div>

    </body>

    </html>