<?php
// Charger les variables d'environnement depuis .env
$env = parse_ini_file('.env');

// Connexion à la base de données
try {
    $pdo = new PDO(
        "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8",
        $env['DB_USER'],
        $env['DB_PASS'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "Connexion réussie";
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
