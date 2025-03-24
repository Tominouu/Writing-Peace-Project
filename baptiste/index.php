<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /baptiste/login.html");
    exit();
}

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page d'inscription</title>
    <link rel="stylesheet" href="../assets/css/all.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="left-section">
        <div class="logo-container">
            <a href="index.html">
                <img src="../assets/img/logo.png" alt="Logo Peace Words" class="logo">
            </a>
        </div>
    </div>
    <div class="right-section">
        <header>
            <img src="../assets/img/player.png" alt="Logo Peace Words" >
            <h3><?php echo $user['username']; ?></h3>
            <div class="connect">
                <a href="login.html">
                    <button class="login"><h3>Log in</h3></button>
                </a>
                <a href="signup.html">
                    <button class="signup"><h3>Sign up</h3></button>
                </a>
            </div>
        </header>
        <main>
            <div class="top">
                <div class="container-Txt">
                    <H1>“Discover the beauty of every script”</H1>
                </div>
                <button class="Play"><H1>PLAY</H1></button>
            </div>
            <div class="bottom">
                <a href="settings.html">
                    <img class="icons-nut" src="../assets/img/icons-nut.png" alt="">
                </a>
                <div class="container-Player">
                    <div class="top">
                        <H2>RANKING</H2>
                        <a href="ranking.html">
                            <img class="icons-arrow" src="../assets/img/icons-arrow.png" alt="">
                        </a>
                    </div>
                    <div class="bottom">
                        <div class="other-Player">
                            <div class="other-Player-1">
                                <h3>#1</h3>
                                <img class="Player-img" src="../assets/img/player.png" alt="">
                                <p class="pseudo">pseudo</p>
                            </div>
                            <div class="other-Player-1">
                                <h3>#1</h3>
                                <img class="Player-img" src="../assets/img/player.png" alt="">
                                <p class="pseudo">pseudo</p>
                            </div>
                            <div class="other-Player-1">
                                <h3>#1</h3>
                                <img class="Player-img" src="../assets/img/player.png" alt="">
                                <p class="pseudo">pseudo</p>
                            </div>
                        </div>
                        <div class="actual-Player">
                            <h3>#1</h3>
                            <img class="Player-img" src="../assets/img/player.png" alt="">
                            <p class="pseudo">pseudo</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
</body>
</html>