<?php
session_start();
include_once 'db.php';

if(isset($_POST['tutorialAnswerID']) && isset($_POST['tutorComment'])) { // Check if tutorialAnswerID and tutorComment are provided via POST data
    // Sanitize the inputs to prevent SQL injection
    $tutorialAnswerID = mysqli_real_escape_string($conn, $_POST['tutorialAnswerID']);
    $tutorComment = mysqli_real_escape_string($conn, $_POST['tutorComment']);
    
    $sql = "UPDATE TutorialAnswer SET tutorComment = '$tutorComment' WHERE tutorialAnswerID = $tutorialAnswerID";
    
    if(mysqli_query($conn, $sql)) {
        // Query executed successfully
        $response = array('status' => 'success', 'message' => 'Tutor comment updated successfully.');
        echo json_encode($response);
    } else {
        // Query execution failed
        $response = array('status' => 'error', 'message' => 'Error updating tutor comment: ' . mysqli_error($conn));
        echo json_encode($response);
    }
} else {
    $response = array('status' => 'error', 'message' => 'POST data not provided.');
    echo json_encode($response);
}

mysqli_close($conn);