<?php
session_start();
require_once "config.php"; // Fichier de connexion à la base

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

// Vérifier si une réponse a été soumise
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userAnswer = $_POST["answer"];
    $correctAnswer = $_POST["correct_answer"];

    if ($userAnswer === $correctAnswer) {
        $_SESSION["score"] = ($_SESSION["score"] ?? 0) + 1;
        $_SESSION["answered"] = true; // Marquer la question comme répondue
        $message = "✅ Bonne réponse !";
    } else {
        $message = "❌ Mauvaise réponse ! La bonne réponse était : $correctAnswer";
        $_SESSION["answered"] = true; // Marquer la question comme répondue même si la réponse est fausse
    }
} elseif (!isset($_SESSION["answered"])) {
    $_SESSION["answered"] = false;  // Si aucune question n'a encore été répondue
}

// Vérifier si on a une question en session, sinon on récupère une nouvelle question
if (!isset($_SESSION["question"])) {
    $_SESSION["question"] = getQuestion($pdo);  // On met une question dans la session
}

// Si l'utilisateur passe à la question suivante, on réinitialise l'état
if (isset($_POST["next_question"])) {
    $_SESSION["answered"] = false;
    $_SESSION["question"] = getQuestion($pdo);
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mode Solo - Writing Peace</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .btn { padding: 10px 20px; font-size: 18px; margin: 10px; background: #007BFF; color: white; border: none; cursor: pointer; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>

    <h1>Mode Solo</h1>
    <p><strong>Score :</strong> <?= $_SESSION["score"] ?? 0 ?></p>

    <?php if (isset($message)) echo "<p>$message</p>"; ?>

    <p><strong>Quelle est la langue de cette phrase ?</strong></p>
    <p style="font-size: 24px; font-weight: bold;">"<?= $_SESSION["question"]['phrase'] ?>"</p>

    <?php if (!$_SESSION["answered"]): ?>
        <!-- Afficher les options de réponse -->
        <form method="post">
            <?php foreach ($_SESSION["question"]["choices"] as $choice): ?>
                <button type="submit" name="answer" value="<?= $choice ?>" class="btn"><?= $choice ?></button>
            <?php endforeach; ?>
            <input type="hidden" name="correct_answer" value="<?= $_SESSION["question"]['correct'] ?>">
        </form>
    <?php else: ?>
        <p><a href="solo.php" class="btn">Passer à la question suivante</a></p>
    <?php endif; ?>

</body>
</html>
