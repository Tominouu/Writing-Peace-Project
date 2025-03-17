<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        die("Tous les champs sont obligatoires.");
    }

    // Vérifier si l'utilisateur existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        die("Nom d'utilisateur déjà pris.");
    }

    // Hasher le mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insérer dans la base de données
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    if ($stmt->execute([$username, $hashedPassword])) {
        echo "Compte créé avec succès. <a href='authentificate.php'>Se connecter</a>";
    } else {
        echo "Erreur lors de la création du compte.";
    }
}
?>

<form method="POST">
    <input type="text" name="username" placeholder="Nom d'utilisateur" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <button type="submit">Créer un compte</button>
</form>
