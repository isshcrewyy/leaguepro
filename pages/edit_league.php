<?php
session_start();
include('db_connection.php'); // Include your database connection file

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['league_id'])) {
    $league_id = $_GET['league_id'];

    // Fetch league details for the selected league
    $query = "SELECT * FROM league WHERE league_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $league_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $league = $result->fetch_assoc();
        // Return the data as JSON
        echo json_encode($league);
        exit; // Exit to prevent further output
    } else {
        // Return null if no league found
        echo json_encode(null);
        exit; // Exit to prevent further output
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $league_id = $_POST['league_id'];
    $league_name = $_POST['league_name'];
    $location = $_POST['location'];
    $founded_year = $_POST['founded_year'];

    $update_query = "UPDATE league SET league_name = ?, location = ?, founded_year = ? WHERE league_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssii", $league_name, $location, $founded_year, $league_id);
    $update_stmt->execute();

    echo "League details updated successfully!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove'])) {
    $league_id = $_POST['league_id'];

    // Delete query for removing the league
    $delete_query = "DELETE FROM league WHERE league_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $league_id);
    $delete_stmt->execute();

    echo "League removed successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit League</title>
    <link rel="stylesheet" href="../assests/css/leagueEdit.css">
</head>
<body>
    <div class="container">
        <h1>Edit or Remove League</h1>
        <form method="POST" action="edit_league.php">
            <label for="league_id">Select League to Edit/Remove:</label>
            <select name="league_id" id="league_id" required>
                <option value="">Select a League</option>
                <?php 
                // Fetch leagues for the dropdown
                $leagues_query = "SELECT * FROM league";
                $leagues_result = $conn->query($leagues_query);
                while ($row = $leagues_result->fetch_assoc()): ?>
                    <option value="<?php echo $row['league_id']; ?>"><?php echo $row['league_name']; ?></option>
                <?php endwhile; ?>
            </select>

            <!-- Hidden edit form, initially invisible -->
            <div id="edit_form" style="display: none;">
                <label for="league_name">League Name:</label>
                <input type="text" name="league_name" id="league_name" required>

                <label for="location">Location:</label>
                <input type="text" name="location" id="location" required>

                <label for="founded_year">Founded Year:</label>
                <input type="number" name="founded_year" id="founded_year" required>
                
                <button type="submit" name="update">Update League</button>
            </div>

            <!-- Remove form -->
            <div id="remove_form" style="display: none;">
                <button type="submit" name="remove">Remove League</button>
            </div>
        </form>
    </div>

    <!-- Modal for League Details -->
    <div id="leagueModal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Edit League Details</h2>
            <form id="modal-form">
                <label for="modal_league_name">League Name:</label>
                <input type="text" id="modal_league_name" required>

                <label for="modal_location">Location:</label>
                <input type="text" id="modal_location" required>

                <label for="modal_founded_year">Founded Year:</label>
                <input type="number" id="modal_founded_year" required>
                
                <button type="button" onclick="submitModalForm()">Update League</button>
            </form>
        </div>
    </div>

    <script>
        const leagueSelect = document.getElementById('league_id');
        const editForm = document.getElementById('edit_form');
        const removeForm = document.getElementById('remove_form');
        const leagueModal = document.getElementById('leagueModal');
        const modalForm = document.getElementById('modal-form');

        leagueSelect.addEventListener('change', function() {
            const leagueId = leagueSelect.value;
            if (leagueId) {
                // Fetch the league details via Ajax to fill the modal form
                fetch(`edit_league.php?league_id=${leagueId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data) {
                            document.getElementById('modal_league_name').value = data.league_name;
                            document.getElementById('modal_location').value = data.location;
                            document.getElementById('modal_founded_year').value = data.founded_year;

                            // Show the modal with data
                            leagueModal.style.display = 'block';

                            // Show edit form and remove button
                            editForm.style.display = 'block';
                            removeForm.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching league details:', error);
                    });
            } else {
                // Hide the forms if no league is selected
                editForm.style.display = 'none';
                removeForm.style.display = 'none';
            }
        });

        // Close modal function
        function closeModal() {
            leagueModal.style.display = 'none';
        }

        // Handle form submission in the modal
        function submitModalForm() {
            const leagueName = document.getElementById('modal_league_name').value;
            const location = document.getElementById('modal_location').value;
            const foundedYear = document.getElementById('modal_founded_year').value;
            const leagueId = leagueSelect.value;

            // Submit the data to update the league
            fetch('edit_league.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `league_id=${leagueId}&league_name=${leagueName}&location=${location}&founded_year=${foundedYear}&update=true`
            })
            .then(response => response.text())
            .then(result => {
                alert('League details updated successfully!');
                closeModal();
                location.reload();
            })
            .catch(error => {
                console.error('Error updating league:', error);
            });
        }
    </script>
</body>
</html>
