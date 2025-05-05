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
    
    // Récupérer les informations sur le système d'écriture
    $stmt = $pdo->prepare("SELECT systeme_ecriture, info_ecriture FROM systemes_ecriture WHERE langue = ?");
    $stmt->execute([$question['langue']]);
    $writingSystem = $stmt->fetch();
    
    // Si aucune info n'est trouvée, utiliser des informations par défaut
    if (!$writingSystem) {
        $writingSystem = [
            'systeme_ecriture' => 'Non spécifié',
            'info_ecriture' => 'Informations non disponibles pour ce système d\'écriture.'
        ];
    }

    return [
        "phrase" => $question['phrase'],
        "correct" => $question['langue'],
        "choices" => $choices,
        "writing_system" => $writingSystem['systeme_ecriture'],
        "writing_info" => $writingSystem['info_ecriture']
    ];
}

// Correction du bug de score: uniquement incrémenter quand la réponse est correcte et n'a pas été déjà comptée
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['answer'])) {
    $userAnswer = $_POST["answer"];
    $correctAnswer = $_POST["correct_answer"];
    
    // Vérifier si on n'a pas déjà compté cette réponse
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

// Si on clique sur "Passer à la question suivante", réinitialiser l'état de la question
if (isset($_POST["next_question"])) {
    $_SESSION["current_question_answered"] = false;
    $_SESSION["question"] = getQuestion($pdo);
}

// Vérifier si on a une question en session, sinon on récupère une nouvelle question
if (!isset($_SESSION["question"])) {
    $_SESSION["question"] = getQuestion($pdo);
    $_SESSION["current_question_answered"] = false;
}

// Initialiser le score s'il n'existe pas
if (!isset($_SESSION["score"])) {
    $_SESSION["score"] = 0;
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
            background: linear-gradient(135deg, #3c74c9 0%, #5d9bec 100%);
            height: 100vh;
            display: flex;
            flex-direction: column;
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
            color: #3c74c9;
            border: none;
            border-radius: 20px;
            padding: 8px 15px;
            margin-left: 10px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
        }
        
        .signup-btn {
            background-color: #3c74c9;
            color: white;
            border: 2px solid white;
        }
        
        .main-content {
            display: flex;
            flex: 1;
            position: relative;
            overflow: hidden;
        }
        
        .left-panel {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .languages-cloud {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            max-width: 600px;
        }
        
        .language-tag {
            padding: 8px 15px;
            margin: 5px;
            border-radius: 20px;
            color: white;
            font-weight: bold;
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
            padding: 40px 30px;
            width: 80%;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .phrase-container {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 30px 0;
        }
        
        .phrase {
            font-size: 28px;
            font-weight: bold;
            color: #3c74c9;
        }
        
        .info-button {
            position: absolute;
            right: -10px;
            top: -10px;
            background-color: #f39c12;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .reaction-buttons {
            margin: 20px 0;
            display: flex;
            justify-content: center;
        }
        
        .reaction-btn {
            background: none;
            border: none;
            font-size: 24px;
            margin: 0 5px;
            cursor: pointer;
        }
        
        .score {
            font-size: 24px;
            color: #666;
        }
        
        .language-options {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 20px;
        }
        
        .language-btn {
            background-color: #5d9bec;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 12px 25px;
            margin: 10px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .language-btn:hover {
            background-color: #3c74c9;
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
            width: 100px;
            height: 200%;
            background-color: white;
            opacity: 0.2;
            transform: rotate(20deg);
            top: -50%;
            left: 50%;
            z-index: 0;
        }
        
        /* Modal pour les infos d'écriture */
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            overflow: auto;
        }
        
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 30px;
            border-radius: 15px;
            width: 80%;
            max-width: 600px;
            position: relative;
            animation: modalOpen 0.4s;
        }
        
        @keyframes modalOpen {
            from {opacity: 0; transform: scale(0.9);}
            to {opacity: 1; transform: scale(1);}
        }
        
        .close-modal {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 28px;
            font-weight: bold;
            color: #3c74c9;
            cursor: pointer;
        }
        
        .writing-system-info {
            text-align: left;
        }
        
        .writing-system-title {
            color: #3c74c9;
            font-size: 22px;
            margin-bottom: 15px;
        }
        
        .writing-system-desc {
            font-size: 16px;
            line-height: 1.6;
            color: #333;
        }
        
        /* Couleurs aléatoires pour les tags de langue */
        .tag-color-1 { background-color: #e74c3c; }
        .tag-color-2 { background-color: #3498db; }
        .tag-color-3 { background-color: #2ecc71; }
        .tag-color-4 { background-color: #f39c12; }
        .tag-color-5 { background-color: #9b59b6; }
        .tag-color-6 { background-color: #1abc9c; }
        .tag-color-7 { background-color: #d35400; }
        .tag-color-8 { background-color: #c0392b; }
        
        /* Bouton de découverte */
        .discover-button {
            background-color: #2ecc71;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 12px 25px;
            margin: 20px auto 10px auto;
            font-size: 16px;
            cursor: pointer;
            display: block;
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
            <a href="index.php" class="home-icon">🏠</a>
            <a href="#" class="login-btn">Log in</a>
            <a href="#" class="signup-btn">Sign Up</a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="diagonal-divider"></div>
        
        <div class="left-panel">
            <div class="languages-cloud">
                <?php
                $languages = ['和平', 'שלום', 'शांति', 'мир', 'Pax', 'سلام', 'ειρήνη', 'Hòa Bình', 'Peace', 'Paix', '平和'];
                $colors = range(1, 8);
                foreach ($languages as $lang) {
                    $colorClass = 'tag-color-' . $colors[array_rand($colors)];
                    echo "<div class='language-tag $colorClass'>$lang</div>";
                }
                ?>
                <div class="language-tag tag-color-2">Writing Peace</div>
            </div>
        </div>
        
        <div class="right-panel">
            <div class="question-card">
                <div class="phrase-container">
                    <div class="phrase"><?= $_SESSION["question"]['phrase'] ?></div>
                    <?php if (isset($_SESSION["current_question_answered"]) && $_SESSION["current_question_answered"] === true): ?>
                    <button id="infoButton" class="info-button">?</button>
                    <?php endif; ?>
                </div>
                
                <div class="reaction-buttons">
                    <button class="reaction-btn">💙</button>
                    <button class="reaction-btn">❤️</button>
                    <button class="reaction-btn">💜</button>
                    <span class="score"><?= $_SESSION["score"] ?> ⏱️</span>
                </div>
                
                <?php if (isset($message)): ?>
                <div class="message"><?= $message ?></div>
                <?php endif; ?>
                
                <?php if (!isset($_SESSION["current_question_answered"]) || $_SESSION["current_question_answered"] === false): ?>
                <div class="language-options">
                    <form method="post">
                        <?php foreach ($_SESSION["question"]["choices"] as $choice): ?>
                            <button type="submit" name="answer" value="<?= $choice ?>" class="language-btn"><?= $choice ?></button>
                        <?php endforeach; ?>
                        <input type="hidden" name="correct_answer" value="<?= $_SESSION["question"]['correct'] ?>">
                    </form>
                </div>
                <?php else: ?>
                <form method="post">
                    <button type="submit" name="next_question" class="next-btn">Question suivante</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal pour les informations sur le système d'écriture -->
    <div id="writingSystemModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div class="writing-system-info">
                <h3 class="writing-system-title">Système d'écriture : <?= $_SESSION["question"]['writing_system'] ?></h3>
                <p class="writing-system-desc"><?= $_SESSION["question"]['writing_info'] ?></p>
                <button class="discover-button">Découvrir plus sur ce système d'écriture</button>
            </div>
        </div>
    </div>
    
    <script>
        // Script pour la modal
        const modal = document.getElementById("writingSystemModal");
        const infoBtn = document.getElementById("infoButton");
        const closeBtn = document.querySelector(".close-modal");
        
        // Afficher la modal quand on clique sur le bouton info
        if (infoBtn) {
            infoBtn.onclick = function() {
                modal.style.display = "block";
            }
        }
        
        // Fermer la modal quand on clique sur X
        if (closeBtn) {
            closeBtn.onclick = function() {
                modal.style.display = "none";
            }
        }
        
        // Fermer la modal si on clique en dehors
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>