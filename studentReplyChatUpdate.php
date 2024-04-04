<?php
session_start();
include_once 'db.php';
use \Firebase\JWT\JWT;
require_once('vendor/autoload.php'); 

$token = $_COOKIE['token'];

// Decode the JWT token to extract the email
$decoded = JWT::decode($token, 'your_secret_key', array('HS256'));
$user_email = $decoded->email;

// Check if the user exists in the database
$sql_check_user = "SELECT * FROM Student WHERE Email = '$user_email'";
$result_check_user = $conn->query($sql_check_user);
$row = $result_check_user->fetch_assoc();
$sid = $row['SID'];

// Check if TID and reply content are provided via POST data
if(isset($_POST['tid']) && isset($_POST['replyContent'])) {
    // Assign POST data to variables
    $tid = $_POST['tid'];
    $replyContent = $_POST['replyContent'];
    
    // Insert the message into the Messages table
    $insertQuery = "INSERT INTO Messages (TID, SID, messageContent, sender_type, receiver_type) VALUES (?, ?, ?, 'Student', 'Tutor')";
    $statement = mysqli_prepare($conn, $insertQuery);
    
    // Bind the parameters and execute the query
    mysqli_stmt_bind_param($statement, "iss", $tid, $sid, $replyContent);
    $result = mysqli_stmt_execute($statement);
    
    if($result) {
        // Insertion successful
        $response = '<div class="alert alert-success" role="alert">Reply sent successfully!</div>';
    } else {
        // Insertion failed
        $response = '<div class="alert alert-danger" role="alert">Error: ' . mysqli_error($conn) . '</div>';
    }
} else {
    // TID or reply content not provided
    $response = '<div class="alert alert-danger" role="alert">Tutor ID and/or reply content was not provided</div>';
}

// Send the response as an HTTP request
http_response_code(200);
echo $response;
?>
