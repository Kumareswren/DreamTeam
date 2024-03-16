<?php

use Firebase\JWT\JWT;

// Include the JWT library if not already included
require_once 'vendor/autoload.php';

// Function to verify JWT token
function verifyToken($token, $secretKey) {
    try {
        // Verify the token
        $decoded = JWT::decode($token, $secretKey, array('HS256'));
        return $decoded;
    } catch (Exception $e) {
        // If an exception occurs (e.g., token is invalid), return false
        return false;
    }
}

// Check if the token cookie exist
if (isset($_COOKIE['token'])) {
    $token = $_COOKIE['token'];
    
    // Your secret key
    $secretKey = 'rNjde95IzZ9CEU1k94aRjHbOX1LvKgM+RX6iv8NfMm8=';
    
    // Verify the token
    $decodedToken = verifyToken($token, $secretKey);
    
    // If the token is not valid or not exist, redirect to index.php
    if (!$decodedToken) {
        header('Location: index.php');
        exit();
    }
} else {
    // If the token cookie doesn't exist, redirect to index.php
    header('Location: index.php');
    exit();
}