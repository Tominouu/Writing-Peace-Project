<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h1 class="login-title">Log In</h1>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" placeholder="Placeholder">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" placeholder="Placeholder">
                <div class="password-hint">It must be a combination of minimum 8 letters, numbers, and symbols.</div>
            </div>
            
            <div class="form-options">
                <div class="remember-me">
                    <input type="checkbox" id="remember">
                    <label for="remember">Remember me</label>
                </div>
                <a href="#" class="forgot-password">Forgot Password?</a>
            </div>
            
            <button class="login-button">Log In</button>
            
            <div class="social-login">
                <button class="social-button">
                    <span>Log in with Google</span>
                </button>
                <button class="social-button">
                    <span>Log in with Apple</span>
                </button>
            </div>
            
            <div class="divider"></div>
            
            <div class="signup-link">
                No account yet? <a href="index.php">Sign Up</a>
            </div>
        </div>
    </div>
    
    <div class="image-container">
        <img src="assets/img/writing-peace.png" alt="Peace in Different Languages" class="logo-image">
    </div>
</body>
</html>