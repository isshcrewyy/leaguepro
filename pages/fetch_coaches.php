<?php
// fetch_coaches.php
header('Content-Type: application/json');

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

// Validate club_id
if ($club_id <= 0) {
    echo json_encode([]);
    exit;
}

// Fetch Coaches for the selected Club
$coachesQuery = "SELECT co_name, age, experience FROM coach WHERE club_id = $club_id";
$coachesResult = $conn->query($coachesQuery);

$coaches = [];
if ($coachesResult && $coachesResult->num_rows > 0) {
    while ($coach = $coachesResult->fetch_assoc()) {
        $coaches[] = $coach;
    }
}

echo json_encode($coaches);
$conn->close();
?>