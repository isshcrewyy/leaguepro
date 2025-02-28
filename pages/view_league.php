<?php
// Start the session
session_start();

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "leaguedb";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the league_id and club_id from the URL
$league_id = isset($_GET['league_id']) ? (int)$_GET['league_id'] : 0;
$club_id = isset($_GET['club_id']) ? (int)$_GET['club_id'] : 0;

// Validate league_id
if ($league_id <= 0) {
    echo "<p>Invalid league ID. Please check the URL.</p>";
    exit;
}

// Fetch League Name
$leagueQuery = "SELECT league_name FROM league WHERE league_id = $league_id";
$leagueResult = $conn->query($leagueQuery);

if ($leagueResult && $leagueResult->num_rows > 0) {
    $league = $leagueResult->fetch_assoc();
} else {
    echo "<p>League not found.</p>";
    exit; // Stop the script if no league is found
}

// Fetch Clubs for the selected League
$clubsQuery = "SELECT club_id, c_name, location FROM club WHERE league_id = $league_id";
$clubsResult = $conn->query($clubsQuery);

$clubs = [];
if ($clubsResult && $clubsResult->num_rows > 0) {
    while ($club = $clubsResult->fetch_assoc()) {
        $clubs[$club['club_id']] = $club;  // Use club_id as the array key
    }
}

// Fetch Players for the selected Club (only if club_id is valid)
$players = [];
if ($club_id > 0) {
    $clubQuery = "SELECT c_name, location FROM club WHERE club_id = $club_id";
    $clubResult = $conn->query($clubQuery);
    if ($clubResult && $clubResult->num_rows > 0) {
        $club = $clubResult->fetch_assoc();

        $playersQuery = "SELECT p_name, age, position, phone_number FROM player WHERE club_id = $club_id";
        $playersResult = $conn->query($playersQuery);
        if ($playersResult && $playersResult->num_rows > 0) {
            while ($player = $playersResult->fetch_assoc()) {
                $players[] = $player;
            }
        }
    } else {
        echo "<p>Club not found.</p>";
    }
}

$coachs = [];
if ($club_id > 0){
    $clubQuery = "SELECT c_name, location FROM club WHERE club_id = $club_id";
    $clubResult = $conn->query($clubQuery);
    if ($clubResult && $clubResult->num_rows > 0) {
        $club = $clubResult->fetch_assoc();

        $coachsQuery = "SELECT co_name, age, experience FROM coach WHERE club_id = $club_id";
        $coachsResult = $conn->query($coachsQuery);
        if ($coachsResult && $coachsResult->num_rows > 0) {
            while ($coach = $coachsResult->fetch_assoc()) {
                $coachs[] = $coach;
            }
        }
    }
}

// Fetch Leaderboard for the selected League
$leaderboardQuery = "SELECT club_id, points, wins, losses, draws, goal_difference FROM leaderboard WHERE league_id = $league_id ORDER BY points DESC";
$leaderboardResult = $conn->query($leaderboardQuery);

$leaderboard = [];
if ($leaderboardResult && $leaderboardResult->num_rows > 0) {
    while ($row = $leaderboardResult->fetch_assoc()) {
        $leaderboard[] = $row;
    }
}

// Fetch Matches for the selected League
$matchesQuery = "SELECT * FROM game WHERE league_id = $league_id ORDER BY date, time ASC";
$matchesResult = $conn->query($matchesQuery);

$pastMatches = [];
$currentMatches = [];
$upcomingMatches = [];

$currentDate = date('Y-m-d');
$currentTime = date('H:i:s');

if ($matchesResult && $matchesResult->num_rows > 0) {
    while ($row = $matchesResult->fetch_assoc()) {
        $matchDate = $row['date'];
        $matchTime = $row['time'];

        if ($matchDate < $currentDate || ($matchDate === $currentDate && $matchTime < $currentTime)) {
            $pastMatches[] = $row;
        } elseif ($matchDate === $currentDate && $matchTime >= $currentTime) {
            $currentMatches[] = $row;
        } else {
            $upcomingMatches[] = $row;
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>League Fixtures</title>
    <link rel="stylesheet" href="../assests/css/style.css">
    <script>
        function showPlayers(clubId) {
            // Fetch players via AJAX
            fetch(`fetch_players.php?club_id=${clubId}`)
                .then(response => response.json())
                .then(data => {
                    const playerList = document.getElementById('player-list');
                    playerList.innerHTML = ''; // Clear existing content

                    if (data.length === 0) {
                        playerList.innerHTML = '<p>No players found for this club.</p>';
                    } else {
                        data.forEach(player => {
                            const playerItem = document.createElement('li');
                            playerItem.textContent = `Name: ${player.p_name}, Age: ${player.age}, Position: ${player.position}`;
                            playerList.appendChild(playerItem);
                        });
                    }

                    // Show the popup
                    document.getElementById('player-popup').style.display = 'block';
                })
                .catch(error => console.error('Error fetching players:', error));
        }

        function closePopup() {
            document.getElementById('player-popup').style.display = 'none';
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.hash) {
                const element = document.querySelector(window.location.hash);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    </script>

</head>
<body>
<div class="container">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb">
        <a href="index.php">Home</a> <span>&gt;</span>
        <a href="fans.php">Fans</a> <span>&gt;</span>
        <span>League Fixtures</span>
    </div>

    <header>
        <div>
            <h1>League Fixtures</h1>
            <h2>League: <?php echo htmlspecialchars($league['league_name']); ?></h2>
        </div>
        <!-- Button Group -->
        <div class="button-group">
            <a href="view_league.php?league_id=<?php echo $league_id; ?>&view=past_matches#past-matches" class="btn">Past Matches</a>
            <a href="view_league.php?league_id=<?php echo $league_id; ?>&view=upcoming_matches#upcoming-matches" class="btn">Upcoming Matches</a>
        </div>
    </header>

    <!-- Clubs Section -->
    <section>
        <h2>Clubs in this League</h2>
        <div class="card-container">
            <?php if (empty($clubs)): ?>
                <p>No clubs available in this league.</p>
            <?php else: ?>
                <?php foreach ($clubs as $club): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($club['c_name']); ?></h3>
                        <p>Location: <?php echo htmlspecialchars($club['location']); ?></p>
                        <div class="button-group">
                            <a href="javascript:void(0);" onclick="showPlayers(<?php echo $club['club_id']; ?>)">View Players</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Popup Structure -->
    <div id="player-popup" class="popup">
        <div class="popup-content">
            <span class="close" onclick="closePopup()">&times;</span>
            <h2>Players</h2>
            <ul id="player-list"></ul>
        </div>
    </div>

    <!-- Past Matches Section -->
    <?php if (isset($_GET['view']) && $_GET['view'] == 'past_matches'): ?>
    <section id="past-matches">
        <h2>Past Matches</h2>
        <div class="matches">
            <?php if (empty($pastMatches)): ?>
                <p>No past matches available.</p>
            <?php else: ?>
                <?php foreach ($pastMatches as $match): ?>
                    <div class="match-card">
                        <p><strong><?php echo htmlspecialchars($clubs[$match['home_club_id']]['c_name'] ?? 'Unknown'); ?></strong> 
                        vs <strong><?php echo htmlspecialchars($clubs[$match['away_club_id']]['c_name'] ?? 'Unknown'); ?></strong></p>
                        <p>Date: <?php echo htmlspecialchars($match['date']); ?></p>
                        <p>Time: <?php echo htmlspecialchars($match['time']); ?></p>
                        <p>Score: <?php echo htmlspecialchars($match['score_home']); ?> - <?php echo htmlspecialchars($match['score_away']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Upcoming Matches Section -->
    <?php if (isset($_GET['view']) && $_GET['view'] == 'upcoming_matches'): ?>
    <section id="upcoming-matches">
        <h2>Upcoming Matches</h2>
        <div class="matches">
            <?php if (empty($upcomingMatches)): ?>
                <p>No upcoming matches available.</p>
            <?php else: ?>
                <?php foreach ($upcomingMatches as $match): ?>
                    <div class="match-card">
                        <p><strong><?php echo htmlspecialchars($clubs[$match['home_club_id']]['c_name'] ?? 'Unknown'); ?></strong> 
                        vs <strong><?php echo htmlspecialchars($clubs[$match['away_club_id']]['c_name'] ?? 'Unknown'); ?></strong></p>
                        <p>Date: <?php echo htmlspecialchars($match['date']); ?></p>
                        <p>Time: <?php echo htmlspecialchars($match['time']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>
</div>
</body>
</html>