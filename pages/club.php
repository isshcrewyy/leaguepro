<?php
// Start the session
session_start();
$name = $_SESSION['name'];
$userId = $_SESSION['userId'];

// Ensure that the user is logged in
if (!isset($_SESSION['userId'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Connect to the database
require 'db_connection.php';

// Check if the form is submitted to add a new club
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_club'])) {
    $clubName = $_POST['c_name'];
    $leagueId = $_POST['league_id'];
    $location = $_POST['location'];
    $userId = $_SESSION['userId'];

    $_SESSION['league_id'] = $leagueId;

    $sql = "SELECT COUNT(*) AS club_count FROM club WHERE created_by = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $club_count = $row['club_count'];

    if ($club_count >= 9) {
        echo "<script>alert('You can only add up to 9 clubs!');</script>";
    } else {
        // Insert the new club into the database
        $stmt = $conn->prepare("INSERT INTO club (c_name, league_id, location, created_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sisi", $clubName, $leagueId, $location, $userId);
        $stmt->execute();
        $stmt->close();

        // Redirect to the same page to prevent form resubmission
        header("Location: club.php");
        exit();
    }
}

// Check if a club should be deleted
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];

    // Delete the club from the database
    $deleteStmt = $conn->prepare("DELETE FROM club WHERE club_id = ?");
    $deleteStmt->bind_param("i", $deleteId);
    $deleteStmt->execute();
    $deleteStmt->close();

    // Redirect to the same page after deletion
    header("Location: club.php");
    exit();
}

// Check if the edit form is submitted
if (isset($_POST['update_club'])) {
    $clubId = $_POST['club_id'];
    $clubName = $_POST['c_name'];
    $location = $_POST['location'];

    // Update the club details in the database
    $updateStmt = $conn->prepare("UPDATE club SET c_name = ?, location = ? WHERE club_id = ?");
    $updateStmt->bind_param("ssi", $clubName, $location, $clubId);

    if ($updateStmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $updateStmt->error]);
    }
    $updateStmt->close();
    exit();
}

// Fetch clubs from the database
$sql = "SELECT c.club_id, c.c_name, c.league_id, c.location, l.league_name FROM club c JOIN league l ON c.league_id = l.league_id WHERE c.created_by = $userId";
$club_stmt = $conn->prepare($sql);
$club_stmt->execute();
$club_result = $club_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Management</title>
    <link rel="stylesheet" href="../assests/css/club.css">
</head>
<body>

<nav class="navbar">
    <a href="org_dashboard.php" class="logo">Organizer</a>
    <span class="menu-toggle" onclick="toggleMenu()">☰</span>
    <ul id="nav-links">
        <li><a href="club.php">Your Clubs</a></li>
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
<h1>Club Management</h1>

<!-- Add New Club Form -->
<h2>Add New Club</h2>
<div class="section-container">
    <div class="section">
        <form action="club.php" method="post">
            <label for="c_name">Club Name:</label>
            <input type="text" name="c_name" id="c_name" required>

            <label for="league_id">League ID:</label>
            <select name="league_id" id="league_id" required>
                <option value="">Select a League</option>
                <?php
                $league_stmt = $conn->prepare("SELECT league_id, league_name FROM league WHERE userid = '".$_SESSION['userId']."'");
                $league_stmt->execute();
                $league_result = $league_stmt->get_result();
                while ($league = $league_result->fetch_assoc()) {
                    echo "<option value='" . $league['league_id'] . "'>" . $league['league_name'] . "</option>";
                }
                ?>
            </select>
            <label for="location">Location:</label>
            <input type="text" name="location" id="location" required>

            <button type="submit" name="add_club">Add Club</button>
        </form>
    </div>
</div>

<!-- Display List of Clubs -->
<h2>Club List</h2>
<table>
    <thead>
        <tr>
            <th>Club ID</th>
            <th>Club Name</th>
            <th>League Name</th>
            <th>Location</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($club = $club_result->fetch_assoc()): ?>
        <tr id="club_<?php echo $club['club_id']; ?>">
            <td><?php echo htmlspecialchars($club['club_id']); ?></td>
            <td>
                <span class="display-value"><?php echo htmlspecialchars($club['c_name']); ?></span>
                <input type="text" class="edit-input" value="<?php echo htmlspecialchars($club['c_name']); ?>" style="display: none;">
            </td>
            <td>
                <span class="display-value"><?php echo htmlspecialchars($club['league_name']); ?></span>
            </td>
            <td>
                <span class="display-value"><?php echo htmlspecialchars($club['location']); ?></span>
                <input type="text" class="edit-input" value="<?php echo htmlspecialchars($club['location']); ?>" style="display: none;">
            </td>
            <td>
                <button class="edit-btn" onclick="toggleEdit(this, <?php echo $club['club_id']; ?>)">Edit</button>
                <button class="save-btn" onclick="saveChanges(this, <?php echo $club['club_id']; ?>)" style="display: none;">Save</button>
                <button class="cancel-btn" onclick="cancelEdit(this, <?php echo $club['club_id']; ?>)" style="display: none;">Cancel</button>
                <a href="club.php?delete_id=<?php echo $club['club_id']; ?>" onclick="return confirm('Are you sure you want to delete this club?')" class="delete-btn">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<script>
function toggleEdit(button, id) {
    const row = document.getElementById(`club_${id}`);
    const displayValues = row.getElementsByClassName('display-value');
    const editInputs = row.getElementsByClassName('edit-input');
    const editBtn = row.querySelector('.edit-btn');
    const saveBtn = row.querySelector('.save-btn');
    const cancelBtn = row.querySelector('.cancel-btn');

    Array.from(displayValues).forEach((span, index) => {
        if (index === 0 || index === 2) { // Only hide name and location fields
            span.style.display = 'none';
        }
    });
    Array.from(editInputs).forEach((input, index) => {
        if (index === 0 || index === 1) { // Only show name and location fields
            input.style.display = 'inline-block';
        }
    });

    editBtn.style.display = 'none';
    saveBtn.style.display = 'inline-block';
    cancelBtn.style.display = 'inline-block';
}

function cancelEdit(button, id) {
    const row = document.getElementById(`club_${id}`);
    const displayValues = row.getElementsByClassName('display-value');
    const editInputs = row.getElementsByClassName('edit-input');
    const editBtn = row.querySelector('.edit-btn');
    const saveBtn = row.querySelector('.save-btn');
    const cancelBtn = row.querySelector('.cancel-btn');

    Array.from(displayValues).forEach((span, index) => {
        if (index === 0 || index === 2) { // Only show name and location fields
            span.style.display = 'inline';
        }
    });
    Array.from(editInputs).forEach((input, index) => {
        if (index === 0 || index === 1) { // Only hide name and location fields
            input.style.display = 'none';
            input.value = displayValues[index === 0 ? 0 : 2].textContent.trim(); // Adjust index for name and location fields
        }
    });

    editBtn.style.display = 'inline-block';
    saveBtn.style.display = 'none';
    cancelBtn.style.display = 'none';
}

function saveChanges(button, id) {
    const row = document.getElementById(`club_${id}`);
    const editInputs = row.getElementsByClassName('edit-input');
    
    const formData = new FormData();
    formData.append('update_club', true);
    formData.append('club_id', id);
    formData.append('c_name', editInputs[0].value);
    formData.append('location', editInputs[1].value);

    fetch('club.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const displayValues = row.getElementsByClassName('display-value');
            Array.from(editInputs).forEach((input, index) => {
                displayValues[index === 0 ? 0 : 2].textContent = input.value; // Adjust index for name and location fields
            });
            
            cancelEdit(button, id);
            alert('Club updated successfully!');
        } else {
            alert('Error updating club: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating club');
    });
}
</script>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>