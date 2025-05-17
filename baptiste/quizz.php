
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

// Fonction pour récupérer une phrase aléatoire et ses mauvaises réponses
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
        "choices" => $choices
    ];
}

// Gestion des réponses
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['answer'])) {
    $userAnswer = $_POST["answer"];
    $correctAnswer = $_POST["correct_answer"];
    
    if (!isset($_SESSION["current_question_answered"]) || $_SESSION["current_question_answered"] === false) {
        $max_time_allowed = 10; // secondes
        $current_time = time();
        $elapsed_time = $current_time - ($_SESSION["question_start_time"] ?? $current_time);

        if ($elapsed_time > $max_time_allowed) {
            $message = "⏰ Temps écoulé ! La bonne réponse était : $correctAnswer";
            $_SESSION["lives"] = max(0, $_SESSION["lives"] - 1);
        } else {
            if ($userAnswer === $correctAnswer) {
                if (!isset($_SESSION["score"])) {
                    $_SESSION["score"] = 0;
                }
                $_SESSION["score"] += 10;
                $updatePoints = $pdo->prepare("UPDATE users SET points = points + 10 WHERE id = :user_id");
                $updatePoints->execute([':user_id' => $_SESSION['user_id']]);
                $message = "✅ Bonne réponse ! +10 points";
            } else {
                $message = "❌ Mauvaise réponse ! La bonne réponse était : $correctAnswer";
                $_SESSION["lives"] = max(0, $_SESSION["lives"] - 1);
            }
        }

        $_SESSION["current_question_answered"] = true;
    }
}

// Gestion de la question suivante
if (isset($_POST["next_question"])) {
    $_SESSION["current_question_answered"] = false;
    $_SESSION["question"] = getQuestion($pdo);
    $_SESSION["question_start_time"] = time(); // 🔥 Horodatage de début
}

// Initialisation de la première question
if (!isset($_SESSION["question"])) {
    $_SESSION["question"] = getQuestion($pdo);
    $_SESSION["current_question_answered"] = false;
    $_SESSION["question_start_time"] = time(); // 🔥
}

if (!isset($_SESSION["score"])) {
    $_SESSION["score"] = 0;
}

if (!isset($_SESSION["lives"])) {
    $_SESSION["lives"] = 3;
}
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
        <script>
        let timeLeft = 10;
        const chrono = document.querySelector('.right h4');
        const timer = setInterval(() => {
            timeLeft--;
            if (chrono) chrono.textContent = timeLeft;
            if (timeLeft <= 0) clearInterval(timer);
        }, 1000);
        </script>

        <main>
            <div class="top">
                <div class="container-Txt">
                    <H1><?php echo htmlspecialchars($_SESSION['question']['phrase']); ?></H1>
                    <img src="../assets/img/point-dinterrogation.png" alt="Logo Peace Words" class="logo" id="openPopup">
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
                        <h4><?php echo $_SESSION['score']; ?></h4>
                        <img src="../assets/img/chrono bleu.png" alt="Chrono">
                    </div>
                </div>
                <?php if (isset($message)): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>
            </div>

            <div class="bottom">
                <a href="settings.php">
                    <img class="icons-nut" src="../assets/img/icons-nut.png" alt="">
                </a>
                <form method="POST" class="container-answers">
                    <input type="hidden" name="correct_answer" value="<?php echo htmlspecialchars($_SESSION['question']['correct']); ?>">
                    <div class="line">
                        <?php for($i = 0; $i < 2; $i++): ?>
                            <button type="submit" name="answer" value="<?php echo htmlspecialchars($_SESSION['question']['choices'][$i]); ?>" class="answer">
                                <h5><?php echo htmlspecialchars($_SESSION['question']['choices'][$i]); ?></h5>
                            </button>
                        <?php endfor; ?>
                    </div>
                    <div class="line">
                        <?php for($i = 2; $i < 4; $i++): ?>
                            <button type="submit" name="answer" value="<?php echo htmlspecialchars($_SESSION['question']['choices'][$i]); ?>" class="answer">
                                <h5><?php echo htmlspecialchars($_SESSION['question']['choices'][$i]); ?></h5>
                            </button>
                        <?php endfor; ?>
                    </div>
                    <?php if (isset($_SESSION['current_question_answered']) && $_SESSION['current_question_answered']): ?>
                        <button type="submit" name="next_question" class="next-question">Question suivante</button>
                    <?php endif; ?>
                </form>
            </div>

        </main>
            <div class="overlay" id="popupOverlay">
                <div class="popup">
                <img src="../assets/img/close.png" alt="Fermer" class="close-img" id="closePopup">
                <h2>Info utiles</h2>
                <p>Voici le contenu de votre pop-up. Vous pouvez ajouter ici n’importe quel texte ou élément HTML.</p>
                </div>
            </div>

            <!-- Script JS -->
              <script>
                // Script pour la popup
                const openBtn = document.getElementById('openPopup');
                const overlay = document.getElementById('popupOverlay');
                const closeBtn = document.getElementById('closePopup');

                openBtn.addEventListener('click', () => {
                    overlay.style.display = 'flex';
                });

                closeBtn.addEventListener('click', () => {
                    overlay.style.display = 'none';
                });

                // Script pour masquer les réponses
                document.querySelectorAll('.answer').forEach(button => {
                    button.addEventListener('click', function() {
                        // Masquer toutes les réponses
                        document.querySelectorAll('.answer').forEach(btn => {
                            btn.classList.add('hidden');
                        });
                    });
                });
            </script>
        
</body>

</html>