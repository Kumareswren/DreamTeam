<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #FAFAD2;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        
        .login-container {
            background-color:#EEE8AA;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .input-group {
            margin-bottom: 20px;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        
        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            transition: border-color 0.3s ease;
        }
        
        .input-group input:focus {
            border-color: #007bff;
        }
        
        .submit-btn {
            width: 105%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .submit-btn:hover {
            background-color: #0056b3;
        }
        
        .error{
            color: red;
            text-align: center;
            margin-top: 20px;
        }
    </style>

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

</head>
<body>
    <div class="login-container">
    <form action="loginBackend.php" method="post" onsubmit="return validateForm()" name="loginForm">
        <h2>Login</h2>
        <?php if(isset($_GET['error'])) { ?>
            <p class="error"> <?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php } 
        ?>
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['token']); ?>">
            <button type="submit" class="submit-btn">Login</button>
        </form>
        
    </div>
</body>
</html>