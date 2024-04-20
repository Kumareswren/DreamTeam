<?php
// Include database connection
require_once('db.php');
require_once('vendor/autoload.php'); 
use \Firebase\JWT\JWT;

if (session_status() == PHP_SESSION_NONE) {
    // If not, start the session
    session_start();
}

if(isset($_COOKIE['token'])) {
    $token = $_COOKIE['token'];
    $secretKey = 'your_secret_key'; // Change to your actual secret key
    $decoded = JWT::decode($token, $secretKey, array('HS256'));

    $userId = $decoded->userId;
    $userRole = $decoded->role;
    $ipAddress = $_SERVER['REMOTE_ADDR'];

    if(isset($_POST['actionPerformed'])) {
        $actionPerformed = $_POST['actionPerformed'];

        $sql = "INSERT INTO Trail (userID, userRole, ip_address, actionPerformed)
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $userId, $userRole, $ipAddress, $actionPerformed);
        if($stmt->execute()) {
            echo "Record inserted successfully.";
        } else {
            echo "Error inserting record.";
        }
    } else {
        echo "Action performed data not received.";
    }
} else {
    echo "JWT token not found in the cookie.";
}
?>
