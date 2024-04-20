<?php
session_start();
include_once 'db.php';
require_once('vendor/autoload.php'); // Include the JWT library

use \Firebase\JWT\JWT;

if(isset($_POST['tutorialAnswerID']) && isset($_POST['tutorComment'])) { // Check if tutorialAnswerID and tutorComment are provided via POST data
    // Sanitize the inputs to prevent SQL injection
    $tutorialAnswerID = mysqli_real_escape_string($conn, $_POST['tutorialAnswerID']);
    $tutorComment = mysqli_real_escape_string($conn, $_POST['tutorComment']);
    
    // Prepare the SQL statement to update the tutor comment
    $updateSql = "UPDATE TutorialAnswer SET tutorComment = '$tutorComment' WHERE tutorialAnswerID = $tutorialAnswerID";
    
    // Execute the update query
    if(mysqli_query($conn, $updateSql)) {
        // Query executed successfully
        
        // Fetch the tutorialAnswerTitle using the provided tutorialAnswerID
        $fetchSql = "SELECT tutorialAnswerTitle FROM TutorialAnswer WHERE tutorialAnswerID = $tutorialAnswerID";
        $result = mysqli_query($conn, $fetchSql);
        
        if($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $tutorialAnswerTitle = $row['tutorialAnswerTitle'];

            $token = $_COOKIE['token'];
            $secretKey = 'your_secret_key';
            $decoded = JWT::decode($token, $secretKey, array('HS256'));
            
            // Insert a record into the trail table
            $actionPerformed = "Updated tutor comment for tutorial answer: $tutorialAnswerTitle";
            $userId = $decoded->userId;
            $userRole = $decoded->role;
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            
            // Prepare the SQL statement to insert into the trail table
            $trailSql = "INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) 
                         VALUES ($userId, '$userRole', '$ipAddress', '$actionPerformed')";
            
            // Execute the insert query
            mysqli_query($conn, $trailSql);
            
            // Send response
            $response = array('status' => 'success', 'message' => 'Tutor comment updated successfully.');
            echo json_encode($response);
        } else {
            // Error fetching tutorialAnswerTitle
            $response = array('status' => 'error', 'message' => 'Error fetching tutorial answer title.');
            echo json_encode($response);
        }
    } else {
        // Query execution failed
        $response = array('status' => 'error', 'message' => 'Error updating tutor comment: ' . mysqli_error($conn));
        echo json_encode($response);
    }
} else {
    // POST data not provided
    $response = array('status' => 'error', 'message' => 'POST data not provided.');
    echo json_encode($response);
}

// Close database connection
mysqli_close($conn);
