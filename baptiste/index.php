<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /baptiste/login.php");
    exit();
}

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Récupération des 4 meilleurs joueurs
$rankingStmt = $pdo->query("SELECT username, points FROM users ORDER BY points DESC LIMIT 4");
$topPlayers = $rankingStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Writing Peace</title>
    <link rel="stylesheet" href="../assets/css/all.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="left-section">
        <div class="logo-container">
            <a href="index.php">
                <img src="../assets/img/logo.png" alt="Logo Peace Words" class="logo">
            </a>
        </div>
    </div>
    <div class="right-section">
        <header>
            <img src="../assets/img/player.png" alt="Logo Peace Words">
            <h3><?= htmlspecialchars($user['username']) ?></h3>
            <div class="connect">
                <a href="login.php">
                    <button class="login"><h3>Log in</h3></button>
                </a>
                <a href="signup.php">
                    <button class="signup"><h3>Sign up</h3></button>
                </a>
            </div>
        </header>
        <main>
            <div class="top">
                <div class="container-Txt">
                    <h1>“Discover the beauty of every script”</h1>
                </div>
                <div class="button-container" style="display: flex; gap: 20px;">
                    <a href="/baptiste/quizz.php" style="text-decoration: none;">
                        <button class="Play"><h1>PLAY</h1></button>
                    </a>
                    <a href="/baptiste/multiplayer.php" style="text-decoration: none;">
                        <button class="Play"><h1>DUO</h1></button>
                    </a>
                </div>
            </div>
            <div class="bottom">
                <a href="settings.html">
                    <img class="icons-nut" src="../assets/img/icons-nut.png" alt="">
                </a>
                <div class="container-Player">
                    <div class="top">
                        <h2>RANKING</h2>
                        <a href="ranking.php">
                            <img class="icons-arrow" src="../assets/img/icons-arrow.png" alt="">
                        </a>
                    </div>
                    <div class="bottom">
                        <div class="other-Player">
                            <?php for ($i = 1; $i < count($topPlayers); $i++): ?>
                            <div class="other-Player-1">
                                <h3>#<?= $i + 1 ?></h3>
                                <img class="Player-img" src="../assets/img/player.png" alt="">
                                <p class="pseudo"><?= htmlspecialchars($topPlayers[$i]['username']) ?></p>
                            </div>
                            <?php endfor; ?>
                        </div>
                        <div class="actual-Player">
                            <?php if (!empty($topPlayers)): ?>
                                <h3>#1</h3>
                                <img class="Player-img" src="../assets/img/player.png" alt="">
                                <p class="pseudo"><?= htmlspecialchars($topPlayers[0]['username']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
</body>
</html>
