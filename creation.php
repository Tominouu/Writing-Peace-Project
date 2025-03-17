<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $email = $_POST['email'];



    if (empty($username) || empty($password) || empty($email)) {
        die("Tous les champs sont obligatoires.");
    }

    // Vérifier si l'utilisateur existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        die("Nom d'utilisateur déjà pris.");
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$username, $email, $hashedPassword])) {
        echo "Compte créé avec succès. <a href='authentificate.php'>Se connecter</a>";
    } else {
        echo "Erreur lors de la création du compte.";
    }
}
?>

<form method="POST">
<div class="form-row">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" name="username" placeholder="Placeholder" required>
                </div>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" placeholder="Placeholder" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" name="password" placeholder="Placeholder" required>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="terms">
                <label for="terms">J'accepte les conditions d'utilisations afin de créer un compte</label required>
            </div>
            <button type="submit">Créer un compte</button>
</div>
</form>
