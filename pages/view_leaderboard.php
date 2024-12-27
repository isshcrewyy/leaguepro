<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "leaguedb"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch available leagues for the dropdown
$leagueQuery = "SELECT league_id, league_name FROM league";
$leagueResult = $conn->query($leagueQuery);

// Handle form submission to get the selected league
$selected_league = isset($_GET['league_id']) ? $_GET['league_id'] : null;

// Fetch leaderboard details for the selected league
$result = null;
$stmt = null; // Initialize the statement variable

if ($selected_league) {
    $sql = "SELECT * FROM leaderboard WHERE league_id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $selected_league); // Bind league ID
        $stmt->execute();
        $result = $stmt->get_result();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <style>
        /* General page layout */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    color: #333;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    height: 100vh;
}

/* Header styling */
h2 {
    text-align: center;
    color: #2c3e50;
    margin-top: 20px;
}

/* Form styling */
form {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin: 20px 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 80%;
    max-width: 500px;
}

form label {
    font-size: 18px;
    margin-bottom: 10px;
    color: #2c3e50;
}

form select {
    padding: 10px;
    font-size: 16px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    width: 100%;
    max-width: 300px;
}

form button {
    padding: 10px 20px;
    background-color: #2980b9;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease;
}

form button:hover {
    background-color: #3498db;
}

/* Table styling */
table {
    width: 80%;
    max-width: 900px;
    margin-top: 30px;
    border-collapse: collapse;
    border-radius: 8px;
    overflow: hidden;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

table, th, td {
    border: 1px solid #ccc;
}

th, td {
    padding: 12px;
    text-align: left;
    font-size: 16px;
}

th {
    background-color: #2980b9;
    color: white;
}

tr:nth-child(even) {
    background-color: #f9f9f9;
}

tr:hover {
    background-color: #ecf0f1;
}

/* Responsive styling */
@media (max-width: 768px) {
    form {
        width: 90%;
    }

    table {
        width: 100%;
        font-size: 14px;
    }

    th, td {
        padding: 10px;
    }
}

    </style>
</head>
<body>

<h2>Select League</h2>

<form action="" method="get">
    <label for="league_id">Choose a League:</label>
    <select name="league_id" id="league_id">
        <option value="">--Select a League--</option>
        <?php
        // Populate the dropdown with available leagues
        if ($leagueResult->num_rows > 0) {
            while ($league = $leagueResult->fetch_assoc()) {
                $selected = $league['league_id'] == $selected_league ? 'selected' : '';
                echo "<option value='{$league['league_id']}' $selected>{$league['league_name']}</option>";
            }
        }
        ?>
    </select>
    <button type="submit">View Leaderboard</button>
</form>

<?php
// Display leaderboard if a league is selected
if ($result && $result->num_rows > 0) {
    echo "<h2>Leaderboard for Selected League</h2>";
    echo "<table border='1'>
            <tr>
                <th>Matches Played</th>
                <th>Wins</th>
                <th>Losses</th>
                <th>Draws</th>
                <th>Goals Scored</th>
                <th>Goals Against</th>
                <th>Goal Difference</th>
                <th>Points</th>
            </tr>";

    // Fetch and display each row of data
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['matches_played']}</td>
                <td>{$row['wins']}</td>
                <td>{$row['losses']}</td>
                <td>{$row['draws']}</td>
                <td>{$row['goals_scored']}</td>
                <td>{$row['goals_against']}</td>
                <td>{$row['goal_difference']}</td>
                <td>{$row['points']}</td>
              </tr>";
    }

    echo "</table>";
} elseif ($selected_league) {
    echo "No leaderboard data found for the selected league.";
}

// Close the statement and connection if they exist
if ($stmt) {
    $stmt->close();
}
$conn->close();
?>

</body>
</html>
