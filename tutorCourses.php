<?php
session_start();
require_once('db.php'); // Include the database connection file
require_once('vendor/autoload.php'); // Include the JWT library

use \Firebase\JWT\JWT;

header("Content-Type: text/html"); // Set content type to HTML

function generateCourseList($conn, $result) {
    $output = '<h2 class="mt-5 mb-4">Courses List</h2>'; // Add the heading
    $output .= '<table class="table">';
    $output .= '<thead>';
    $output .= '<tr>';
    $output .= '<th>Course Name</th>';
    $output .= '<th>Start Date</th>';
    $output .= '<th>End Date</th>';
    $output .= '<th>Course Description</th>';
    $output .= '</tr>';
    $output .= '</thead>';
    $output .= '<tbody>';

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $output .= '<tr class="course-row" data-courseid="' . $row['courseID'] . '" data-coursename="' . $row['courseName'] . '" data-startdate="' . $row['startDate'] . '" data-enddate="' . $row['endDate'] . '">';
            $output .= '<td>' . (isset($row['courseName']) ? $row['courseName'] : '') . '</td>';
            $output .= '<td>' . (isset($row['startDate']) ? $row['startDate'] : '') . '</td>';
            $output .= '<td>' . (isset($row['endDate']) ? $row['endDate'] : '') . '</td>';
            $output .= '<td>' . (isset($row['courseDesc']) ? $row['courseDesc'] : '') . '</td>';
            $output .= '</tr>';
        }
    } else {
        $output .= '<tr><td colspan="4">No courses found</td></tr>';
    }

    $output .= '</tbody>';
    $output .= '</table>';

    return $output;
}

// Retrieve the token from the cookie
if (isset($_COOKIE['token'])) {
    $token = $_COOKIE['token'];

    // Decode the token to get the email 
    $secretKey = 'your_secret_key'; // Update with your secret key
    try {
        $decoded = JWT::decode($token, $secretKey, array('HS256'));
        $email = $decoded->email;

        // Query to get the TID for the tutor's email
        $sqlTutor = "SELECT TID FROM Tutor WHERE Email=?";
        $stmtTutor = $conn->prepare($sqlTutor);
        if (!$stmtTutor) {
            die("Error in SQL query: " . $conn->error);
        }

        $stmtTutor->bind_param("s", $email);
        $stmtTutor->execute();
        $resultTutor = $stmtTutor->get_result();

        // Check if tutor found
        if ($resultTutor->num_rows > 0) {
            $rowTutor = $resultTutor->fetch_assoc();
            $tid = $rowTutor['TID'];
// Set the TID in session
$_SESSION['TID'] = $tid;

// Prepare SQL query to log system activity
$activity_type = "Show Courses";
$page_name = "tutorDashboard.php";
$browser_name = $_SERVER['HTTP_USER_AGENT'];
$user_id = $tid; 
$user_type = "Tutor";

$insert_query = "INSERT INTO SystemActivity (UserID, UserType, ActivityType, PageName, BrowserName) 
                 VALUES ('$user_id', '$user_type', '$activity_type', '$page_name', '$browser_name')";
if ($conn->query($insert_query) !== TRUE) {
    // Handle error if insert query fails
    echo "Error inserting system activity: " . $conn->error;
}
            // Query to get the courses assigned to the tutor's TID
            $sql = "SELECT * FROM Course WHERE TID = ?";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                die("Error in SQL query: " . $conn->error);
            }

            $stmt->bind_param("i", $tid);
            $stmt->execute();
            $result = $stmt->get_result();

            // Generate course list
            $courseListHTML = generateCourseList($conn, $result);

            // Close the database connection
            $conn->close();

            echo $courseListHTML; // Output the HTML table
        } else {
            // Tutor not found
            echo "Tutor not found.";
        }
    } catch (Exception $e) {
        // Token verification failed
        echo "Token verification failed.";
    }
} else {
    // Token not found
    echo "Token not found.";
}
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jsonwebtoken/8.5.1/jsonwebtoken.min.js"></script>

<script>
    $(document).ready(function() {
        // Add click event listener to course rows
        $(document).on('click', '.course-row', function() {
            console.log('Course row clicked');
            // Retrieve course details from the row
            const courseId = $(this).data('courseid');
            const courseName = $(this).data('coursename');
            const startDate = $(this).data('startdate');
            const endDate = $(this).data('enddate');

            // AJAX request to set session variables
            $.ajax({
                url: 'setCourseSession.php', // Path to the PHP script that sets session variables
                type: 'POST', // Use POST method to send data to server
                data: {
                    courseId: courseId,
                    courseName: courseName,
                    startDate: startDate,
                    endDate: endDate
                },
                success: function(response) {
                    console.log('Session variables set successfully.');
                    // AJAX request to load the inCourse.php component
                    $.ajax({
                        url: 'inCourse.php',
                        type: 'GET',
                        success: function(response) {
                            // Replace the content of componentContainer with inCourse.php content
                            $('#componentContainer').html(response);
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error setting session variables:', error);
                }
            });
        });
    });
</script>

