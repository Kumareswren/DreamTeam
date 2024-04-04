<?php
session_start();

// Check if the necessary key exists in the $_POST array
if (isset($_POST['courseId'])) {
    // Retrieve courseId from the POST data
    $courseId = $_POST['courseId'];

    // Set the courseId session variable
    $_SESSION['courseId'] = $courseId;

    /* echo "Session variable 'courseId' set successfully."; */
} else {
    // If the key is missing, display an error message
    echo "Error: Missing 'courseId' in POST data.";
}

