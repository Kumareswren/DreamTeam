<?php
use \Firebase\JWT\JWT;
session_start();

// Include necessary files and initialize the database connection
include "db.php";
require_once('vendor/autoload.php'); // Include the JWT library

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $component = $_POST['component'];
    if ($component === 'requestMeeting') {
        // Retrieve form data
        $courseTitle = $_POST['course_title'];
        $meetingDate = $_POST['meeting_date'];
        $meetingTime = $_POST['meeting_time'];
        $meetingLocation = $_POST['meeting_location'];
        $meetingDesc = $_POST['meeting_desc'];

        // Insert the meeting request into the database
        $sql = "INSERT INTO MeetingStudent (courseTitle, meetingDate, meetingTime, meetingLocation, meetingDesc, TID) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $courseTitle, $meetingDate, $meetingTime, $meetingLocation, $meetingDesc, $tutorID);

        if ($stmt->execute()) {
            echo "<script>alert('Request meeting successfully.');</script>";
            //unset($_SESSION['error_message_type']);
           // unset($_SESSION['request_meeting_error']);
            
            echo "<script>clearForm();</script>";
            exit();
        } else {
            echo "<script>alert('Failed to request meeting. Please try again later.');</script>";
            exit();
        }

        $stmt->close();
        $conn->close();
    }
}

// Assuming you have an array of meeting location options
$meetingLocationOptions = array("Online Meeting", "Physical Meeting");

// Insert meeting location options into the database
foreach ($meetingLocationOptions as $option) {
    $sql = "INSERT INTO MeetingLocationOptions (option_name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $option);
    $stmt->execute();
    $stmt->close();
}

?>
