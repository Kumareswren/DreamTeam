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

$tutorQuery = "SELECT TID, FName, LName FROM Tutor";
$tutorResult = mysqli_query($conn, $tutorQuery);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Create Course</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Function to clear the form
        function clearForm() {
            document.forms["adminCreateCourseForm"].reset();
        }

    </script>

    <style>
        /* Add your custom styles here */
        body {
            background-color: #FFF6D9; /* Orange 50 */
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
    
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Admin Create Course Form -->
                <div class="card shadow">
                    <h2 class="text-center mb-4">Create Course</h2>

                    <?php if(isset($_SESSION['message_type']) && $_SESSION['message_type'] === 'error' && isset($_SESSION['course_creation_error'])): ?>
                        <p class="text-danger"><?php echo htmlspecialchars($_SESSION['course_creation_error']); ?></p>
                        <?php // Clear session variables after displaying error message ?>
                        <?php unset($_SESSION['message_type']); ?>
                        <?php unset($_SESSION['course_creation_error']); ?>
                    <?php endif; ?>

                    <form method="post" action="adminCreateCourseBackend.php" class="form" name="adminCreateCourseForm">
                        <label for="course_name">Course Name:</label>
                        <input type="text" name="course_name" class="form-control mb-3" required>

                        <label for="start_date">Start Date:</label>
                        <input type="month" name="start_date" class="form-control mb-3" required>

                        <label for="end_date">End Date:</label>
                        <input type="month" name="end_date" class="form-control mb-3" required>

                        <label for="course_description">Course Description:</label>
                        <textarea name="course_description" class="form-control mb-3" rows="3" required></textarea>

                        <select name="tutor_id" class="form-control mb-3" required>
                            <option value="">Select Tutor</option>
                            <?php
                            while ($tutorRow = mysqli_fetch_assoc($tutorResult)) {
                                $tutorID = $tutorRow['TID'];
                                $tutorName = $tutorRow['FName'] . ' ' . $tutorRow['LName'];
                                echo "<option value='$tutorID'>$tutorName</option>";
                            }
                            ?>
                        </select>

                        <button type="submit" class="btn btn-primary btn-action">Create Course</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript (optional, for certain components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
?>