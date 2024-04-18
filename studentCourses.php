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
    
        // Query to get the SID for the student's email
        $sql = "SELECT SID FROM Student WHERE Email = ?";
        $stmt = $conn->prepare($sql);
    
        if (!$stmt) {
            die("Error in SQL query: " . $conn->error);
        }
    
        $stmt->bind_param("s", $email); // Bind email parameter
        $stmt->execute();
        $result = $stmt->get_result();
    
        // Check if any courses are assigned to the student
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $sid = $row['SID'];
            
// Set the SID in session
$_SESSION['SID'] = $row['SID'];

// Prepare SQL query to log system activity
$activity_type = "Show Courses";
$page_name = "studentDashboard.php";
$browser_name = $_SERVER['HTTP_USER_AGENT'];
$user_id = $sid; // Assuming $sid holds the student's ID
$user_type = "Student";

$insert_query = "INSERT INTO SystemActivity (UserID, UserType, ActivityType, PageName, BrowserName) 
                 VALUES ('$user_id', '$user_type', '$activity_type', '$page_name', '$browser_name')";
if ($conn->query($insert_query) !== TRUE) {
    // Handle error if insert query fails
    echo "Error inserting system activity: " . $conn->error;
}
        // Query to get the SID for the student's email
        $sql = "SELECT courseID FROM Coursestudent WHERE SID = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("Error in SQL query: " . $conn->error);
        }

        $stmt->bind_param("i", $sid);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if any courses are assigned to the student
        if ($result->num_rows > 0) {
            // Initialize an empty array to store course IDs
            $courses = array();
        
            // Fetch each courseID assigned to the student
            while ($row = $result->fetch_assoc()) {
                $courseID = $row['courseID'];
                // Store the course ID in the array
                $courses[] = $courseID;
            }
        
            // Query to get course details for the retrieved courseIDs
            $sql_course = "SELECT * FROM Course WHERE courseID IN (" . implode(",", $courses) . ")";
            $result_course = $conn->query($sql_course);
        
            if ($result_course->num_rows > 0) {
                // Generate HTML for the courses
                $courseListHTML = generateCourseList($conn, $result_course);
                // Echo the HTML
                echo $courseListHTML;
            } else {
                // No courses found with the given courseIDs
                echo "No courses found.";
            }
        } else {
            // No courses assigned to the student
            echo "No courses assigned to the student.";
        }
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
                        url: 'inCourseStudent.php',
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

