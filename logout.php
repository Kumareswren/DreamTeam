<?php
include "db.php";
require_once('vendor/autoload.php'); // Include the JWT library

use \Firebase\JWT\JWT;

$token = $_COOKIE['token'];
$pastExpirationTime = time() - 3600;

// Check if token is provided in the request
if(isset($_COOKIE['token'])) {
    // Retrieve token from the query string
    $token = $_COOKIE['token'];

    // Decode the token to extract the payload
    try {
        $secretKey = 'your_secret_key';
        $decoded = JWT::decode($token, $secretKey, array('HS256'));

        // Check if the token is expired
        if ($decoded->time < time()) {
            // Token expired, redirect to login page
            header("Location: index.php?error=Token%20expired.%20Please%20login%20again");
            exit();
        } else {
            // Token not expired, blacklist the token and redirect to login page
            setcookie('token', '', $pastExpirationTime, '/');
            
            // Log the logout action
            $userId = $decoded->userId;
            $userRole = $decoded->role;
            $userIP = $_SERVER['REMOTE_ADDR'];
            $actionPerformed = 'Logged out';

            $sql = "INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $userId, $userRole, $userIP, $actionPerformed);
            $stmt->execute();
            $stmt->close();

            header("Location: index.php");
            //include logout message
            /* header("Location: index.php"); */
            exit();
        }
    } catch (Exception $e) {
        // Invalid token, redirect to login page
        header("Location: index.php?error=Invalid%20token.%20Please%20login%20again");
        exit();
    }
} else {
    // Token not provided, redirect to login page
    header("Location: index.php?error=Token%20not%20provided");
    exit();
}
?>