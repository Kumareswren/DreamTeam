<?php
include 'db.php'; // Include database connection
require_once('vendor/autoload.php');

use \Firebase\JWT\JWT;


if(isset($_POST['noteID'])) {
    $noteID = $_POST['noteID'];
    
    $token = $_COOKIE['token'];
    $secretKey = 'your_secret_key';
    $decoded = JWT::decode($token, $secretKey, array('HS256'));

    $sql2 = "SELECT noteTitle FROM note WHERE noteID = ?";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("i", $noteID);
            $stmt2->execute();
            $stmt2->bind_result($noteName);

            // Fetch the result
            $stmt2->fetch();

            $stmt2->close();

    // Perform the deletion query
    $sql = "DELETE FROM Note WHERE noteID = $noteID";
    if ($conn->query($sql) === TRUE) {
            $userId = $decoded->userId;
            $userRole = $decoded->role;
            $userIP = $_SERVER['REMOTE_ADDR'];

            // Now $tutorialName should contain the fetched value
            $actionPerformed = 'Notes deleted: ' . $noteName;

            $sql = "INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $userId, $userRole, $userIP, $actionPerformed);
            $stmt->execute();
            $stmt->close();
    } else {
        echo "Error deleting note: " . $conn->error;
    }

    $conn->close();
}
?>
