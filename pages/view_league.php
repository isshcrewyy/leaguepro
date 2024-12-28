<?php
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
        
    </script>
</head>
<body>
<div class="button-group">
    <a href="index.php" class="btn">League Pro</a>
</div>

    <div class="container">
        <header>
            <h1>League Fixtures</h1>
            <h2>League: <?php echo htmlspecialchars($league['league_name']); ?></h2>
            <div class="button-group">
            <a href="view_leaderboard.php" class="btn">Leaderboard</a>
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
                            <a href="?league_id=<?php echo $league_id; ?>&club_id=<?php echo $club['club_id']; ?>">View Players</a>
                         </div>
                </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

                    <!-- Players Section -->
                    <?php if ($club_id > 0 && !empty($players)): ?>
                        <section>
                            <h2>Players</h2>
                            <div class="card-container">
                                <?php foreach ($players as $player): ?>
                                    <div class="card">
                                        <h3><?php echo htmlspecialchars($player['p_name']); ?></h3>
                                        <p>Age: <?php echo htmlspecialchars($player['age']); ?></p>
                                        <p>Position: <?php echo htmlspecialchars($player['position']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php elseif ($club_id > 0): ?>
                        <section>
                            <h2>No players found for this club</h2>
                        </section>
                    <?php endif; ?>

                     <!-- coach Section -->
                     <?php if ($club_id > 0 && !empty($coachs)): ?>
                        <section>
                            <h2>Coach</h2>
                            <div class="card-container">
                                <?php foreach ($coachs as $coach): ?>
                                    <div class="card">
                                        <h3><?php echo htmlspecialchars($coach['co_name']); ?></h3>
                                        <p>Age: <?php echo htmlspecialchars($coach['age']); ?></p>
                                        <p>Experience <?php echo htmlspecialchars($coach['experience']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php elseif ($club_id > 0): ?>
                        <section>
                            <h2>No coach found for this club</h2>
                        </section>
                    <?php endif; ?>

        <!-- Past Matches Section -->
        <section>
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

        <!-- Upcoming Matches Section -->
        <section>
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
                            <p>Score: <?php echo htmlspecialchars($match['score_home']); ?> - <?php echo htmlspecialchars($match['score_away']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Leaderboard Section -->
        <?php if (isset($_GET['view']) && $_GET['view'] == 'leaderboard'): ?>
            <section>
                <h2>Leaderboard</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Club</th>
                            <th>Points</th>
                            <th>Wins</th>
                            <th>Losses</th>
                            <th>Draws</th>
                            <th>Goal Difference</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leaderboard as $entry): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($clubs[$entry['club_id']]['c_name'] ?? 'Unknown'); ?></td>
                                <td><?php echo htmlspecialchars($entry['points']); ?></td>
                                <td><?php echo htmlspecialchars($entry['wins']); ?></td>
                                <td><?php echo htmlspecialchars($entry['losses']); ?></td>
                                <td><?php echo htmlspecialchars($entry['draws']); ?></td>
                                <td><?php echo htmlspecialchars($entry['goal_difference']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endif; ?>
    </div>
</body>
</html>
