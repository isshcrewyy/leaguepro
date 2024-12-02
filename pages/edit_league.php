<?php
// Assuming you've already connected to the database
include('db_connection.php');

// Check if the league_id is passed via URL
if (isset($_GET['league_id'])) {
    $league_id = $_GET['league_id'];

    // Fetch the league details from the database
    $stmt = $conn->prepare("SELECT * FROM league WHERE league_id = ?");
    $stmt->bind_param("i", $league_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $league = $result->fetch_assoc();

    // Check if the league exists
    if (!$league) {
        echo "League not found!";
        exit;
    }
} else {
    echo "No league ID specified!";
    exit;
}

// Handle form submission to update league details
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {
    $league_name = $_POST['league_name'];
    $season = $_POST['season'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Update league details in the database
    $stmt = $conn->prepare("UPDATE league SET league_name = ?, season = ?, start_date = ?, end_date = ? WHERE league_id = ?");
    $stmt->bind_param("ssssi", $league_name, $season, $start_date, $end_date, $league_id);

    if ($stmt->execute()) {
        echo '<script>alert("League updated successfully."); window.location.href = "add_league.php";</script>';
    } else {
        echo '<script>alert("Failed to update league: ' . $stmt->error . '");</script>';
    }

    $stmt->close();
}
?>

<h2>Edit League</h2>

<!-- Form to Edit League Details -->
<form method="POST" action="edit_league.php?league_id=<?php echo $league['league_id']; ?>">
    <input type="hidden" name="action" value="update">

    <label for="league_name">League Name:</label>
    <input type="text" name="league_name" value="<?php echo htmlspecialchars($league['league_name']); ?>" required><br><br>

    <label for="season">Season:</label>
    <input type="text" name="season" value="<?php echo htmlspecialchars($league['season']); ?>" required><br><br>

    <label for="start_date">Start Date:</label>
    <input type="date" name="start_date" value="<?php echo htmlspecialchars($league['start_date']); ?>" required><br><br>

    <label for="end_date">End Date:</label>
    <input type="date" name="end_date" value="<?php echo htmlspecialchars($league['end_date']); ?>" required><br><br>

    <input type="submit" value="Update League">
</form>
