<?php
// Include database connection
require_once('db.php');
session_start();
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

// Retrieve course ID from AJAX request
$courseId = isset($_SESSION['courseId']) ? $_SESSION['courseId'] : '';

// Fetch students for the given course ID
echo courseStudents($courseId);
?>
