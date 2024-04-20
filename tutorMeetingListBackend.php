<?php
use \Firebase\JWT\JWT;
session_start();

// Include necessary files and initialize the database connection
include "db.php";
require_once('vendor/autoload.php'); // Include the JWT library

// Check if the user is a tutor (you may have a better way to check this)
$isTutor = true; // Set this based on your authentication logic

if (!$isTutor) {
    // Redirect to the login page or unauthorized access page
    $_SESSION['message'] = "Unauthorized access.";
    header("Location: tutorMeetingList.php");
    exit();
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
            $_SESSION['message'] = "Error in SQL query: " . $conn->error;
            header("Location: tutorMeetingList.php");
            exit();
        }

        $stmtTutor->bind_param("s", $email);
        $stmtTutor->execute();
        $resultTutor = $stmtTutor->get_result();

        // Check if tutor found
        if ($resultTutor->num_rows > 0) {
            $rowTutor = $resultTutor->fetch_assoc();
            $tid = $rowTutor['TID'];

            // Fetch meeting list for the tutor based on their TID
            $meetingQuery = "SELECT ms.meetingDate, ms.meetingTime, ms.meetingLocation, ms.meetingDesc, s.fname, s.lname, ms.status
                             FROM MeetingStudent ms
                             INNER JOIN Student s ON ms.SID = s.SID
                             WHERE ms.TID = ?
                             ORDER BY ms.meetingDate DESC, ms.meetingTime DESC"; // Order by meeting date and time

            $stmtMeeting = $conn->prepare($meetingQuery);
            if (!$stmtMeeting) {
                $_SESSION['message'] = "Error in SQL query: " . $conn->error;
                header("Location: tutorMeetingList.php");
                exit();
            }

            $stmtMeeting->bind_param("i", $tid);
            $stmtMeeting->execute();
            $meetingResult = $stmtMeeting->get_result();

            // Check if meeting list fetched successfully
            if ($meetingResult->num_rows > 0) {
                // You can process the meeting list here if needed, but in this case, it's just redirecting back to the tutor meeting list page
                header("Location: tutorMeetingList.php");
                exit();
            } else {
                // No meetings found for the tutor
                $_SESSION['message'] = "No meetings found for the tutor.";
                header("Location: tutorMeetingList.php");
                exit();
            }

            $stmtMeeting->close();
        } else {
            // Tutor not found
            $_SESSION['message'] = "Tutor not found.";
            header("Location: tutorMeetingList.php");
            exit();
        }

        $stmtTutor->close();
    } catch (Exception $e) {
        // Token verification failed
        $_SESSION['message'] = "Token verification failed.";
        header("Location: tutorMeetingList.php");
        exit();
    }
} else {
    // Token not found
    $_SESSION['message'] = "Token not found.";
    header("Location: tutorMeetingList.php");
    exit();
}
?>
