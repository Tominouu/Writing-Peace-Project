<?php
session_start();
require_once "config.php"; // Fichier de connexion à la base

// Réinitialiser le score si c'est une nouvelle session
if (!isset($_SESSION["game_started"])) {
    $_SESSION["score"] = 0;
    $_SESSION["game_started"] = true;
    $_SESSION["question_count"] = 0;
}

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

// Gérer la soumission de réponse
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['answer'])) {
    $userAnswer = $_POST["answer"];
    $correctAnswer = $_POST["correct_answer"];
    
    // Vérifier si on n'a pas déjà compté cette réponse
    if (!isset($_SESSION["current_question_id"]) || $_SESSION["current_question_id"] !== $_POST["question_id"]) {
        $_SESSION["current_question_id"] = $_POST["question_id"];
        $_SESSION["question_count"]++;
        
        if ($userAnswer === $correctAnswer) {
            $_SESSION["score"]++;
            $message = "✅ Bonne réponse !";
        } else {
            $message = "❌ Mauvaise réponse ! La bonne réponse était : $correctAnswer";
        }
        $_SESSION["answered"] = true;
    }
}

// Si on clique sur "Question suivante", générer une nouvelle question
if (isset($_POST["next_question"])) {
    $_SESSION["answered"] = false;
    $_SESSION["question"] = getQuestion($pdo);
    $_SESSION["question_id"] = uniqid(); // Générer un nouvel ID unique pour la question
}

// Vérifier si on a une question en session, sinon on récupère une nouvelle question
if (!isset($_SESSION["question"]) || !isset($_SESSION["question_id"])) {
    $_SESSION["question"] = getQuestion($pdo);
    $_SESSION["question_id"] = uniqid();
    $_SESSION["answered"] = false;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Writing Peace</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #3b6fc9 0%, #5b97eb 100%);
            height: 100vh;
            display: flex;
            flex-direction: column;
            color: #333;
        }
        
        .header {
            display: flex;
            justify-content: flex-end;
            padding: 15px 20px;
        }
        
        .auth-buttons {
            display: flex;
            align-items: center;
        }
        
        .home-icon {
            margin-right: 10px;
            font-size: 24px;
            color: white;
            text-decoration: none;
        }
        
        .login-btn, .signup-btn {
            background-color: white;
            color: #3b6fc9;
            border: none;
            border-radius: 20px;
            padding: 8px 20px;
            margin-left: 10px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
        }
        
        .signup-btn {
            background-color: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .main-content {
            display: flex;
            flex: 1;
            position: relative;
        }
        
        .left-panel {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .languages-cloud {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            max-width: 500px;
            position: relative;
            z-index: 2;
        }
        
        .language-tag {
            padding: 8px 15px;
            margin: 5px;
            border-radius: 20px;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        
        .right-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .question-card {
            background-color: white;
            border-radius: 15px;
            padding: 40px 20px;
            width: 80%;
            max-width: 450px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        
        .phrase {
            font-size: 36px;
            font-weight: bold;
            color: #1d4e8f;
            margin: 20px 0 40px 0;
        }
        
        .reaction-buttons {
            position: absolute;
            bottom: 10px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .reaction-btn {
            background: none;
            border: none;
            font-size: 24px;
            margin: 0 5px;
            cursor: pointer;
            color: #7b9edb;
        }
        
        .score {
            position: absolute;
            bottom: 15px;
            right: 20px;
            font-size: 24px;
            color: #666;
            font-weight: bold;
        }
        
        .language-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
            width: 100%;
        }
        
        .language-btn {
            background-color: #5d9bec;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 12px 15px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .language-btn:hover {
            background-color: #3b6fc9;
        }
        
        .next-btn {
            background-color: #5d9bec;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 12px 25px;
            margin-top: 20px;
            font-size: 18px;
            cursor: pointer;
        }
        
        .message {
            margin: 15px 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .diagonal-divider {
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, transparent 40%, rgba(255, 255, 255, 0.1) 40%);
            top: 0;
            left: 0;
            z-index: 1;
        }
        
        /* Couleurs variées pour les tags de langue, comme dans l'image */
        .tag-red { background-color: #e74c3c; }
        .tag-blue { background-color: #3498db; }
        .tag-green { background-color: #2ecc71; }
        .tag-orange { background-color: #f39c12; }
        .tag-purple { background-color: #9b59b6; }
        .tag-teal { background-color: #1abc9c; }
        .tag-brown { background-color: #d35400; }
        .tag-coral { background-color: #e67e22; }
        .tag-pink { background-color: #e84393; }
        .tag-indigo { background-color: #6c5ce7; }
        
        .centered-text {
            text-align: center;
            font-weight: bold;
            color: white;
            margin-bottom: 30px;
            font-size: 20px;
        }
        
        @media (max-width: 768px) {
            .main-content {
                flex-direction: column;
            }
            .left-panel {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="auth-buttons">
            <a href="#" class="home-icon">🏠</a>
            <a href="#" class="login-btn">Log in</a>
            <a href="#" class="signup-btn">Sign Up</a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="diagonal-divider"></div>
        
        <div class="left-panel">
            <div class="languages-cloud">
                <!-- Tags de langue comme dans l'image -->
                <div class="language-tag tag-red">和平</div>
                <div class="language-tag tag-blue">שלום</div>
                <div class="language-tag tag-orange">शांति</div>
                <div class="language-tag tag-coral">ሰላም</div>
                <div class="language-tag tag-teal">平和</div>
                <div class="language-tag tag-purple">Խաղաղություն</div>
                <div class="language-tag tag-brown">سلام</div>
                <div class="language-tag tag-green">мир</div>
                <div class="language-tag tag-red">평화</div>
                <div class="language-tag tag-orange">ειρήνη</div>
                <div class="language-tag tag-blue">Writing Peace</div>
                <div class="language-tag tag-purple">მშვიდობა</div>
                <div class="language-tag tag-teal">Hòa Bình</div>
                <div class="language-tag tag-orange">སྣོད་བཅུད།</div>
                <div class="language-tag tag-indigo">ᓄᖅᑲᑎᑦᑎᓂᖅ</div>
                <div class="language-tag tag-pink">ᐊᖏᕐᓂᖅ</div>
            </div>
        </div>
        
        <div class="right-panel">
            <div class="question-card">
                <div class="phrase"><?= $_SESSION["question"]['phrase'] ?></div>
                
                <?php if (!isset($_SESSION["answered"]) || $_SESSION["answered"] === false): ?>
                <div class="language-options">
                    <form method="post">
                        <?php foreach ($_SESSION["question"]["choices"] as $choice): ?>
                            <button type="submit" name="answer" value="<?= $choice ?>" class="language-btn"><?= $choice ?></button>
                        <?php endforeach; ?>
                        <input type="hidden" name="correct_answer" value="<?= $_SESSION["question"]['correct'] ?>">
                        <input type="hidden" name="question_id" value="<?= $_SESSION["question_id"] ?>">
                    </form>
                </div>
                <?php else: ?>
                <div class="message"><?= $message ?? "" ?></div>
                <form method="post">
                    <button type="submit" name="next_question" class="next-btn">Question suivante</button>
                </form>
                <?php endif; ?>
                
                <div class="reaction-buttons">
                    <button class="reaction-btn">💙</button>
                    <button class="reaction-btn">❤️</button>
                    <button class="reaction-btn">💜</button>
                </div>
                
                <div class="score"><?= $_SESSION["score"] ?> ⏱️</div>
            </div>
        </div>
    </div>
</body>
</html>