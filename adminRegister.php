<?php
session_start();

// Include necessary files and initialize the database connection
include "db.php";
require_once('vendor/autoload.php'); // Include the JWT library

use \Firebase\JWT\JWT;

// Check if the user is an admin (you may have a better way to check this)
$isAdmin = true; // Set this based on your authentication logic

if (!$isAdmin) {
    // Redirect to login page or unauthorized access page
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Function to clear the form
        function clearForm() {
            document.forms["adminRegistrationForm"].reset();
        }

    </script>
    <style>
        body {
            background-color: #FFF6D9; /* Orange 50 */
        }
        .error {
            color: red; /* Set the text color to red */
        }
        .success {
            color: green; /* Set the text color to green */
        }
        .container {
            max-width: 600px;
            margin: auto;
            margin-top: 5%;
        }
        .logo {
            display: block;
            margin: auto;
            margin-bottom: 20px;
            width: 100px;
        }
        .card {
            padding: 20px;
            margin-bottom: 20px;
        }
        .form {
            margin-bottom: 20px;
        }
        .btn-action {
            width: 100%;
        }

        /* for Sidebar items */  #menu .nav-link .d-none.d-sm-inline {
    color: #ffffff;
}

.custom-div {
    background-color: #FFF6D9;
    padding: 20px;
}

.nav-link {
    font-family: Arial, sans-serif; 
    font-size: 11px; 
    font-weight: 350; 
    padding: 13px 38px;
}

.nav-item:hover{
    color:floralwhite;
    
}

.nav-link:hover{
    background-color: #00425A;
    color: #ffffff;
}

.nav-item:hover .nav-link {
    background-color: #00425A;
    color: #ffffff;
}

.bi-house-fill {
    color: #8fc8bd;
}


.bi-journal-text{
    color: #8fc8bd;
}

.bi-table{
    color: #8fc8bd;
}

.bi-book{
    color: #8fc8bd;
}

.bi-newspaper{
    color: #8fc8bd;
}


.bi-envelope{
    color: #8fc8bd;
}


.bi-box-arrow-left{
    color: #8fc8bd;
}


.bg-secondary{
    background-color: #1F8A70!important;
}

.login-logo{
    display: block;
    width: 40px;
    height: 40px;
    transform: translateY(32px);
}
    
.success {
    color: green;
}
     
    </style>

</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Admin Registration Form -->
                <div class="card shadow">
                    <img src="icons/online-learning.png" alt="Education Logo" class="logo">
                    <h2 class="text-center mb-4">Registration</h2>

                    <form method="post" action="adminRegisterBackend.php" class="form" name="adminRegistrationForm"
                        onsubmit="return validateForm()">
                        <label for="user_type">User Type:</label>
                        <select name="user_type" id="user_type" class="form-control mb-3" required>
                            <option value="" disabled selected>Select User Type</option>
                            <option value="student">Student</option>
                            <option value="tutor">Tutor</option>
                        </select>

                        <label for="first_name">First Name:</label>
                        <input type="text" name="first_name" 
id="first_name" class="form-control mb-3" required>

                        <label for="last_name">Last Name:</label>
                        <input type="text" name="last_name" id="last_name" class="form-control mb-3" required>

                        <label for="email">Email:</label>
                        <input type="email" name="email" class="form-control mb-3" required>

                        <label for="contact">Contact:</label>
                        <input type="number" name="contact" class="form-control mb-3" required>

                        <label for="password">Password:</label>
                        <input type="password" name="password" 
class="form-control mb-3" required>

                        <input type="hidden" name="component" value="adminRegister">

                        <button type="submit" class="btn btn-primary btn-action">Register</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript (optional, for certain components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function validateForm() {
            var firstName = document.getElementById('first_name').value;
            var lastName = document.getElementById('last_name').value;

            if (containsSymbols(firstName) || containsSymbols(lastName)) {
                alert("First name and last name cannot contain symbols.");
 return false;
            }
            return true;
        }

        function containsSymbols(str) {
            // Regular expression to check for symbols
            var pattern = /[!@#$%^&*(),.?":{}|<>]/;
            return pattern.test(str);
        }
    </script>
</body>

</html>