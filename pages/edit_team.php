<?php
// Assuming you've already connected to the database
include('db_connection.php');

// Initialize variables
$player = null;
$coach = null;

// Check if player_id or coach_id is passed via URL
if (isset($_GET['player_id'])) {
    $player_id = $_GET['player_id'];

    // Fetch player details from the database
    $stmt = $conn->prepare("SELECT * FROM player WHERE player_id = ?");
    $stmt->bind_param("i", $player_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $player = $result->fetch_assoc();

    // Check if player exists
    if (!$player) {
        echo "Player not found!";
        exit;
    }
} elseif (isset($_GET['coach_id'])) {
    $coach_id = $_GET['coach_id'];

    // Fetch coach details from the database
    $stmt = $conn->prepare("SELECT * FROM coach WHERE coach_id = ?");
    $stmt->bind_param("i", $coach_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $coach = $result->fetch_assoc();

    // Check if coach exists
    if (!$coach) {
        echo "Coach not found!";
        exit;
    }
} else {
    echo "No player or coach ID specified!";
    exit;
}

// Handle form submission for updating player or coach
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {
    if (isset($_POST['name'])) {  // Update Player
        $name = $_POST['name'];
        $position = $_POST['position'];
        $club_id = $_POST['club_id'];
        $phone_number = $_POST['phone_number'];

        // Update player details
        $stmt = $conn->prepare("UPDATE player SET name = ?, position = ?, club_id = ?, phone_number = ? WHERE player_id = ?");
        $stmt->bind_param("ssisi", $name, $position, $club_id, $phone_number, $player_id);

        if ($stmt->execute()) {
            echo '<script>alert("Player updated successfully."); window.location.href = "team.php";</script>';
        } else {
            echo '<script>alert("Failed to update player: ' . $stmt->error . '");</script>';
        }
    } elseif (isset($_POST['coach_name'])) {  // Update Coach
        $coach_name = $_POST['coach_name'];
        $age = $_POST['age'];
        $experience = $_POST['experience'];
        $club_id = $_POST['club_id'];
        $phone_number = $_POST['phone_number'];

        // Update coach details
        $stmt = $conn->prepare("UPDATE coach SET name = ?, age = ?, experience = ?, club_id = ?, phone_number = ? WHERE coach_id = ?");
        $stmt->bind_param("siissi", $coach_name, $age, $experience, $club_id, $phone_number, $coach_id);

        if ($stmt->execute()) {
            echo '<script>alert("Coach updated successfully."); window.location.href = "team.php";</script>';
        } else {
            echo '<script>alert("Failed to update coach: ' . $stmt->error . '");</script>';
        }
    }
    $stmt->close();
}
?>

<h2>Edit <?php echo isset($player) ? "Player" : "Coach"; ?></h2>

<!-- Form to Edit Player or Coach -->
<form method="POST" action="edit_team.php?<?php echo isset($player) ? 'player_id=' . $player['player_id'] : 'coach_id=' . $coach['coach_id']; ?>">
    <input type="hidden" name="action" value="update">

    <?php if (isset($player)): ?>
        <label for="name">Player Name:</label>
        <input type="text" name="player_name" value="<?php echo htmlspecialchars($player['name']); ?>" required><br><br>

        <label for="position">Position:</label>
        <input type="text" name="position" value="<?php echo htmlspecialchars($player['position']); ?>" required><br><br>

        <label for="club_id">Club:</label>
        <select name="club_id" required>
            <option value="1">Club 1</option>
        
            <?php
            // Fetch all clubs from the database
            $query = "SELECT club_id, c_name FROM club";
            $result = $conn->query($query);
            if($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $selected = $player['club_id'] == $row['club_id'] ? 'selected' : '';
                    echo '<option value="' . $row['club_id'] . '" ' . $selected . '>' . $row['c_name'] . '</option>';
                }
            }
            ?>
        </select>
        
        <!-- <input type="number" name="club_id" value="<?php echo htmlspecialchars($player['club_id']); ?>" required><br><br> -->

        <label for="phone_number">Phone Number:</label>
        <input type="text" name="phone_number" value="<?php echo htmlspecialchars($player['phone_number']); ?>" required><br><br>
    <?php elseif (isset($coach)): ?>
        <label for="coach_name">Coach Name:</label>
        <input type="text" name="coach_name" value="<?php echo htmlspecialchars($coach['name']); ?>" required><br><br>

        <label for="age">Age:</label>
        <input type="number" name="age" value="<?php echo htmlspecialchars($coach['age']); ?>" required><br><br>

        <label for="experience">Experience:</label>
        <input type="text" name="experience" value="<?php echo htmlspecialchars($coach['experience']); ?>" required><br><br>

        <label for="club_id">Club ID:</label>
        <input type="number" name="club_id" value="<?php echo htmlspecialchars($coach['club_id']); ?>" required><br><br>

        <label for="phone_number">Phone Number:</label>
        <input type="text" name="phone_number" value="<?php echo htmlspecialchars($coach['phone_number']); ?>" required><br><br>
    <?php endif; ?>

    <input type="submit" value="Update <?php echo isset($player) ? "Player" : "Coach"; ?>">
</form>
