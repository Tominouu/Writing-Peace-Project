
<?php
session_start();
require_once "../config.php"; // Fichier de connexion à la base

// Fonction pour récupérer une phrase aléatoire et ses mauvaises réponses
function getQuestion($pdo) {
    // Récupérer une phrase aléatoire
    $stmt = $pdo->query("SELECT * FROM phrases ORDER BY RAND() LIMIT 1");
    $question = $stmt->fetch();

    // Récupérer 3 langues incorrectes
    $stmt = $pdo->query("SELECT langue FROM phrases WHERE langue != '{$question['langue']}' ORDER BY RAND() LIMIT 3");
    $wrongAnswers = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Mélanger la bonne réponse avec les mauvaises
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
        if ($userAnswer === $correctAnswer) {
            if (!isset($_SESSION["score"])) {
                $_SESSION["score"] = 0;
            }
            $_SESSION["score"]++;
            $message = "✅ Bonne réponse !";
        } else {
            $message = "❌ Mauvaise réponse ! La bonne réponse était : $correctAnswer";
        }
        $_SESSION["current_question_answered"] = true;
    }
}

// Gestion de la question suivante
if (isset($_POST["next_question"])) {
    $_SESSION["current_question_answered"] = false;
    $_SESSION["question"] = getQuestion($pdo);
}

// Initialisation de la question et du score
if (!isset($_SESSION["question"])) {
    $_SESSION["question"] = getQuestion($pdo);
    $_SESSION["current_question_answered"] = false;
}

if (!isset($_SESSION["score"])) {
    $_SESSION["score"] = 0;
}

// Initialisation des vies (3 par défaut)
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
            <h3><?php echo $user['username']; ?></h3>
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
                const openBtn = document.getElementById('openPopup');
                const overlay = document.getElementById('popupOverlay');
                const closeBtn = document.getElementById('closePopup');

                openBtn.addEventListener('click', () => {
                overlay.style.display = 'flex';
                });

                closeBtn.addEventListener('click', () => {
                overlay.style.display = 'none';
                });
            </script>
        
</body>

</html>