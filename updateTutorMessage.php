<?php
// Assuming your database connection is established and stored in $conn
include_once 'db.php';
use \Firebase\JWT\JWT;
require_once('vendor/autoload.php'); 
session_start();



if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["message"]) && isset($_POST["sid"])) {
    // Retrieve message and SID from POST data
    $message = $_POST["message"];
    $sid = $_POST["sid"];

    // Retrieve the TID from the session
    /* $tid = $_SESSION["TID"]; */
    $token = $_COOKIE['token'];

// Decode the JWT token to extract the email
$decoded = JWT::decode($token, 'your_secret_key', array('HS256'));
$user_email = $decoded->email;

// Check if the user exists in the database
$sql_check_user = "SELECT * FROM Tutor WHERE Email = '$user_email'";
$result_check_user = $conn->query($sql_check_user);
$row = $result_check_user->fetch_assoc();
$tid = $row['TID'];

    // Insert message into database table
    $sql = "INSERT INTO Messages (TID, SID, messageContent, sender_type, receiver_type) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        // Assuming the sender is a Tutor and receiver is a Student
        $sender_type = "Tutor";
        $receiver_type = "Student";
        $stmt->bind_param("iisss", $tid, $sid, $message, $sender_type, $receiver_type);
        if ($stmt->execute()) {
            // Message inserted successfully
            echo "Message inserted successfully.";
        } else {
            // Error occurred while executing query
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Error occurred in preparing statement
        echo "Error: " . $conn->error;
    }
} else {
    // Invalid request or missing parameters
    echo "Invalid request.";
}
?>
