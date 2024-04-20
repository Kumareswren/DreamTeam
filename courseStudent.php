<?php
// Include database connection
require_once('db.php');
session_start();

// Include JWT library
require_once('vendor/autoload.php');
use \Firebase\JWT\JWT;

// Function to fetch students assigned to a course
function courseStudents($courseId) {
    global $conn;
    $output = '';

    // Query to fetch students assigned to the course
    $sql = "SELECT s.FName, s.Email
            FROM Student s 
            INNER JOIN CourseStudent cs ON s.SID = cs.SID
            WHERE cs.courseID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $courseId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Retrieve userId from JWT token
    $token = $_COOKIE['token'];
    $secretKey = 'your_secret_key';
    $decoded = JWT::decode($token, $secretKey, array('HS256'));
    $userId = $decoded->userId;
    $userRole = $decoded->role;
    $userIP = $_SERVER['REMOTE_ADDR'];
    $actionPerformed = 'Checking student list';
    
    $sql_insert = "INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("isss", $userId, $userRole, $userIP, $actionPerformed);
    $stmt_insert->execute();
    $stmt_insert->close();

    // Check if students found
    if ($result->num_rows > 0) {
        $output .= '<div class="table-responsive">';
        $output .= '<table class="table table-striped">';
        $output .= '<thead><tr><th>Name</th><th>Email</th></tr></thead>';
        $output .= '<tbody>';
        while ($row = $result->fetch_assoc()) {
            $output .= '<tr><td>' . $row['FName'] . '</td><td>' . $row['Email'] . '</td></tr>';
        }
        $output .= '</tbody>';
        $output .= '</table>';
        $output .= '</div>';
    } else {
        $output .= '<div class="alert alert-info" role="alert">No students assigned to this course.</div>';
    }

    return $output;
}

// Function to retrieve userId from JWT token
function getUserIdFromToken() {
    $token = $_COOKIE['token'];
    $secretKey = 'your_secret_key';
    try {
        $decoded = JWT::decode($token, $secretKey, array('HS256'));
        return $decoded->userId;
    } catch (Exception $e) {
        // Handle invalid token or other errors
        return null;
    }
}

// Retrieve course ID from AJAX request
$courseId = isset($_SESSION['courseId']) ? $_SESSION['courseId'] : '';

// Fetch students for the given course ID
echo courseStudents($courseId);
?>
