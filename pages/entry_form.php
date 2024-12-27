<?php
// Include database connection (if not already included)
require_once('db_connection.php');

// Start session to track logged-in user
session_start();

// Check if the user is logged in (assuming the user is logged in and userId is stored in session)
if (!isset($_SESSION['userId'])) {
    // If the user is not logged in, redirect to login page or handle accordingly
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['userId'];  // Get the logged-in user's ID

if (isset($_POST['submit_form'])) {
    // Gather form inputs and sanitize them
    $league_name = $_POST['league_name'];
    $duration = $_POST['duration'];
    $max_teams = filter_var($_POST['max_teams'], FILTER_SANITIZE_NUMBER_INT);
    $one_league = $_POST['one_league'];
    $start_date = $_POST['start_date'];
    
    // Automatically calculate the end_date based on the duration
    if (!empty($start_date)) {
        $end_date = calculateEndDate($start_date, $duration);
    } else {
        $end_date = $_POST['end_date']; // fallback if start_date is not provided
    }

    $experience = filter_var($_POST['experience'], FILTER_SANITIZE_STRING);
    $location = filter_var($_POST['location'], FILTER_SANITIZE_STRING);
    $rules = filter_var($_POST['rules'], FILTER_SANITIZE_STRING);
    $season = $_POST['season']; // Assuming this is an additional field in the form for the season

    // Insert into 'form' table, including the logged-in user's ID
    $stmt = $conn->prepare("INSERT INTO form (league_name, userId, duration, max_teams, one_league, start_date, end_date, experience, location, rules) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("siisisssss", $league_name, $userId, $duration, $max_teams, $one_league, $start_date, $end_date, $experience, $location, $rules);

    if ($stmt->execute()) {
        // Insert into 'league' table with the common details
      // Insert into 'league' table with the additional details
$league_stmt = $conn->prepare("INSERT INTO league (league_name, userId, start_date, end_date, season, duration, max_teams, location) VALUES (?,?,?,?,?,?,?,?)");
$league_stmt->bind_param("sissssis", $league_name, $userId, $start_date, $end_date, $season, $duration, $max_teams, $location);

if ($league_stmt->execute()) {
    echo "<script>alert('Form and league details submitted successfully!');</script>";
    header("Location: login.php");
    exit;
} else {
    echo "<script>alert('Error inserting into the league table.');</script>";
}



        $league_stmt->close();
    } else {
        echo "<script>alert('Error submitting the form. Please try again.');</script>";
    }

    $stmt->close();
}

// Function to calculate the end_date based on the duration and start_date
function calculateEndDate($start_date, $duration) {
    $start_date_obj = new DateTime($start_date);
    switch ($duration) {
        case '1 month':
            $start_date_obj->modify("+1 month");
            break;
        case '3 months':
            $start_date_obj->modify("+3 months");
            break;
        case '6 months':
            $start_date_obj->modify("+6 months");
            break;
        case '1 year':
            $start_date_obj->modify("+1 year");
            break;
    }
    return $start_date_obj->format('Y-m-d');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entry Form</title>
    <link rel="stylesheet" href="../assests/css/entry_form.css">
</head>
<body>

<div class="container">
    <div class="card">
        <form method="POST" onsubmit="return validateDates();">
            <div class="form-group">
                <label for="league_name">League Name:</label>
                <input type="text" class="form-control" id="league_name" name="league_name" required>
            </div>
            
            <!-- Duration -->
            <label for="duration">League Duration</label>
            <select name="duration" required>
                <option value="1 month">1 Month</option>
                <option value="3 months">3 Months</option>
                <option value="6 months">6 Months</option>
                <option value="1 year">1 Year</option>
            </select>

            <!-- Maximum Teams -->
            <label for="max_teams">Maximum Teams</label>
            <select name="max_teams" required>
                <?php for ($i = 1; $i <= 9; $i++) echo "<option value=\"$i\">$i</option>"; ?>
            </select>

            <!-- One League -->
            <label for="one_league">Do you agree to manage only one league at a time?</label>
            <select name="one_league" required>
                <option value="yes">Yes</option>
                <option value="no">No</option>
            </select>

            <!-- Start Date -->
            <label for="start_date">Start Date</label>
            <input type="date" name="start_date" required>

            <!-- End Date (Auto-calculated) -->
            <label for="end_date">End Date</label>
            <input type="date" name="end_date" required readonly>

            <!-- Location -->
            <label for="location">Location</label>
            <input type="text" name="location" placeholder="Enter your location" required>

            <!-- Rules -->
            <label for="rules">Do you agree to follow the rules of LeaguePro? (Describe if necessary)</label>
            <textarea name="rules" rows="4" placeholder="Enter any additional rules..." required></textarea>

            <!-- Experience -->
            <label for="experience">Your Experience in Organizing Leagues</label>
            <textarea name="experience" rows="4" placeholder="Describe your experience..." required></textarea>

            <!-- Submit Button -->
            <button type="submit" name="submit_form">Submit</button>
        </form>
    </div>
</div>

<!-- JavaScript for auto-calculating the End Date based on Duration -->
<script>
// JavaScript to validate start and end date, and automatically calculate end date based on the selected duration
document.querySelector('[name="duration"]').addEventListener('change', function() {
    calculateEndDate();
});

document.querySelector('[name="start_date"]').addEventListener('change', function() {
    calculateEndDate();
});

function calculateEndDate() {
    const duration = document.querySelector('[name="duration"]').value;
    const startDate = document.querySelector('[name="start_date"]').value;

    if (startDate) {
        let endDate = new Date(startDate);
        switch (duration) {
            case '1 month':
                endDate.setMonth(endDate.getMonth() + 1);
                break;
            case '3 months':
                endDate.setMonth(endDate.getMonth() + 3);
                break;
            case '6 months':
                endDate.setMonth(endDate.getMonth() + 6);
                break;
            case '1 year':
                endDate.setFullYear(endDate.getFullYear() + 1);
                break;
        }
        // Set the end date in the input field
        document.querySelector('[name="end_date"]').value = formatDate(endDate);
    }
}

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}
</script>

</body>
</html>
