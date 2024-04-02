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
        $meetingDesc = $_POST['meeting_desc']; // Add this line to retrieve meeting description

        // Validate based on meeting location
if ($meetingLocation === 'Online Meeting') {
    // Debugging: Echo out the selected meeting location and meeting description
    echo "Selected Meeting Location: $meetingLocation <br>";
    echo "Meeting Description: $meetingDesc <br>";

    // If online meeting is selected, validate the meeting description
    if (!preg_match('/^[a-zA-Z\s.,!?-]+$/', $meetingDesc)) {
        // Meeting description contains invalid characters
        echo "<script>alert('Invalid meeting description. Only letters, spaces, and punctuation are allowed.');</script>";
        exit();
    }
} elseif ($meetingLocation === 'Physical Meeting') {
    // If physical meeting is selected, validate that a description is provided
    if (empty($meetingDesc)) {
        // Meeting description is required for physical meetings
        echo "<script>alert('Please provide a description for the physical meeting.');</script>";
        exit();
    }
}

// Retrieve JWT token from request (assuming it's passed as a header or form field)
$jwt_token = $_COOKIE['token'];

try {
    // Decode JWT token to get student's email
    $decodedToken = JWT::decode($jwt_token, 'your_secret_key', array('HS256'));
    $studentEmail = $decodedToken->email;

    // Retrieve SID using the student's email
    $sql = "SELECT SID FROM Student WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $studentEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $SID = $row['SID'];



        // Retrieve TID (assigned tutor ID) from StudentAssignment table using SID
        $sql = "SELECT TID FROM StudentAssignment WHERE SID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $SID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $TID = $row['TID'];

            // Retrieve assigned tutor's email using TID
            $sql = "SELECT Email FROM Tutor WHERE TID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $TID);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $assignedTutorEmail = $row['Email'];

                // Now you have the assigned tutor's email, you can use it to send an email
                // Send email to $assignedTutorEmail informing about the meeting request
                $transport = (new Swift_SmtpTransport('smtp.gmail.com', 587, 'tls')) // Replace with your SMTP server details
            ->setUsername('venturesrsk@gmail.com')
            ->setPassword('zohh take gpri knhn');

            //*here************************* */
        $mailer = new Swift_Mailer($transport);

        $message = (new Swift_Message('New Meeting Request'))
            ->setFrom(['venturesrsk@gmail.com' => 'System bot'])
            ->setTo([$assignedTutorEmail])
            ->setBody("You have a new meeting request! Please login to the dashboard to view more details.");

        // Send the message
        $result = $mailer->send($message);
            }

        // Insert the meeting request into the database
        $sql = "INSERT INTO MeetingStudent (courseTitle, meetingDate, meetingTime, meetingLocation, meetingDesc, TID, SID) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssii", $courseTitle, $meetingDate, $meetingTime, $meetingLocation, $meetingDesc, $TID, $SID);



        if ($stmt->execute()) {
            // Inform user about successful meeting request
            echo "<script>alert('Request meeting successfully.');</script>";
            unset($_SESSION['error_message_type']);
            unset($_SESSION['request_meeting_error']);
            echo "<script>clearForm();</script>";
            exit();
        } else {
            // Meeting request insertion failed, display error message
            echo "<script>alert('Failed to request meeting. Please try again later.');</script>";
            exit();
                        }
                    }
                }
        } catch (Exception $e) {
            // JWT token is invalid or expired
            echo "<script>alert('Invalid JWT token. Please login again.');</script>";
            exit();
        }
    } else {
        // Component other than 'requestMeeting' provided
        echo "<script>alert('Invalid form component.');</script>";
        exit();
    }
}

// Code for inserting meeting location options into the database (assuming you have it)
$meetingLocationOptions = array("Online Meeting", "Physical Meeting");

foreach ($meetingLocationOptions as $option) {
    $sql = "INSERT INTO MeetingLocationOptions (option_name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $option);
    $stmt->execute();
    $stmt->close();
}

// Close database connection
$conn->close();
?>

