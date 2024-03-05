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
            
            header("Location: index.php?message=Logout%20successful");
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

