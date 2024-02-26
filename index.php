<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #FFF6D9; /* Orange 50 */
        }
        .error {
        color: red; /* Set the text color to red */
        }
        .login-container {
            max-width: 400px;
            margin: auto;
            margin-top: 10%;
        }
        .login-logo {
            display: block;
            margin: auto;
            margin-bottom: 20px;
            width: 100px;
        }
        .login-card {
            padding: 20px;
        }
        .login-form {
            margin-bottom: 20px;
        }
        .login-btn {
            width: 100%;
        }
        .forgot-password {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 login-container">
                <div class="login-card card shadow">
                    <img src="icons/online-learning.png" alt="Education Logo" class="login-logo">
                    <h2 class="text-center mb-4">Login</h2>
                    <?php if(isset($_GET['error'])) { ?>
                        <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
                    <?php } ?>
                    <form action="loginBackend.php" method="post" onsubmit="return validateForm()" class="login-form" name="loginForm">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['token']); ?>">
                        <button type="submit" class="btn btn-primary login-btn">Login</button>
                    </form>
                    <div class="forgot-password">
                        <a href="resetPassword.php" class="text-decoration-none">Forgot password?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript (optional, for certain components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function validateForm() {
            var password = document.forms["loginForm"]["password"].value;

            if (password.length < 6) {
                alert("Password must be at least 6 characters long");
                return false;
            }

            return true;
        }
    </script>
</body>
</html>
