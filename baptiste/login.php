<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page d'inscription</title>
    <link rel="stylesheet" href="../assets/css/all.css">
    <link rel="stylesheet" href="../assets/css/signup.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
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
            <a href="index.php">
                <img src="../assets/img/icons-home.png" alt="Home Icon"  class="icons-home">
            </a>
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
            <h1 class="signup-title">Log In</h1>
            <div class="signup-container">
                <form class="signup-form" method="POST" action="../authentificate.php">
                    <div class="input-group-collumn">
                        <h2>Username</h2>
                        <input type="text" placeholder="bobdu72xXxl" name="username" required>
                    </div>
                    <div class="input-group-collumn">
                        <h2>Password</h2>
                        <input type="password" placeholder="..." name="password" required>
                    </div>
                    <button type="submit" class="submit-btn">SUBMIT</button>
                </form>
                <div class="login-text">
                    <p>Don't have an account ?</p>
                    <a href="signup.php">
                        <img src="../assets/img/icons-arrow.png" class="icons-arrow">
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
