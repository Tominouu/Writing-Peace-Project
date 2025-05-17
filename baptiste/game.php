<?php
session_start();
require_once "../config.php";

if (!isset($_SESSION['user_id']) || !isset($_GET['code'])) {
    header("Location: /baptiste/multiplayer.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$room_code = $_GET['code'];

// Vérifier si l'utilisateur fait partie de la room
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE code = ? AND (player1_id = ? OR player2_id = ?)");
$stmt->execute([$room_code, $user_id, $user_id]);
$room = $stmt->fetch();

if (!$room) {
    header("Location: /baptiste/multiplayer.php");
    exit();
}

// Fonction pour obtenir une question aléatoire
function getQuestion($pdo) {
    $stmt = $pdo->query("SELECT * FROM phrases ORDER BY RAND() LIMIT 1");
    $question = $stmt->fetch();

    $stmt = $pdo->query("SELECT langue FROM phrases WHERE langue != '{$question['langue']}' ORDER BY RAND() LIMIT 3");
    $wrongAnswers = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $choices = $wrongAnswers;
    $choices[] = $question['langue'];
    shuffle($choices);

    return [
        "phrase" => $question['phrase'],
        "correct" => $question['langue'],
        "choices" => $choices,
        "histoire" => $question['histoire'],
        "image_ecriture" => $question['image_ecriture']
    ];
}

// Traitement des réponses
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['answer'])) {
    $userAnswer = $_POST["answer"];
    $correctAnswer = $_POST["correct_answer"];
    $isPlayer1 = ($user_id == $room['player1_id']);
    $scoreField = $isPlayer1 ? 'player1_score' : 'player2_score';

    if ($userAnswer === $correctAnswer) {
        $stmt = $pdo->prepare("UPDATE rooms SET $scoreField = $scoreField + 10, current_question = current_question + 1 WHERE code = ?");
        $stmt->execute([$room_code]);
    } else {
        $stmt = $pdo->prepare("UPDATE rooms SET current_question = current_question + 1 WHERE code = ?");
        $stmt->execute([$room_code]);
    }

    // Vérifier si c'est la fin du jeu (10 questions)
    $stmt = $pdo->prepare("SELECT current_question FROM rooms WHERE code = ?");
    $stmt->execute([$room_code]);
    $currentQuestion = $stmt->fetch()['current_question'];

    if ($currentQuestion > 10) {
        $stmt = $pdo->prepare("UPDATE rooms SET game_status = 'finished' WHERE code = ?");
        $stmt->execute([$room_code]);
    }
}

// Récupérer les informations des joueurs
$stmt = $pdo->prepare("SELECT u1.username as player1_name, u2.username as player2_name, r.* 
                       FROM rooms r 
                       LEFT JOIN users u1 ON r.player1_id = u1.id 
                       LEFT JOIN users u2 ON r.player2_id = u2.id 
                       WHERE r.code = ?");
$stmt->execute([$room_code]);
$gameInfo = $stmt->fetch();

// Générer une nouvelle question si le jeu est en cours
if ($gameInfo['game_status'] === 'in_progress') {
    $_SESSION['question'] = getQuestion($pdo);
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
    <title>Duel - Writing Peace</title>
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
            <div class="game-info">
                <div class="player-info">
                    <h3><?= htmlspecialchars($gameInfo['player1_name']) ?></h3>
                    <p>Score: <?= $gameInfo['player1_score'] ?></p>
                </div>
                <div class="vs">VS</div>
                <div class="player-info">
                    <h3><?= htmlspecialchars($gameInfo['player2_name'] ?? 'En attente...') ?></h3>
                    <p>Score: <?= $gameInfo['player2_score'] ?></p>
                </div>
            </div>
            <div class="question-counter">
                Question <?= min($gameInfo['current_question'], 10) ?>/10
            </div>
        </header>

        <main>
            <?php if ($gameInfo['game_status'] === 'waiting'): ?>
                <div class="waiting-screen">
                    <?php if (!is_null($gameInfo['player2_id'])): ?>
                        <div class="countdown">La partie commence dans <span id="countdown">10</span></div>
                        <script>
                            let countdown = 10;
                            const countdownElement = document.getElementById('countdown');
                            const countdownInterval = setInterval(() => {
                                countdown--;
                                if (countdownElement) countdownElement.textContent = countdown;
                                if (countdown <= 0) {
                                    clearInterval(countdownInterval);
                                    window.location.reload();
                                }
                            }, 1000);
                        </script>
                    <?php else: ?>
                        <h2>En attente d'un autre joueur</h2>
                        <p>Code de la room: <strong><?= htmlspecialchars($room_code) ?></strong></p>
                        <p>Partagez ce code avec votre adversaire pour commencer la partie!</p>
                    <?php endif; ?>
                </div>

            <?php elseif ($gameInfo['game_status'] === 'finished'): ?>
                <div class="game-over">
                    <h2>Partie terminée!</h2>
                    <div class="final-scores">
                        <p><?= htmlspecialchars($gameInfo['player1_name']) ?>: <?= $gameInfo['player1_score'] ?> points</p>
                        <p><?= htmlspecialchars($gameInfo['player2_name']) ?>: <?= $gameInfo['player2_score'] ?> points</p>
                    </div>
                    <h3>
                        <?php
                        if ($gameInfo['player1_score'] > $gameInfo['player2_score']) {
                            echo htmlspecialchars($gameInfo['player1_name']) . " remporte la victoire!";
                        } elseif ($gameInfo['player2_score'] > $gameInfo['player1_score']) {
                            echo htmlspecialchars($gameInfo['player2_name']) . " remporte la victoire!";
                        } else {
                            echo "Égalité!";
                        }
                        ?>
                    </h3>
                    <a href="multiplayer.php" class="new-game-button">Nouvelle partie</a>
                </div>

            <?php else: ?>
                <div class="game-container">
                    <div class="timer">
                        <span id="chrono">10</span>
                    </div>
                    <div class="question">
                        <h2><?= htmlspecialchars($_SESSION['question']['phrase']) ?></h2>
                    </div>

                    <form method="POST" class="answers-container" id="quiz-form">
                        <input type="hidden" name="correct_answer" value="<?= htmlspecialchars($_SESSION['question']['correct']) ?>">
                        <div class="answers-grid">
                            <?php foreach ($_SESSION['question']['choices'] as $choice): ?>
                                <button type="submit" name="answer" value="<?= htmlspecialchars($choice) ?>" class="answer-button">
                                    <?= htmlspecialchars($choice) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
    // Rafraîchir la page toutes les 3 secondes si en attente d'un joueur
    <?php if ($gameInfo['game_status'] === 'waiting'): ?>
    setTimeout(function() {
        window.location.reload();
    }, 3000);
    <?php endif; ?>

    <?php if ($gameInfo['game_status'] === 'in_progress'): ?>
    // Timer
    let timeLeft = 10;
    const chrono = document.getElementById('chrono');
    const form = document.getElementById('quiz-form');

    const timer = setInterval(() => {
        timeLeft--;
        if (chrono) chrono.textContent = timeLeft;
        if (timeLeft <= 0) {
            clearInterval(timer);
            if (!document.querySelector('button[name="next_question"]')) {
                const input = document.createElement("input");
                input.type = "hidden";
                input.name = "timeout";
                input.value = "1";
                form.appendChild(input);
                form.submit();
            }
        }
    }, 1000);

    // Masquer les boutons après clic
    document.querySelectorAll('.answer-button').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.answer-button').forEach(btn => btn.style.display = 'none');
            clearInterval(timer);
        });
    });
    <?php endif; ?>
    </script>
</body>
</html>