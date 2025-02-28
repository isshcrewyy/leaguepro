<?php
// Database connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "leaguedb";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the club_id from the URL
$club_id = isset($_GET['club_id']) ? (int)$_GET['club_id'] : 0;

// Fetch Players for the selected Club
$players = [];
if ($club_id > 0) {
    $playersQuery = "SELECT p_name, age, position FROM player WHERE club_id = $club_id";
    $playersResult = $conn->query($playersQuery);
    if ($playersResult && $playersResult->num_rows > 0) {
        while ($player = $playersResult->fetch_assoc()) {
            $players[] = $player;
        }
    }
}

$conn->close();

// Return the players as JSON
header('Content-Type: application/json');
echo json_encode($players);
?>