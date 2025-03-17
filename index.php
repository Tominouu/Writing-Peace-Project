<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: authentificate.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Writing Peace - Accueil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
            flex-direction: column;
            text-align: center;
        }
        h1 {
            margin-bottom: 20px;
        }
        .container {
            display: flex;
            gap: 20px;
        }
        .btn {
            padding: 15px 30px;
            font-size: 18px;
            color: white;
            background-color: #007BFF;
            border: none;
            cursor: pointer;
            border-radius: 8px;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <h1>Bienvenue sur Writing Peace</h1>
    <p>Découvrez de nouvelles langues et testez vos connaissances !</p>
    
    <div class="container">
        <a href="solo.php" class="btn">Mode Solo</a>
        <a href="multijoueur.php" class="btn">Mode Multijoueur</a>
    </div>

</body>
</html>
