<?php
require_once "config.php"; // connexion PDO
session_start();

// Récupération des 10 meilleurs utilisateurs par points
$stmt = $pdo->query("SELECT username, points FROM users ORDER BY points DESC LIMIT 10");
$ranking = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classement - Writing Peace</title>
    <link rel="stylesheet" href="../assets/css/all.css">
    <link rel="stylesheet" href="../assets/css/ranking.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body>
    <a href="index.html">
        <img src="../assets/img/logo.png" alt="Logo Peace Words" class="logo">
    </a>
    <div class="left-section"></div>
    <div class="right-section">
        <header>
            <a href="index.html">
                <img src="../assets/img/icons-home.png" alt="Home Icon" class="icons-home">
            </a>
            <img src="../assets/img/player.png" alt="Logo Peace Words">
            <h3><?= $_SESSION['pseudo'] ?? 'Invité' ?></h3>
            <div class="connect">
                <a href="login.php"><button class="login"><h3>Log in</h3></button></a>
                <a href="signup.php"><button class="signup"><h3>Sign up</h3></button></a>
            </div>
        </header>
    </div>
    <main>
        <div class="allsettings">
            <h1>RANKING</h1>
            <div class="container">
                <div class="ranking-wrapper">
                    <?php
                    $place = 1;
                    foreach ($ranking as $player):
                        // style alterné entre part1 / part2 pour l'existant
                        $class = ($place % 2 == 0) ? "part2" : "part1";
                    ?>
                    <div class="<?= $class ?>">
                        <div class="partleft">
                            <div class="containerplace">
                                <h3>#<?= $place ?></h3>
                            </div>
                            <img src="../assets/img/player.png" alt="">
                            <h4><?= htmlspecialchars($player['pseudo']) ?></h4>
                        </div>
                        <div class="<?= $class ?>right">
                            <h3><?= number_format($player['points'], 0, ',', ' ') ?></h3>
                        </div>
                    </div>
                    <?php $place++; endforeach; ?>
                </div>   
            </div>
        </div>
    </main>
    <script src="js/script.js"></script>
</body>
</html>
