<?php

session_start();
// Include database connection
include "db.php";

// Include Swift Mailer library
require_once 'vendor/autoload.php';
use Firebase\JWT\JWT;

// Create the Transport
$transport = (new Swift_SmtpTransport('smtp.gmail.com', 587, 'tls'))
    ->setUsername('venturesrsk@gmail.com')
    ->setPassword('zohh take gpri knhn');

// Create the Mailer using your created Transport
$mailer = new Swift_Mailer($transport);

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Extract the assignments array from the form data
    if (isset($_POST['assignments']) && is_array($_POST['assignments'])) {
        // Prepare an array to store valid student-tutor assignments
        $validAssignments = array();

        // Loop through the submitted assignments
        foreach ($_POST['assignments'] as $studentID => $tutorID) {
            // Check if a tutor is selected for the student
            if (!empty($tutorID)) {
                // Add the student-tutor pair to the valid assignments array
                $validAssignments[] = array('studentID' => $studentID, 'tutorID' => $tutorID);

                // Perform insertion of valid assignments into the database
                // Prepare the SQL statement
                $insertQuery = "INSERT INTO StudentAssignment (SID, TID) VALUES (?, ?)";
                // Prepare the statement
                $stmt = mysqli_prepare($conn, $insertQuery);
                // Bind parameters and execute the statement for each valid assignment
                mysqli_stmt_bind_param($stmt, "ii", $studentID, $tutorID);
                mysqli_stmt_execute($stmt);
                // Close the statement
                mysqli_stmt_close($stmt);

                // Log to trail table
                $studentNameQuery = "SELECT FName FROM Student WHERE SID = ?";
                $studentNameStmt = mysqli_prepare($conn, $studentNameQuery);
                mysqli_stmt_bind_param($studentNameStmt, "i", $studentID);
                mysqli_stmt_execute($studentNameStmt);
                mysqli_stmt_bind_result($studentNameStmt, $studentFName);
                mysqli_stmt_fetch($studentNameStmt);
                mysqli_stmt_close($studentNameStmt); // Close the statement after fetching results

                // Retrieve the first name of the tutor
                $tutorNameQuery = "SELECT FName FROM Tutor WHERE TID = ?";
                $tutorNameStmt = mysqli_prepare($conn, $tutorNameQuery);
                mysqli_stmt_bind_param($tutorNameStmt, "i", $tutorID);
                mysqli_stmt_execute($tutorNameStmt);
                mysqli_stmt_bind_result($tutorNameStmt, $tutorFName);
                mysqli_stmt_fetch($tutorNameStmt);
                mysqli_stmt_close($tutorNameStmt);

                // Construct the trail action with first names
                $trailAction = "Assigned student $studentFName to tutor $tutorFName";
                $token = $_COOKIE['token'];
                $secretKey = 'your_secret_key';
                $decoded = JWT::decode($token, $secretKey, array('HS256'));
                $userId = $decoded->userId;
                $userRole = $decoded->role;
                $ipAddress = $_SERVER['REMOTE_ADDR'];
                
                $trailSql = "INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?, ?, ?, ?)";
                $trailStmt = $conn->prepare($trailSql);
                $trailStmt->bind_param("isss", $userId, $userRole, $ipAddress, $trailAction);
                $trailStmt->execute();
                mysqli_stmt_close($trailStmt);

                // Retrieve email of the student
                $studentEmailQuery = "SELECT email FROM Student WHERE SID = ?";
                $studentStmt = mysqli_prepare($conn, $studentEmailQuery);
                mysqli_stmt_bind_param($studentStmt, "i", $studentID);
                mysqli_stmt_execute($studentStmt);
                mysqli_stmt_bind_result($studentStmt, $studentEmail);
                mysqli_stmt_fetch($studentStmt);
        
                // Create and send email to the student
                $studentMessage = (new Swift_Message('Assignment Successful'))
                    ->setFrom(['venturesrsk@gmail.com' => 'System bot'])
                    ->setTo([$studentEmail])
                    ->setBody("Dear Student, Your assignment was successful. Please check your account for details.");
                $mailer->send($studentMessage);
        
                // Close the statement
                mysqli_stmt_close($studentStmt);
        
                // Retrieve email of the tutor
                $tutorEmailQuery = "SELECT email FROM Tutor WHERE TID = ?";
                $tutorStmt = mysqli_prepare($conn, $tutorEmailQuery);
                mysqli_stmt_bind_param($tutorStmt, "i", $tutorID);
                mysqli_stmt_execute($tutorStmt);
                mysqli_stmt_bind_result($tutorStmt, $tutorEmail);
                mysqli_stmt_fetch($tutorStmt);
        
                // Create and send email to the tutor
                $tutorMessage = (new Swift_Message('New Assignment'))
                    ->setFrom(['venturesrsk@gmail.com' => 'System bot'])
                    ->setTo([$tutorEmail])
                    ->setBody("Dear Tutor, You have a new assignment. Please check your account for details.");
                $mailer->send($tutorMessage);
        
                // Close the statement
                mysqli_stmt_close($tutorStmt);
            }
        }

        // Return a success message
        echo "success";
        exit();
    } else {
        // No valid assignments to insert, handle accordingly (e.g., show error message)
        echo "No valid assignments to insert.";
        exit();
    }
}
?>
