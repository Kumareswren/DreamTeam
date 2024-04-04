<?php
session_start();

// Include necessary files and initialize the database connection
include "db.php";

// SwiftMailer for sending emails
require_once 'vendor/autoload.php';

// Check if the user is a tutor session (you may have a better way to check this)
$isTutor = true; // Set this based on your authentication logic

if (!$isTutor) {
    // Redirect to the login page or unauthorized access page
    header("Location: index.php");
    exit();
}

// Check if the form is submitted and the required fields are set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['meeting_id'], $_POST['action'])) {
    $meetingID = $_POST['meeting_id'];
    $action = $_POST['action'];

    // Initialize Swift Mailer for sending emails
    $transport = (new Swift_SmtpTransport('smtp.gmail.com', 587, 'tls')) // Replace with your SMTP server details
        ->setUsername('venturesrsk@gmail.com')
        ->setPassword('zohh take gpri knhn');

    $mailer = new Swift_Mailer($transport);

    if ($action === 'accept') {
        // Update the meeting status to 'accepted'
        $updateQuery = "UPDATE MeetingStudent SET status = 'accepted' WHERE meetingID = ?";

        // Send email to student about meeting acceptance
        $studentEmailQuery = "SELECT email FROM Student WHERE SID = (SELECT SID FROM MeetingStudent WHERE meetingID = ?)";
        $studentStmt = $conn->prepare($studentEmailQuery);
        if ($studentStmt) {
            $studentStmt->bind_param("i", $meetingID);
            $studentStmt->execute();
            $studentStmt->bind_result($studentEmail);
            $studentStmt->fetch();

            // Create and send email to the student
            $studentMessage = (new Swift_Message('Meeting Accepted'))
                ->setFrom(['venturesrsk@gmail.com' => 'System bot'])
                ->setTo([$studentEmail])
                ->setBody("Dear Student, Your meeting request has been accepted. Please check your account for details.");
            $mailer->send($studentMessage);

            $studentStmt->close();
        }
    } else {
        // Update the meeting status to 'declined'
        $updateQuery = "UPDATE MeetingStudent SET status = 'declined' WHERE meetingID = ?";

        // Send email to student about meeting rejection
        $studentEmailQuery = "SELECT email FROM Student WHERE SID = (SELECT SID FROM MeetingStudent WHERE meetingID = ?)";
        $studentStmt = $conn->prepare($studentEmailQuery);
        if ($studentStmt) {
            $studentStmt->bind_param("i", $meetingID);
            $studentStmt->execute();
            $studentStmt->bind_result($studentEmail);
            $studentStmt->fetch();

            // Create and send email to the student
            $studentMessage = (new Swift_Message('Meeting Declined'))
                ->setFrom(['venturesrsk@gmail.com' => 'System bot'])
                ->setTo([$studentEmail])
                ->setBody("Dear Student, Your meeting request has been declined. Please check your account for details.");
            $mailer->send($studentMessage);

            $studentStmt->close();
        }
    }

    $stmt = $conn->prepare($updateQuery);
    if ($stmt) {
        // Binding parameters
        $bindResult = $stmt->bind_param("i", $meetingID);
        if (!$bindResult) {
            $message = "Error binding parameters.";
        } else {
            // Execute the statement
            if ($stmt->execute()) {
                $message = "Meeting status updated successfully.";
                //echo "<script>alert('Meeting accepted successfully.');</script>";
            } else {
                $message = "Error executing update query: " . $stmt->error;
            }
        }
        $stmt->close();
    } else {
        $message = "Error preparing update query: " . $conn->error;
    }


    // Redirect back to the tutor meeting list page with a success message
  //  $_SESSION['message'] = $message;
   // header("Location: tutorMeeting.php");
   echo "<script>alert('Meeting update successfully.');</script>";
    exit();
} else {
    // Handle the error if the form is not submitted or the required fields are missing
    echo "Error: Invalid request.";
}
?>
