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


// Traitement des réponses ou expiration
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correctAnswer = $_POST["correct_answer"];

    $max_time_allowed = 10;
    $current_time = time();
    $elapsed_time = $current_time - ($_SESSION["question_start_time"] ?? $current_time);

    if (!isset($_SESSION["current_question_answered"]) || $_SESSION["current_question_answered"] === false) {
        if (isset($_POST['answer'])) {
            $userAnswer = $_POST["answer"];

            if ($elapsed_time > $max_time_allowed) {
                $message = "⏰ Time's up! The correct answer was: $correctAnswer";
                $_SESSION["lives"] = max(0, $_SESSION["lives"] - 1);
            } else {
                if ($userAnswer === $correctAnswer) {
                    $_SESSION["score"] = ($_SESSION["score"] ?? 0) + 10;
                    $updatePoints = $pdo->prepare("UPDATE users SET points = points + 10 WHERE id = :user_id");
                    $updatePoints->execute([':user_id' => $_SESSION['user_id']]);
                    $message = "✅ Good answer! +10 points";
                } else {
                    $message = "❌ Wrong answer! The correct answer was: $correctAnswer";
                    $_SESSION["lives"] = max(0, $_SESSION["lives"] - 1);
                }
            }

            $_SESSION["current_question_answered"] = true;
        }

        // Gestion timeout automatique (quand JS envoie un POST sans réponse)
        if (isset($_POST["timeout"])) {
            $message = "⏰ Time's up! The correct answer was: $correctAnswer";
            $_SESSION["lives"] = max(0, $_SESSION["lives"] - 1);
            $_SESSION["current_question_answered"] = true;
        }
    }
}

if (isset($_POST["next_question"])) {
    $_SESSION["current_question_answered"] = false;
    $_SESSION["question"] = getQuestion($pdo);
    $_SESSION["question_start_time"] = time();
}

if (!isset($_SESSION["question"])) {
    $_SESSION["question"] = getQuestion($pdo);
    $_SESSION["current_question_answered"] = false;
    $_SESSION["question_start_time"] = time();
}

if (!isset($_SESSION["score"])) $_SESSION["score"] = 0;
if (!isset($_SESSION["lives"])) $_SESSION["lives"] = 3;

// 🎯 FIN DE PARTIE : réinitialiser si demandé
if (isset($_POST['restart_game'])) {
    $_SESSION["score"] = 0;
    $_SESSION["lives"] = 3;
    $_SESSION["question"] = getQuestion($pdo);
    $_SESSION["current_question_answered"] = false;
    $_SESSION["question_start_time"] = time();
}

// 🎯 FIN DE PARTIE : condition
$game_over = ($_SESSION["lives"] <= 0);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/all.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/solo.css">
    <link rel="stylesheet" href="../assets/css/quiz.css">
    <title>Writing Peace</title>
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
            <img src="../assets/img/player.png" alt="Logo Peace Words" >
            <h3><?= htmlspecialchars($user['username']) ?></h3>
            <div class="connect">
                <a href="login.php"><button class="login"><h3>Log in</h3></button></a>
                <a href="signup.php"><button class="signup"><h3>Sign up</h3></button></a>
            </div>
        </header>

        <main>
            <?php if ($game_over): ?>
                <div class="game-over-container">
                    <div class="game-over">
                        <h1>💀 Game over!</h1>
                        <p>Score : <strong><?= $_SESSION["score"] ?></strong></p>
                    </div>
                    <form method="POST">
                        <button type="submit" name="restart_game" class="restart-button">Retry</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="top">
                    <div class="container-Txt">
                        <h1><?= htmlspecialchars($_SESSION['question']['phrase']) ?></h1>
                        <img src="../assets/img/point-dinterrogation.png" alt="Aide" class="logo" id="openPopup">
                    </div>
                    <div class="mid">
                        <div class="left">
                            <?php for($i = 0; $i < $_SESSION['lives']; $i++): ?>
                                <img src="../assets/img/Arrière-plan coeur en bleu.png" alt="Coeur rempli">
                            <?php endfor; ?>
                            <?php for($i = $_SESSION['lives']; $i < 3; $i++): ?>
                                <img src="../assets/img/Arrière-plan coeur contour.png" alt="Coeur vide">
                            <?php endfor; ?>
                        </div>
                        <div class="right">
                            <h4 id="chrono">10</h4>
                            <img src="../assets/img/chrono bleu.png" alt="Chrono">
                        </div>
                    </div>

                </div>
                <div class="bottom">

                    <a href="settings.html"><img class="icons-nut" src="../assets/img/icons-nut.png" alt=""></a>
                    <form method="POST" class="container-answers" id="quiz-form">
                        <input type="hidden" name="correct_answer" value="<?= htmlspecialchars($_SESSION['question']['correct']) ?>">
                        <div class="line">
                            <?php for($i = 0; $i < 2; $i++): ?>
                                <button type="submit" name="answer" value="<?= htmlspecialchars($_SESSION['question']['choices'][$i]) ?>" class="answer">
                                    <h5><?= htmlspecialchars($_SESSION['question']['choices'][$i]) ?></h5>
                                </button>
                            <?php endfor; ?>
                        </div>
                        <div class="line">
                            <?php for($i = 2; $i < 4; $i++): ?>
                                <button type="submit" name="answer" value="<?= htmlspecialchars($_SESSION['question']['choices'][$i]) ?>" class="answer">
                                    <h5><?= htmlspecialchars($_SESSION['question']['choices'][$i]) ?></h5>
                                </button>
                            <?php endfor; ?>
                        </div>
                    <?php if (isset($message)): ?>
                        <div class="message"><?= $message ?></div>
                    <?php endif; ?>
                        <?php if ($_SESSION['current_question_answered']): ?>
                            <button type="submit" name="next_question" class="next-question">Next question</button>
                        <?php endif; ?>
                    </form>
                </div>
            <?php endif; ?>
        </main>


        <div class="overlay" id="popupOverlay">
            <div class="popup">
                <img src="../assets/img/close.png" alt="Fermer" class="close-img" id="closePopup">
                <h2>Language : <?= htmlspecialchars($_SESSION['question']['correct']) ?></h2>
                <p><?= nl2br(htmlspecialchars($_SESSION['question']['histoire'])) ?></p>

                <?php if (!empty($_SESSION['question']['image_ecriture'])): ?>
                    <img src="<?= htmlspecialchars($_SESSION['question']['image_ecriture']) ?>" alt="Alphabet" style="max-width: 50%; max-height: auto;  margin-top: 15px;">
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    // Popup
    const openBtn = document.getElementById('openPopup');
    const overlay = document.getElementById('popupOverlay');
    const closeBtn = document.getElementById('closePopup');

    // Désactiver le bouton au début
    openBtn.style.pointerEvents = 'none';
    openBtn.style.opacity = '0.5';

    // Fonction pour activer le bouton
    const enableHelpButton = () => {
        openBtn.style.pointerEvents = 'auto';
        openBtn.style.opacity = '1';
    };

    openBtn.addEventListener('click', () => overlay.style.display = 'flex');
    closeBtn.addEventListener('click', () => overlay.style.display = 'none');

    // Masquer les boutons après clic et activer le bouton d'aide
    document.querySelectorAll('.answer').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.answer').forEach(btn => btn.classList.add('hidden'));
            clearInterval(timer); // stop timer si réponse donnée
            enableHelpButton(); // Activer le bouton d'aide après réponse
        });
    });

    // Compte à rebours JS
    let timeLeft = 10;
    const chrono = document.getElementById('chrono');
    const form = document.getElementById('quiz-form');

    const timer = setInterval(() => {
        timeLeft--;
        if (chrono) chrono.textContent = timeLeft;
        if (timeLeft <= 0) {
            clearInterval(timer);
            enableHelpButton(); // Activer le bouton d'aide quand le timer atteint zéro
            // Empêche double envoi
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
    </script>
</body>
</html>
