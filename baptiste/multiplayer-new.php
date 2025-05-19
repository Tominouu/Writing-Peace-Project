<?php
session_start();
require_once "../config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: /baptiste/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fonction pour générer un code de room unique
function generateRoomCode() {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    do {
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        $stmt = $GLOBALS['pdo']->prepare("SELECT id FROM rooms WHERE code = ?");
        $stmt->execute([$code]);
    } while ($stmt->fetch());
    
    return $code;
}

// Création d'une nouvelle room
if (isset($_POST['create_room'])) {
    $code = generateRoomCode();
    $stmt = $pdo->prepare("INSERT INTO rooms (code, player1_id) VALUES (?, ?)");
    if ($stmt->execute([$code, $user_id])) {
        $_SESSION['room_code'] = $code;
        header("Location: game.php?code=" . $code);
        exit();
    }
}

// Rejoindre une room existante
if (isset($_POST['join_room'])) {
    $code = strtoupper(trim($_POST['room_code']));
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE code = ? AND game_status = 'waiting'");
    $stmt->execute([$code]);
    $room = $stmt->fetch();

    if ($room) {
        $stmt = $pdo->prepare("UPDATE rooms SET player2_id = ?, game_status = 'in_progress' WHERE code = ?");
        if ($stmt->execute([$user_id, $code])) {
            header("Location: game.php?code=" . $code);
            exit();
        }
    } else {
        $error = "Room incorect";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/all.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/multiplayer.css">
    <title>Mode Multijoueur - Writing Peace</title>
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
        </header>

        <main class="multiplayer-container">
            <div class="container-Txt">
                <H1>1 vs 1</H1>
            </div>
            <?php if (isset($error)): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <div class="multiplayer-options">
                <div class="create-room">
                    <h2 style="color:#3e67ab; margin-bottom: 1.2vw;">Create a party</h2>

                    <form method="POST">
                        <button type="submit" name="create_room" style="text-decoration: none;" class="Play-mul"><h1>Create</h1></button>
                    </form>
                </div>

                <div class="join-room">
                    <form method="POST">
                        <input type="text" name="room_code" placeholder="Entrez le code de la room" maxlength="6" required>
                        <button type="submit" name="join_room" style="text-decoration: none;" class="Play-mul"><h1>Join</h1></button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>