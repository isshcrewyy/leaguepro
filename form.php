<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['organizer_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Database connection
$host = "localhost";
$user = "your_username"; // Change to your database username
$password = "your_password"; // Change to your database password
$dbname = "leaguedb"; // Change to your database name

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $table = $_POST['table'];

    if ($table == 'club') {
        $name = $_POST['name'];
        $location = $_POST['location'];
        $established_year = $_POST['established_year'];
        $organizer_id = $_SESSION['organizer_id'];
        
        $sql = "INSERT INTO clubs (name, location, established_year, organizer_id) VALUES ('$name', '$location', '$established_year', '$organizer_id')";
    } elseif ($table == 'coach') {
        $name = $_POST['name'];
        $club_id = $_POST['club_id'];
        $experience = $_POST['experience'];
        $nationality = $_POST['nationality'];
        
        $sql = "INSERT INTO coaches (name, club_id, experience, nationality) VALUES ('$name', '$club_id', '$experience', '$nationality')";
    } elseif ($table == 'game') {
        $league_id = $_POST['league_id'];
        $home_team_id = $_POST['home_team_id'];
        $away_team_id = $_POST['away_team_id'];
        $match_date = $_POST['match_date'];
        $score_home = $_POST['score_home'];
        $score_away = $_POST['score_away'];
        
        $sql = "INSERT INTO games (league_id, home_team_id, away_team_id, match_date, score_home, score_away) VALUES ('$league_id', '$home_team_id', '$away_team_id', '$match_date', '$score_home', '$score_away')";
    } elseif ($table == 'leaderboard') {
        $league_id = $_POST['league_id'];
        $club_id = $_POST['club_id'];
        $points = $_POST['points'];
        $wins = $_POST['wins'];
        $losses = $_POST['losses'];
        $draws = $_POST['draws'];
        
        $sql = "INSERT INTO leaderboards (league_id, club_id, points, wins, losses, draws) VALUES ('$league_id', '$club_id', '$points', '$wins', '$losses', '$draws')";
    } elseif ($table == 'league') {
        $name = $_POST['name'];
        $season = $_POST['season'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $organizer_id = $_SESSION['organizer_id'];
        
        $sql = "INSERT INTO leagues (name, season, start_date, end_date, organizer_id) VALUES ('$name', '$season', '$start_date', '$end_date', '$organizer_id')";
    } elseif ($table == 'player') {
        $name = $_POST['name'];
        $club_id = $_POST['club_id'];
        $position = $_POST['position'];
        $age = $_POST['age'];
        $nationality = $_POST['nationality'];
        
        $sql = "INSERT INTO players (name, club_id, position, age, nationality) VALUES ('$name', '$club_id', '$position', '$age', '$nationality')";
    }

    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Form</title>
</head>
<body>
    <h1>Input Data for LeaguePro</h1>
    <form method="post" action="">
        <label for="table">Select Entity:</label>
        <select name="table" id="table" required onchange="showForm(this.value)">
            <option value="" disabled selected>Select an entity</option>
            <option value="club">Club</option>
            <option value="coach">Coach</option>
            <option value="game">Game</option>
            <option value="leaderboard">Leaderboard</option>
            <option value="league">League</option>
            <option value="player">Player</option>
        </select>

        <div id="formFields"></div>

        <button type="submit">Submit</button>
    </form>

    <script>
        function showForm(entity) {
            const formFields = document.getElementById('formFields');
            formFields.innerHTML = ''; // Clear previous fields

            if (entity === 'club') {
                formFields.innerHTML = `
                    <h2>Add Club</h2>
                    <label>Name:</label>
                    <input type="text" name="name" required><br>
                    <label>Location:</label>
                    <input type="text" name="location"><br>
                    <label>Established Year:</label>
                    <input type="number" name="established_year" min="1900" max="2100"><br>
                `;
            } else if (entity === 'coach') {
                formFields.innerHTML = `
                    <h2>Add Coach</h2>
                    <label>Name:</label>
                    <input type="text" name="name" required><br>
                    <label>Club ID:</label>
                    <input type="number" name="club_id" required><br>
                    <label>Experience (Years):</label>
                    <input type="number" name="experience"><br>
                    <label>Nationality:</label>
                    <input type="text" name="nationality"><br>
                `;
            } else if (entity === 'game') {
                formFields.innerHTML = `
                    <h2>Add Game</h2>
                    <label>League ID:</label>
                    <input type="number" name="league_id" required><br>
                    <label>Home Team ID:</label>
                    <input type="number" name="home_team_id" required><br>
                    <label>Away Team ID:</label>
                    <input type="number" name="away_team_id" required><br>
                    <label>Match Date:</label>
                    <input type="datetime-local" name="match_date" required><br>
                    <label>Score Home:</label>
                    <input type="number" name="score_home"><br>
                    <label>Score Away:</label>
                    <input type="number" name="score_away"><br>
                `;
            } else if (entity === 'leaderboard') {
                formFields.innerHTML = `
                    <h2>Add Leaderboard Entry</h2>
                    <label>League ID:</label>
                    <input type="number" name="league_id" required><br>
                    <label>Club ID:</label>
                    <input type="number" name="club_id" required><br>
                    <label>Points:</label>
                    <input type="number" name="points" required><br>
                    <label>Wins:</label>
                    <input type="number" name="wins"><br>
                    <label>Losses:</label>
                    <input type="number" name="losses"><br>
                    <label>Draws:</label>
                    <input type="number" name="draws"><br>
                `;
            } else if (entity === 'league') {
                formFields.innerHTML = `
                    <h2>Add League</h2>
                    <label>Name:</label>
                    <input type="text" name="name" required><br>
                    <label>Season:</label>
                    <input type="text" name="season"><br>
                    <label>Start Date:</label>
                    <input type="date" name="start_date" required><br>
                    <label>End Date:</label>
                    <input type="date" name="end_date" required><br>
                `;
            } else if (entity === 'player') {
                formFields.innerHTML = `
                    <h2>Add Player</h2>
                    <label>Name:</label>
                    <input type="text" name="name" required><br>
                    <label>Club ID:</label>
                    <input type="number" name="club_id" required><br>
                    <label>Position:</label>
                    <input type="text" name="position"><br>
                    <label>Age:</label>
                    <input type="number" name="age"><br>
                    <label>Nationality:</label>
                    <input type="text" name="nationality"><br>
                `;
            }
        }
    </script>
</body>
</html>
