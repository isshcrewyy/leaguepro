<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeaguePro</title>
    <link rel="stylesheet" href="../assests/css/indexStyle.css"> 
</head>
<body> 
    <header>
        <div class="header-container">
            <a href="#" class="home-button">LeaguePro</a>
            <div class="admin-button-container">
                <!-- Fixed: Added onclick handler for redirecting -->
                <button class="admin-button" onclick="window.location.href='proAdmin.php'">Admin</button>
            </div>
        </div>
        <h2 class="quote">Feel the heat of the game with LeaguePro</h2>
    </header>

    <div class="container">
        <h1>Welcome to LeaguePro</h1>
        <img src="../assests/imgs/bg1.jpg" alt="LeaguePro Background Image" class="bg-image"> 
        <div class="button-container-1">
            <button class="fans-button" onclick="window.location.href='fans.php'">Fans</button>
        </div>

        <div class="button-container-2">
            <button class="organizer-button" onclick="window.location.href='login.php'">Organizer</button>
        </div>
    </div>
</body>
</html>
