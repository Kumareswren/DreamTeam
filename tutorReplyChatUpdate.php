<?php
session_start();
include_once 'db.php';
use \Firebase\JWT\JWT;
require_once('vendor/autoload.php'); 

$token = $_COOKIE['token'];

// Decode the JWT token to extract the email
$decoded = JWT::decode($token, 'your_secret_key', array('HS256'));
$user_email = $decoded->email;
$userId = $decoded->userId;
$userRole = $decoded->role;

// Check if the user exists in the database
$sql_check_user = "SELECT * FROM Tutor WHERE Email = '$user_email'";
$result_check_user = $conn->query($sql_check_user);
$row = $result_check_user->fetch_assoc();
$tid = $row['TID'];

// Check if SID and reply content are provided via POST data
if(isset($_POST['sid']) && isset($_POST['replyContent'])) {
    // Assign POST data to variables
    $sid = $_POST['sid'];
    $replyContent = $_POST['replyContent'];
    
    // Insert the message into the Messages table
    $insertQuery = "INSERT INTO Messages (TID, SID, messageContent, sender_type, receiver_type) VALUES (?, ?, ?, 'Tutor', 'Student')";
    $statement = mysqli_prepare($conn, $insertQuery);
    
    // Bind the parameters and execute the query
    mysqli_stmt_bind_param($statement, "iss", $tid, $sid, $replyContent);
    $result = mysqli_stmt_execute($statement);
    
    if($result) {
        // Insertion successful
        $response = '<div class="alert alert-success" role="alert">Reply sent successfully!</div>';
        
        // Fetch the student's first name from the student table
        $studentFirstName = "";
        $fetchQuery = "SELECT FName FROM Student WHERE SID = ?";
        $fetchStatement = mysqli_prepare($conn, $fetchQuery);
        mysqli_stmt_bind_param($fetchStatement, "i", $sid);
        mysqli_stmt_execute($fetchStatement);
        mysqli_stmt_bind_result($fetchStatement, $studentFirstName);
        mysqli_stmt_fetch($fetchStatement);
        mysqli_stmt_close($fetchStatement);
        
        // Construct the trail action message with the student's first name
        $trailAction = "Sent reply to student: $studentFirstName";
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        
        // Prepare and execute the SQL query to insert into trail table
        $trailSql = "INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?, ?, ?, ?)";
        $trailStmt = mysqli_prepare($conn, $trailSql);
        mysqli_stmt_bind_param($trailStmt, "isss", $userId, $userRole, $ipAddress, $trailAction);
        mysqli_stmt_execute($trailStmt);
    } else {
        // Insertion failed
        $response = '<div class="alert alert-danger" role="alert">Error: ' . mysqli_error($conn) . '</div>';
    }
} else {
    // SID or reply content not provided
    $response = '<div class="alert alert-danger" role="alert">Your credentials and/or your message was not provided</div>';
}

// Send the response as an HTTP request
http_response_code(200);
echo $response;
?>

