<?php
use \Firebase\JWT\JWT;

// Include necessary files and initialize the database connection
include "db.php";
require_once('vendor/autoload.php'); // Include the JWT library
session_start();

header("Content-Type: text/html"); // Set content type to HTML
    
    function generateMeetingList($conn, $resultList) {
        $output = '<h2 class="mt-5 mb-4">My Meeting Status</h2>'; // Add the heading
        $output .= '<input type="text" id="meetingSearchInput" placeholder="Search meetings..." class="form-control mb-3">';
        $output .= '<table class="table">';
        $output .= '<thead>';
        $output .= '<tr>';
        $output .= '<th>Meeting ID</th>';
        $output .= '<th>Course Name</th>';
        $output .= '<th>Date</th>';
        $output .= '<th>Time</th>';
        $output .= '<th>Location</th>';
        $output .= '<th>Description</th>';
        $output .= '<th>Status</th>';
        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '<tbody>';

    if ($resultList->num_rows > 0) {
        while ($row = $resultList->fetch_assoc()) {
            $output .= '<tr>';
            $output .= '<td>' . (isset($row['meetingID']) ? $row['meetingID'] : '') . '</td>';
            $output .= '<td>' . (isset($row['courseTitle']) ? $row['courseTitle'] : '') . '</td>';
            $output .= '<td>' . (isset($row['meetingDate']) ? $row['meetingDate'] : '') . '</td>';
            $output .= '<td>' . (isset($row['meetingTime']) ? $row['meetingTime'] : '') . '</td>';
            $output .= '<td>' . (isset($row['meetingLocation']) ? $row['meetingLocation'] : '') . '</td>';
            $output .= '<td>' . (isset($row['meetingDesc']) ? $row['meetingDesc'] : '') . '</td>';
            $output .= '<td>' . (isset($row['status']) ? $row['status'] : '') . '</td>';
            $output .= '</tr>';
        }
    } else {
        $output .= '<tr><td colspan="7">No meetings found</td></tr>';
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
        $sqlStudent = "SELECT SID FROM Student WHERE Email=?";
        $stmtStudent = $conn->prepare($sqlStudent);
        if (!$stmtStudent) {
            $_SESSION['message'] = "Error in SQL query: " . $conn->error;
            header("Location: studentMeetingList.php");
            exit();
        }

        $stmtStudent->bind_param("s", $email);
        $stmtStudent->execute();
        $resultStudent = $stmtStudent->get_result();

        // Check if student found
        if ($resultStudent->num_rows > 0) {
            $rowStudent = $resultStudent->fetch_assoc();
            $SID = $rowStudent['SID'];

            // Fetch meeting list for the student based on their SID
            $meetingQuery = "SELECT MS.meetingID, MS.courseTitle, MS.meetingDate, MS.meetingTime, MS.meetingLocation, MS.meetingDesc, MS.status
            FROM MeetingStudent AS MS
            INNER JOIN Tutor t ON MS.TID = t.TID
            WHERE MS.SID = ? AND MS.meetingDate >= CURDATE() 
            ORDER BY MS.meetingDate ASC, MS.meetingTime ASC";
            
            $stmtMeeting = $conn->prepare($meetingQuery);
            if (!$stmtMeeting) {
                die("Error in SQL query: " . $conn->error);
            }

            $stmtMeeting->bind_param("i", $SID);
            $stmtMeeting->execute();
            $meetingResult = $stmtMeeting->get_result();

            
            // Generate student list
            $studentMeetingHTML = generateMeetingList($conn, $meetingResult);


// Set the SID in session
$_SESSION['SID'] = $SID;

// Prepare SQL query to log system activity
$activity_type = "Upcoming Meeting";
$page_name = "studentDashboard.php";
$full_user_agent = $_SERVER['HTTP_USER_AGENT'];
 // Regular expression to extract the browser name
 if (preg_match('/Edg\/([\d.]+)/i', $full_user_agent, $matches)) {
    $browser_name = 'Edge';
} elseif (preg_match('/(Firefox|Chrome|Safari|Opera)/i', $full_user_agent, $matches)) {
    $browser_name = $matches[1];
} else {
    $browser_name = "Unknown"; // Default to "Unknown" if browser name cannot be determined
}

$user_id = $SID; // Assuming $SID holds the student's ID
$user_type = "Student";

$insert_query = "INSERT INTO SystemActivity (UserID, UserType, ActivityType, PageName, BrowserName) 
                 VALUES ('$user_id', '$user_type', '$activity_type', '$page_name', '$browser_name')";
if ($conn->query($insert_query) !== TRUE) {
    // Handle error if insert query fails
    echo "Error inserting system activity: " . $conn->error;
}
            // Close the database connection
            $conn->close();

            echo $studentMeetingHTML; // Output the HTML table

        } else {
            // Meeting List not found
            echo "Meeting list not found.";
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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$(document).ready(function() {
    $('#meetingSearchInput').on('keyup', function() {
        var searchText = $(this).val().toLowerCase();
        $('tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(searchText) > -1);
        });
    });
});
</script>