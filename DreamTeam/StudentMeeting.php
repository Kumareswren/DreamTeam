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
            document.forms["studentMeetingForm"].reset();
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
                <!-- Student Request Meeting Form -->
                <div class="card shadow">
                    <img src="icons/online-learning.png" alt="Education Logo" class="logo">
                    <h2 class="text-center mb-4">Request a Meeting</h2>

                    <?php if(isset($_SESSION['error_message_type']) && $_SESSION['error_message_type'] === 'error' && isset($_SESSION['request_meeting_error'])): ?>
                        <p class="text-danger"><?php echo htmlspecialchars($_SESSION['request_meeting_error']); ?></p>
                        <?php // Clear session variables after displaying error message ?>
                        <?php unset($_SESSION['error_message_type']); ?>
                        <?php unset($_SESSION['request_meeting_error']); ?>
                    <?php endif; ?>

                    <form method="post" action="studentMeetingBackend.php" class="form" name="studentMeetingForm">

                        <label for="course_title">Course Name:</label>
                        <input type="text" name="course_title" class="form-control mb-3" required>

                        <label for="meeting_date">Meeting Date:</label>
                        <input type="date" name="meeting_date" class="form-control mb-3" required>

                        <label for="meeting_time">Meeting Time:</label>
                        <input type="time" name="meeting_time" class="form-control mb-3" required>

                        
                        <label for="meeting_location">Meeting Options:</label>
                        <select name="meeting_location" class="form-control mb-3" required>
                        <option value="online">Online Meeting</option>
                        <option value="physical">Physical Meeting</option>
                        </select>


                        <label for="meeting_desc">Description:</label>
                        <input type="meeting_desc" name="meeting_desc" class="form-control mb-3" required>

                        <input type="hidden" name="component" value="requestMeeting">

                        <button type="submit" class="btn btn-primary btn-action">Request Meeting</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript (optional, for certain components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>