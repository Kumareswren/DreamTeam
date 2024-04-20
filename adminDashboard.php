<?php
require_once 'tokenVerify.php';
session_start();

include "db.php";

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

use \Firebase\JWT\JWT;

// Retrieve the JWT token from the cookie
$token = $_COOKIE['token'];

// Decode the JWT token to extract the email
$decoded = JWT::decode($token, 'your_secret_key', array('HS256'));
$user_email = $decoded->email;

$stmt = $conn->prepare("SELECT AID FROM Admin WHERE Email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $_SESSION['AID'] = $row['AID'];
}

// Check if the user exists in the database
$sql_check_user = "SELECT * FROM Admin WHERE Email = '$user_email'";
$result_check_user = $conn->query($sql_check_user);

$welcome_message = "";
$last_login_time = ""; // Initialize the variable to avoid errors

if ($result_check_user && $result_check_user->num_rows > 0) {
    // User exists in the database
    $row = $result_check_user->fetch_assoc();
    
    // Check if it's the user's first login (last login time is NULL)
    if ($row['last_login'] === null) {
        // Update last login time
        date_default_timezone_set('Asia/Kuala_Lumpur');
        $current_time = date('Y-m-d H:i:s');
        $update_query = "UPDATE Admin SET last_login = '$current_time' WHERE Email = '$user_email'";
        $conn->query($update_query);

        // Display welcome message for the first login
        $welcome_message = "Welcome to your Dashboard, " . $row['FName'] . "! This is your first login.";
    } else {
        // User has logged in before, fetch the last login time
        $last_login_time = $row['last_login'];
        $welcome_message = "Welcome back to your Dashboard, " . $row['FName'] . "!";
    }
} else {
    // User doesn't exist in the database
    $welcome_message = "Unknown User";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

   
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
 integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
 
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <title>Admin Dashboard</title>

    <style>

/* Styling for e-Tutor */ .min-vh-100 .fs-5 {
    color: rgb(255, 255, 255); 
    font-family: "garamond"; 
    
}

/* Styling for Sidebar items */  #menu .nav-link .d-none.d-sm-inline {
    color: #ffffff;
}

.custom-div {
    background-color: #FFF6D9;
    padding: 20px;
}

.nav-link {
    font-family: Arial, sans-serif; 
    font-size: 10px; 
    font-weight: 350; 
    padding: 20px 40px;
}

.nav-item:hover{
    color:floralwhite;
    
}

.nav-link:hover{
    background-color: #00425A;
    color: #ffffff;
}

.nav-item:hover > .nav-link {
    background-color: #00425A; /* Change the background color */
    color: #ffffff; /* Change the text color */
}

.submenu .nav-item:hover > .nav-link {
    background-color: #00425A; /* Set the background color to transparent */
    color: #ffffff; /* Inherit the text color */
}

.bi-people {
    color: #8fc8bd;
}

.bi-journal-text{
    color: #8fc8bd;
}

.bi-house-fill {
    color: #8fc8bd;
}


.bi-person-vcard{
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

.custom-div {
    background-color: #fff6d9;
    padding: 20px;
}

  </style>
</head>
<body>
    
<div class="container-fluid">
    <div class="row flex-nowrap">
        <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 bg-secondary">
            <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100">
                <a href="#" class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                    <span class="fs-5 d-none d-sm-inline">
                        <img src="icons/online-learning.png" alt="Education Logo" class="login-logo">
                        <span class="fs-5 d-none d-sm-inline etutor-text" style="margin-left: 50px;">e-Tutor</span>
                    </span>
                </a> <br>
                <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start" id="menu">
                    <li class="nav-item">
                        <a href="#" class="nav-link align-middle px-10 dashboard-link" data-aid="<?php echo $_SESSION['AID']; ?>">
                            <i class="fs-4 bi-house-fill"></i> <span class="ms-1 d-none d-sm-inline">Home</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link align-middle px-10 viewDashboard-link">
                            <i class="fs-4 bi-house-fill"></i> <span class="ms-1 d-none d-sm-inline">View Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link align-middle px-10">
                            <i class="fs-4 bi-people"></i> <span class="ms-1 d-none d-sm-inline">Students</span>
                        </a>
                        <!-- Submenu for Student tab -->
                        <ul class="submenu nav flex-column ms-4">
                            <li class="nav-item">
                                <a href="#" class="nav-link align-middle px-10 assignment-link">
                                    <i class="fs-4 bi-person-vcard"></i> <span class="ms-1 d-none d-sm-inline">New Assignment</span>
                                </a>
                            </li>
                        </ul>
                        <ul class="submenu nav flex-column ms-4">
                            <li class="nav-item">
                                <a href="#" class="nav-link align-middle px-10 reassignment-link">
                                    <i class="fs-4 bi-person-vcard"></i> <span class="ms-1 d-none d-sm-inline">Re-assignment</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link align-middle px-10">
                            <i class="fs-4 bi-person-vcard"></i> <span class="ms-1 d-none d-sm-inline">Tutors</span>
                        </a>
                    </li>

                    <li class="nav-item">
                                <a href="#" class="nav-link align-middle px-10 register-link">
                                    <i class="fs-4 bi-people"></i> <span class="ms-1 d-none d-sm-inline">Register User</span>
                                </a>
                            </li>

                    <li class="nav-item">
                                <a href="#" class="nav-link align-middle px-10 course-link">
                                <i class="fs-4 bi bi-journal-text"></i><span class="ms-1 d-none d-sm-inline">Create Course</span>
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a href="#" class="nav-link align-middle px-10 trail-link">
                                <i class="fs-4 bi bi-journal-text"></i><span class="ms-1 d-none d-sm-inline">Activities</span>
                                </a>
                            </li>

                    <li class="nav-item">
                        <a href="logout.php" class="nav-link align-middle px-10">
                            <i class="fs-4 bi-box-arrow-left"></i> <span class="ms-1 d-none d-sm-inline">Logout</span>
                        </a>
                    </li>
                </ul>
                <p>Last Login: <?php echo $last_login_time; ?></p> <!-- Display the last login time here -->
            </div>
        </div>
        <script>
            $(document).ready(function() {
                $('.dashboard-link').click(function(event) {
                    var userRole = "admin"; // Set the user role here, replace with actual user role
                    var aid = $(this).data('aid'); // Assuming $tid contains the TID value

                    $.ajax({
                        url: 'dashboard.php',
                        type: 'POST',
                        data: { user_role: userRole, userID: aid }, // Include tid in the data object
                        success: function(response) {
                            $('#componentContainer').html(response);
                        },
                        error: function(xhr, status, error) {
                            console.error('An error occurred:', error);
                        }
                    });
                });
            });
        </script>
        
            <script>
                $(document).ready(function() {
                    $('.viewDashboard-link').click(function(event) {
                        event.preventDefault();
                        $.ajax({
                            url: 'viewDashboardAdmin.php',
                            success: function(data) {
                                $('#componentContainer').html(data);
                            },
                            error: function(xhr, status, error) {
                                console.error('An error occurred:', error);
                            }
                        });

                    });
                });

                $(document).ready(function() {
                    $('.assignment-link').click(function(event) {
                        event.preventDefault();
                        $.ajax({
                            url: 'assStudentTutorComp.php',
                            success: function(data) {
                                $('#componentContainer').html(data);
                            },
                            error: function(xhr, status, error) {
                                console.error('An error occurred:', error);
                            }
                        });
                        $('#componentContainer').off('submit', 'form');
                        handleAssignmentFormSubmission();
                    });
                });

                function handleAssignmentFormSubmission() {
                    // Attach event listener to form submission
                    $('#alertMessageAssign').hide();
                    $('#componentContainer').on('submit', 'form', function(event) {
                        // Prevent default form submission
                        event.preventDefault();
                        // Collect form data
                        var formData = $(this).serialize();
                        
                        // Send form data to backend using AJAX
                        $.ajax({
                            type: 'POST',
                            url: 'assStudentTutorBackend.php',
                            data: formData,
                            success: function(response) {
                                // Handle the response
                                $('.alert').remove();
                                if (response === 'success') {
                                    alert("Assignment successful.");
                                    // Reload the component
                                    $.get('assStudentTutorComp.php', function(data) {
                                        $('.container').replaceWith(data); // Replace the container content with the updated one
                                    });
                                    
                                } else {
                                    $('<div id="alertMessageAssign" class="alert alert-danger" role="alert">' + response + '</div>').insertBefore('form').show();
                                }
                            },
                            error: function(xhr, status, error) {
                                // Handle errors
                                alert("An error occurred: " + error);
                            }
                        });
                    });
                }

                $(document).ready(function() {
                    $('.reassignment-link').click(function(event) {
                        event.preventDefault();
                        $.ajax({
                            url: 'reAssStudentTutorComp.php',
                            success: function(data) {
                                $('#componentContainer').html(data);
                            },
                            error: function(xhr, status, error) {
                                console.error('An error occurred:', error);
                            }
                        });
                        $('#componentContainer').off('submit', 'form');
                        handleReAssignmentFormSubmission();
                    });
                });

                function handleReAssignmentFormSubmission() {
                    // Attach event listener to form submission
                    $('#alertMessageAssign').hide();
                    $('#componentContainer').on('submit', 'form', function(event) {
                        // Prevent default form submission
                        event.preventDefault();
                        // Collect form data
                        var formData = $(this).serialize();
                        
                        // Send form data to backend using AJAX
                        $.ajax({
                            type: 'POST',
                            url: 'reAssStudentTutorBackend.php',
                            data: formData,
                            success: function(response) {
                                // Handle the response
                                $('.alert').remove();
                                if (response === 'success') {
                                    alert("Assignment successful.");
                                    // Reload the component
                                    $.get('reAssStudentTutorComp.php', function(data) {
                                        $('.container').replaceWith(data); // Replace the container content with the updated one
                                    });
                                    
                                } else {
                                    $('<div id="alertMessageAssign" class="alert alert-danger" role="alert">' + response + '</div>').insertBefore('form').show();
                                }
                            },
                            error: function(xhr, status, error) {
                                // Handle errors
                                alert("An error occurred: " + error);
                            }
                        });
                    });
                }

                
            </script>
            <script>
                    // Call the function when the register link is clicked
                    $(document).ready(function() {
                    $('.register-link').click(function(event) {
                        
                        event.preventDefault();
                        $.ajax({
                            url: 'adminRegister.php',
                            success: function(data) {
                                $('#componentContainer').html(data);
                            },
                            error: function(xhr, status, error) {
                                console.error('An error occurred:', error);
                            }
                        });
                        $('#componentContainer').off('submit', 'form');
                        handleRegisterFormSubmission();
                    });
                });

                function handleRegisterFormSubmission() {
                    // Attach event listener to form submission
                    $('#alertMessageRegister').hide();
                    $('#componentContainer').on('submit', 'form', function(event) {
                        // Prevent default form submission
                        event.preventDefault();
                        
                        // Collect form data
                        var formData = $(this).serialize();
                        // Send form data to backend using AJAX
                        $.ajax({
                            type: 'POST',
                            url: 'adminRegisterBackend.php',
                            data: formData,
                            success: function(response) {
                                // Handle the response
                                $('.alert').remove();
                                $('#alertMessage').hide();
                                if (response === 'success') {
                                    alert("Register successful.");
                                    // Reload the component
                                    $.get('adminRegister.php', function(data) {
                                        $('.container').replaceWith(data); // Replace the container content with the updated one
                                    });
                                } else {
                                    $('<div id="alertMessageRegister" class="alert alert-danger" role="alert">' + response + '</div>').insertBefore('form').show();
                                }
                            },
                            error: function(xhr, status, error) {
                                // Handle errors
                                alert("An error occurred: " + error);
                            }
                        });
                    });
                }
                // Call the function when the assignment link is clicked
                $(document).ready(function() {
                    $('.course-link').click(function(event) {
                        event.preventDefault();
                        $.ajax({
                            url: 'adminCreateCourse.php',
                            success: function(data) {
                                $('#componentContainer').html(data);
                            },
                            error: function(xhr, status, error) {
                                console.error('An error occurred:', error);
                            }
                        });
                        $('#componentContainer').off('submit', 'form');
                      handleCourseFormSubmission();
                    });
                });
                function handleCourseFormSubmission() {
                    // Attach event listener to form submission
                    $('#alertMessageCourse').hide();
                    $('#componentContainer').on('submit', 'form', function(event) {
                        // Prevent default form submission
                        event.preventDefault();
                        
                        // Collect form data
                        var formData = $(this).serialize();
                        // Send form data to backend using AJAX
                        $.ajax({
                            type: 'POST',
                            url: 'adminCreateCourseBackend.php',
                            data: formData,
                            success: function(response) {
                                // Handle the response
                                $('.alert').remove();
                                $('#alertMessage').hide();
                                if (response === 'success') {
                                    alert("Register successful.");
                                    // Reload the component
                                    $.get('adminCreateCourse.php', function(data) {
                                        $('.container').replaceWith(data); // Replace the container content with the updated one
                                    });
                                } else {
                                    $('<div id="alertMessageCourse" class="alert alert-danger" role="alert">' + response + '</div>').insertBefore('form').show();
                                }
                            },
                            error: function(xhr, status, error) {
                                // Handle errors
                                alert("An error occurred: " + error);
                            }
                        });
                    });
                }
            </script>
            <script>
            $(document).ready(function() {
                $('.trail-link').click(function(event) {
                    //event.preventDefault();
                    $.ajax({
                        url: 'selectUserTrail.php',
                        success: function(data) {
                                $('#componentContainer').html(data);
                            },
                            error: function(xhr, status, error) {
                                console.error('An error occurred:', error);
                            }
                    });
                
                    });
                });

            </script>

            <div class="col py-3 custom-div">
                
                <main class="mt-5 pt-3">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-md-12" id="componentContainer">

                        </div>
                      </div>
                      <br>

            </div>
        </div>
    </div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <!-- for charts? --> <script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.2/dist/chart.min.js"></script> 
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
     </body>
</html>