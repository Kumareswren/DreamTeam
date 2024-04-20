<?php
session_start();

// Include necessary files and initialize the database connection
include "db.php";

// SwiftMailer for sending emails
require_once 'vendor/autoload.php';
use \Firebase\JWT\JWT;

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
        $studentEmailQuery = "SELECT email, FName FROM Student WHERE SID = (SELECT SID FROM MeetingStudent WHERE meetingID = ?)";
        $studentStmt = $conn->prepare($studentEmailQuery);
        if ($studentStmt) {
            $studentStmt->bind_param("i", $meetingID);
            $studentStmt->execute();
            $studentStmt->bind_result($studentEmail, $studentFName);
            $studentStmt->fetch();
    
            // Create and send email to the student
            $studentMessage = (new Swift_Message('Meeting Accepted'))
                ->setFrom(['venturesrsk@gmail.com' => 'System bot'])
                ->setTo([$studentEmail])
                ->setBody("Dear $studentFName, Your meeting request has been accepted. Please check your account for details.");
            $mailer->send($studentMessage);
    
            $studentStmt->close();
        }
    
        // Insert into trail table
        $trailAction = "Accepted meeting with student: $studentFName";
        insertTrailRecord($conn, $trailAction);
    } else {
        // Update the meeting status to 'declined'
        $updateQuery = "UPDATE MeetingStudent SET status = 'declined' WHERE meetingID = ?";
    
        // Send email to student about meeting rejection
        $studentEmailQuery = "SELECT email, FName FROM Student WHERE SID = (SELECT SID FROM MeetingStudent WHERE meetingID = ?)";
        $studentStmt = $conn->prepare($studentEmailQuery);
        if ($studentStmt) {
            $studentStmt->bind_param("i", $meetingID);
            $studentStmt->execute();
            $studentStmt->bind_result($studentEmail, $studentFName);
            $studentStmt->fetch();
    
            // Create and send email to the student
            $studentMessage = (new Swift_Message('Meeting Declined'))
                ->setFrom(['venturesrsk@gmail.com' => 'System bot'])
                ->setTo([$studentEmail])
                ->setBody("Dear $studentFName, Your meeting request has been declined. Please check your account for details.");
            $mailer->send($studentMessage);
    
            $studentStmt->close();
        }
    
        // Insert into trail table
        $trailAction = "Declined meeting with student: $studentFName";
        insertTrailRecord($conn, $trailAction);
    }
    // Prepare and execute the update query
    $stmt = $conn->prepare($updateQuery);
    if ($stmt) {
        $stmt->bind_param("i", $meetingID);
        if ($stmt->execute()) {
            // Meeting status updated successfully
            echo "<script>alert('Meeting update successfully.');</script>";
        } else {
            // Error executing update query
            echo "Error executing update query: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Error preparing update query
        echo "Error preparing update query: " . $conn->error;
    }

    exit();
} else {
    // Handle the error if the form is not submitted or the required fields are missing
    echo "Error: Invalid request.";
}

// Function to insert into trail table
function insertTrailRecord($conn, $trailAction) {
    $token = $_COOKIE['token'];
    $secretKey = 'your_secret_key';
    $decoded = JWT::decode($token, $secretKey, array('HS256'));
    $userId = $decoded->userId;
    $userRole = $decoded->role;
    $ipAddress = $_SERVER['REMOTE_ADDR'];

    // Prepare and execute the SQL query to insert into trail table
    $trailSql = "INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?, ?, ?, ?)";
    $trailStmt = $conn->prepare($trailSql);
    $trailStmt->bind_param("isss", $userId, $userRole, $ipAddress, $trailAction);
    if ($trailStmt->execute()) {
        // Trail record inserted successfully
        // You can handle success here if needed
    } else {
        // Error inserting into trail table
        echo "Error inserting into trail table: " . $trailStmt->error;
    }
    $trailStmt->close();
}
?>
