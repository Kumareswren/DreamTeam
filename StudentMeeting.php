<?php
session_start();

// Include necessary files and initialize the database connection
include "db.php";
require_once('vendor/autoload.php'); // Include the JWT library

use \Firebase\JWT\JWT;

// Retrieve JWT token from cookie
$jwt_token = $_COOKIE['token'];

// Function to decode JWT token and extract email
function getEmailFromToken($jwt_token) {
    $secretKey = 'your_secret_key'; // Replace with your secret key used for JWT encoding
    try {
        $decoded = JWT::decode($jwt_token, $secretKey, array('HS256'));
        return $decoded->email;
    } catch (Exception $e) {
        return null; // Return null if token is invalid
    }
}

// Get user's email from JWT token
$user_email = getEmailFromToken($jwt_token);

if ($user_email !== null) {
    // Query the Student table to retrieve SID based on email
    $sid_query = "SELECT SID FROM Student WHERE Email = ?";
    
    // Prepare the statement
    $stmt = $conn->prepare($sid_query);

    // Bind parameters and execute the statement
    $stmt->bind_param("s", $user_email); // Assuming email is stored as a string
    $stmt->execute();

    // Get the result of the executed statement
    $sid_result = $stmt->get_result();

    // Fetch SID
    if ($sid_row = $sid_result->fetch_assoc()) {
        $student_SID = $sid_row['SID'];

        // Query to fetch courses assigned to the student
        $course_query = "SELECT Course.courseName 
                         FROM Course 
                         INNER JOIN CourseStudent ON Course.courseID = CourseStudent.courseID 
                         WHERE CourseStudent.SID = ?";
         // Set the SID in session
    $_SESSION['SID'] = $student_SID;

    // Prepare SQL query to log system activity
    $activity_type = "Request Meeting";
    $page_name = "studentDashboard.php";
    $browser_name = $_SERVER['HTTP_USER_AGENT'];
    $user_id = $student_SID; // Assuming $student_SID holds the student's ID
    $user_type = "Student";

    $insert_query = "INSERT INTO SystemActivity (UserID, UserType, ActivityType, PageName, BrowserName) 
                     VALUES ('$user_id', '$user_type', '$activity_type', '$page_name', '$browser_name')";
    if ($conn->query($insert_query) !== TRUE) {
        // Handle error if insert query fails
        echo "Error inserting system activity: " . $conn->error;
    }

        // Prepare the statement
        $stmt = $conn->prepare($course_query);

        // Bind parameters and execute the statement
        $stmt->bind_param("i", $student_SID); // Assuming SID is stored as an integer
        $stmt->execute();

        // Get the result of the executed statement
        $course_result = $stmt->get_result();

        // Fetch course data into an associative array
        $courses = [];
        while ($row = $course_result->fetch_assoc()) {
            $courses[] = $row['courseName'];
        }

        // Now $courses array contains the names of courses assigned to the student
    } else {
        // Handle case when SID is not found for the provided email
        echo "Student not found for the provided email.";
    }
} else {
    // Handle case when JWT token is invalid or does not contain email
    echo "Invalid JWT token or missing email.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Request Meeting</title>
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

                        <!-- Course selection dropdown -->
                        <label for="course_title" class="form-label">Course Name:</label>
                        <select name="course_title" class="form-control" required>
                            <option value="">Select Course</option>
                            <?php foreach($courses as $course): ?>
                                <option value="<?php echo $course; ?>"><?php echo $course; ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label for="meeting_date">Meeting Date:</label>
                        <input type="date" name="meeting_date" class="form-control mb-3" id="meeting_date" required>

                        <label for="meeting_time">Meeting Time:</label>
                        <input type="time" name="meeting_time" class="form-control mb-3" id="meeting_time" required min="<?php echo date('H:i'); ?>">


                        
                        <label for="meeting_location">Meeting Options:</label>
                        <select name="meeting_location" class="form-control mb-3" required>
                        <option value="online">Online Meeting</option>
                        <option value="physical">Physical Meeting</option>
                        </select>


                        <label for="meeting_desc">Description:</label>
                        <input type="text" name="meeting_desc" id="meeting_desc" class="form-control mb-3" required>
                        <input type="hidden" name="component" value="requestMeeting">

                        <button type="submit" class="btn btn-primary btn-action">Request Meeting</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript (optional, for certain components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Get today's date
    var today = new Date().toISOString().split('T')[0];

    // Set the minimum value of the date input to today's date
    document.getElementById("meeting_date").min = today;

    // Function to update minimum time value based on selected date
    function updateMinTime() {
        var selectedDate = new Date(document.getElementById("meeting_date").value);
        var today = new Date();
        var currentHour = today.getHours();
        var currentMinute = today.getMinutes();

        // If the selected date is today, set the minimum time to the current time
        if (selectedDate.toDateString() === today.toDateString()) {
            var selectedHour = selectedDate.getHours();
            var selectedMinute = selectedDate.getMinutes();

            // Ensure that the selected time is not before the current time
            if (selectedHour < currentHour || (selectedHour === currentHour && selectedMinute < currentMinute)) {
                document.getElementById("meeting_time").min = currentHour + ":" + currentMinute;
            } else {
                document.getElementById("meeting_time").min = selectedHour + ":" + selectedMinute;
            }
        } else {
            // If the selected date is not today, allow any time
            document.getElementById("meeting_time").min = "00:00";
        }
    }

    // Add event listener to the date input field
    document.getElementById("meeting_date").addEventListener("change", updateMinTime);

    // Trigger the function initially to set the initial minimum time value
    updateMinTime();
</script>

</body>
</html>