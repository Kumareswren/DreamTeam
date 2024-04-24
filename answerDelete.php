<?php
// Include the database connection
include 'db.php';
require_once('vendor/autoload.php');

use \Firebase\JWT\JWT;

if(isset($_POST['tutorialAnswerId'])) {
    // Sanitize the tutorialAnswerId to prevent SQL injection
    $tutorialAnswerId = intval($_POST['tutorialAnswerId']);

    $token = $_COOKIE['token'];
    $secretKey = 'your_secret_key';
    $decoded = JWT::decode($token, $secretKey, array('HS256'));

    // Retrieve the tutorial answer title for logging purposes
    $sql2 = "SELECT tutorialAnswerTitle FROM TutorialAnswer WHERE tutorialAnswerID = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $tutorialAnswerId);
    $stmt2->execute();
    $stmt2->bind_result($tutorialAnswerTitle);
    $stmt2->fetch();
    $stmt2->close();

    // Prepare SQL statement to delete the tutorial answer
    $sql = "DELETE FROM TutorialAnswer WHERE tutorialAnswerID = ?";

    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind the tutorialAnswerId parameter
        $stmt->bind_param("i", $tutorialAnswerId);

        // Execute the statement
        if ($stmt->execute()) {
            // If deletion is successful, return success message
            echo "Tutorial answer deleted successfully.";

            // Log the deletion action
            $userId = $decoded->userId;
            $userRole = $decoded->role;
            $userIP = $_SERVER['REMOTE_ADDR'];
            $actionPerformed = 'Tutorial answer deleted: ' . $tutorialAnswerTitle;

            $sql = "INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $userId, $userRole, $userIP, $actionPerformed);
            $stmt->execute();
            $stmt->close();

        } else {
            // If deletion fails, return error message
            echo "Error: Unable to delete tutorial answer.";
        }

        // Close the statement
        $stmt->close();
    } else {
        // If statement preparation fails, return error message
        echo "Error: Unable to prepare statement.";
    }
} else {
    // If tutorialAnswerId is not set in the POST request, return error message
    echo "Error: Tutorial Answer ID is not provided.";
}

// Close the database connection
$conn->close();
?>
