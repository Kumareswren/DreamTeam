<?php
include 'db.php'; // Include database connection
require_once('vendor/autoload.php');

use \Firebase\JWT\JWT;

if(isset($_POST['tutorialID'])) {
    $tutorialID = $_POST['tutorialID'];
    

    $token = $_COOKIE['token'];
    $secretKey = 'your_secret_key';
    $decoded = JWT::decode($token, $secretKey, array('HS256'));

    $sql2 = "SELECT tutorialTitle FROM tutorial WHERE tutorialID = ?";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("i", $tutorialID);
            $stmt2->execute();
            $stmt2->bind_result($tutorialName);

            // Fetch the result
            $stmt2->fetch();

            $stmt2->close();

    // Delete corresponding tutorial answers
    $sqlDeleteAnswers = "DELETE FROM TutorialAnswer WHERE tutorialID = $tutorialID";
    if ($conn->query($sqlDeleteAnswers) === TRUE) {
        // Tutorial answers deleted successfully, proceed to delete tutorial
        $sqlDeleteTutorial = "DELETE FROM Tutorial WHERE tutorialID = $tutorialID";
        if ($conn->query($sqlDeleteTutorial) === TRUE) {
            $userId = $decoded->userId;
            $userRole = $decoded->role;
            $userIP = $_SERVER['REMOTE_ADDR'];

            // Now $tutorialName should contain the fetched value
            $actionPerformed = 'Tutorial deleted: ' . $tutorialName;

            $sql = "INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $userId, $userRole, $userIP, $actionPerformed);
            $stmt->execute();
            $stmt->close();

            
        } else {
            echo "Error deleting tutorial: " . $conn->error;
        }
    } else {
        echo "Error deleting tutorial answers: " . $conn->error;
    }

    $conn->close();
}
?>
