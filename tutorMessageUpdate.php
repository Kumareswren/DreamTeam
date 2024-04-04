<?php
session_start();
require_once('db.php'); 




$tid = $_POST['TID']; // Retrieve POST data
$sid = $_POST['SID']; 
$messageContent = $_POST['messageContent'];


$sql = "INSERT INTO Messages (TID, SID, messageContent) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $tid, $sid, $messageContent);
$result = $stmt->execute();




if (!$stmt) {
    // If  SQL query fails, error response
    echo json_encode(array('success' => false, 'message' => 'Error in SQL query preparation: ' . $conn->error));
    exit();
}

/* $receiver_id = $sid; */

// Bind parameters - execute the statement
/* $stmt->bind_param("iis", $TID, $receiver_id, $messageContent);
$result = $stmt->execute(); */

if (!$result) {
    // If the execution fails, return an error response
    echo json_encode(array('success' => false, 'message' => 'Error in executing SQL query: ' . $stmt->error));
    exit();
    
}

// If the execution is successful, return a success response
echo json_encode(array('success' => true, 'message' => 'Message sent successfully'));



$stmt->close();
$conn->close();
?>
