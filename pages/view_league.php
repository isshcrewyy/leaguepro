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

// Get the league_id from the URL
$league_id = isset($_GET['league_id']) ? $_GET['league_id'] : 0;

// Fetch League Name
$leagueQuery = "SELECT league_name FROM league WHERE league_id = $league_id";
$leagueResult = $conn->query($leagueQuery);
$league = $leagueResult->fetch_assoc();

// Fetch Clubs for the selected League
$clubs = [];
$clubsQuery = "SELECT club_id, name, location, founded_year FROM club WHERE league_id = $league_id";
$clubsResult = $conn->query($clubsQuery);

if ($clubsResult && $clubsResult->num_rows > 0) {
    while ($row = $clubsResult->fetch_assoc()) {
        $clubs[$row['club_id']] = $row;
    }
}

// Fetch Leaderboard for the selected League
$leaderboard = [];
$leaderboardQuery = "SELECT club_id, points, wins, losses, draws, goal_difference FROM leaderboard WHERE league_id = $league_id ORDER BY points DESC";
$leaderboardResult = $conn->query($leaderboardQuery);

if ($leaderboardResult && $leaderboardResult->num_rows > 0) {
    while ($row = $leaderboardResult->fetch_assoc()) {
        $leaderboard[] = $row;
    }
}

// Fetch Players by Club
$players = [];
$playersQuery = "SELECT club_id, name, age, position, phone_number FROM player WHERE club_id IN (SELECT club_id FROM club WHERE league_id = $league_id)";
$playersResult = $conn->query($playersQuery);

if ($playersResult && $playersResult->num_rows > 0) {
    while ($row = $playersResult->fetch_assoc()) {
        $players[$row['club_id']][] = $row;
    }
}

// Current Date and Time
$currentDate = date('Y-m-d');
$currentTime = date('H:i:s');

// Fetch Matches for the selected League
$matchesQuery = "SELECT * FROM game WHERE league_id = $league_id ORDER BY date, time ASC";
$result = $conn->query($matchesQuery);

$pastMatches = [];
$currentMatches = [];
$upcomingMatches = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
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
</head>
<body>
    <div class="container">
        <!-- Display League Name -->
        <section>
            <h2>Matches for League: <?php echo htmlspecialchars($league['league_name']); ?></h2>
        </section>

        <header>
            <h1>League Fixtures</h1>
            <!-- Place the "View Leaderboard" link outside the club cards -->
            <div class="button-group">
                <a href="?view=leaderboard&league_id=<?php echo $league_id; ?>">View Leaderboard</a>
            </div>
        </header>

        <!-- Past Matches -->
        <section>
            <h2>Past Matches</h2>
            <div class="matches">
                <?php if (empty($pastMatches)): ?>
                    <p>No past matches available.</p>
                <?php else: ?>
                    <?php foreach ($pastMatches as $match): ?>
                        <div class="match-card">
                            <p><strong><?php echo htmlspecialchars($clubs[$match['home_club_id']]['name'] ?? 'Unknown'); ?></strong> 
                            vs <strong><?php echo htmlspecialchars($clubs[$match['away_club_id']]['name'] ?? 'Unknown'); ?></strong></p>
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
                            <p><strong><?php echo htmlspecialchars($clubs[$match['home_club_id']]['name'] ?? 'Unknown'); ?></strong> 
                            vs <strong><?php echo htmlspecialchars($clubs[$match['away_club_id']]['name'] ?? 'Unknown'); ?></strong></p>
                            <p>Date: <?php echo htmlspecialchars($match['date']); ?></p>
                            <p>Time: <?php echo htmlspecialchars($match['time']); ?></p>
                            <p>Score: <?php echo htmlspecialchars($match['score_home']); ?> - <?php echo htmlspecialchars($match['score_away']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Clubs Section -->
        <section>
            <h2>Clubs in this League</h2>
            <div class="card-container">
                <?php foreach ($clubs as $club): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($club['name']); ?></h3>
                        <p>Location: <?php echo htmlspecialchars($club['location']); ?></p>
                        <p>Founded: <?php echo htmlspecialchars($club['founded_year']); ?></p>
                        <div class="button-group">
                            <a href="?club_id=<?php echo $club['club_id']; ?>">View Players</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Leaderboard -->
        <?php if (isset($_GET['view']) && $_GET['view'] == 'leaderboard'): ?>
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
                            <td><?php echo htmlspecialchars($clubs[$entry['club_id']]['name'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($entry['points']); ?></td>
                            <td><?php echo htmlspecialchars($entry['wins']); ?></td>
                            <td><?php echo htmlspecialchars($entry['losses']); ?></td>
                            <td><?php echo htmlspecialchars($entry['draws']); ?></td>
                            <td><?php echo htmlspecialchars($entry['goal_difference']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
