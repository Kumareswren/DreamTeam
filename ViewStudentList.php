<?php
session_start();
require_once('db.php'); // Include the database connection file
require_once('vendor/autoload.php'); // Include the JWT library

use \Firebase\JWT\JWT;

header("Content-Type: text/html"); // Set content type to HTML
    
    function generateStudentList($conn, $result) {
        $output = '<h2 class="mt-5 mb-4">My Student List</h2>'; // Add the heading
        $output .= '<table class="table">';
        $output .= '<thead>';
        $output .= '<tr>';
        $output .= '<th>First Name</th>';
        $output .= '<th>Last Name</th>';
        $output .= '<th>Email</th>';
        $output .= '<th>Contact</th>';
        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '<tbody>';

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $output .= '<tr>';
            $output .= '<td>' . (isset($row['FName']) ? $row['FName'] : '') . '</td>';
            $output .= '<td>' . (isset($row['LName']) ? $row['LName'] : '') . '</td>';
            $output .= '<td>' . (isset($row['Email']) ? $row['Email'] : '') . '</td>';
            $output .= '<td>' . (isset($row['Contact']) ? $row['Contact'] : '') . '</td>';
            $output .= '</tr>';
        }
    } else {
        $output .= '<tr><td colspan="4">No students found</td></tr>';
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
        $sqlTutor = "SELECT TID FROM tutor WHERE Email=?";
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
            $_SESSION['TID'] = $tid; // Assuming $tid holds the tutor's ID

            // Prepare SQL query to log system activity
            $activity_type = "Show Student list";
            $page_name = "tutorDashboard.php";
            $browser_name = $_SERVER['HTTP_USER_AGENT'];
            $user_id = $tid; // Assuming $tid holds the tutor's ID
            $user_type = "Tutor";
            
            $insert_query = "INSERT INTO SystemActivity (UserID, UserType, ActivityType, PageName, BrowserName) 
                             VALUES ('$user_id', '$user_type', '$activity_type', '$page_name', '$browser_name')";
            
            // Execute the query
            if ($conn->query($insert_query) !== TRUE) {
                // Handle error if insert query fails
                echo "Error inserting system activity: " . $conn->error;
            }
            // Query to get the students assigned to the tutor's TID
            $sql = "SELECT Student.* FROM Student 
                    INNER JOIN StudentAssignment ON Student.SID = StudentAssignment.SID 
                    WHERE StudentAssignment.TID = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Error in SQL query: " . $conn->error);
            }

            $stmt->bind_param("i", $tid);
            $stmt->execute();
            $result = $stmt->get_result();

            // Generate student list
            $studentListHTML = generateStudentList($conn, $result);

            // Close the database connection
            $conn->close();

            echo $studentListHTML; // Output the HTML table
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
    echo "Token not found.";
}
?>