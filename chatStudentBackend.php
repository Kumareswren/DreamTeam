<?php
session_start();
require_once('db.php'); // Include the database connection file
require_once('vendor/autoload.php'); // Include the JWT library
echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>';
use \Firebase\JWT\JWT;
function Chat() {
    global $conn;
    // Retrieve the token from the cookie
    if (isset($_COOKIE['token'])) {
        $token = $_COOKIE['token'];

        // Decode the token to get the email 
        $secretKey = 'your_secret_key'; // Update with your secret key
        try {
            $decoded = JWT::decode($token, $secretKey, array('HS256'));
            $email = $decoded->email;

            // Query to get the SID for the student's email
            $sqlStudent = "SELECT SID FROM student WHERE Email=?";
            $stmtStudent = $conn->prepare($sqlStudent);
            if (!$stmtStudent) {
                die("Error in SQL query: " . $conn->error);
            }

            $stmtStudent->bind_param("s", $email);
            $stmtStudent->execute();
            $resultStudent = $stmtStudent->get_result();

            // Check if student found
            if ($resultStudent->num_rows > 0) {
                $rowStudent = $resultStudent->fetch_assoc();
                $sid = $rowStudent['SID'];
                /* echo "SID found: " . $sid;  */// Add this line to display the SID found

                // Retrieve TID from $_POST array
                if(isset($_POST['tid'])) {
                    $tid = $_POST['tid'];

                    // Update readStatus in Messages table where TID, SID, and sender_type match
                    $sqlUpdateReadStatus = "UPDATE Messages SET readStatus = 'Read' WHERE TID = ? AND SID = ? AND sender_type = 'Tutor'";
                    $stmtUpdateReadStatus = $conn->prepare($sqlUpdateReadStatus);
                    if (!$stmtUpdateReadStatus) {
                        die("Error in SQL query: " . $conn->error);
                    }
                    $stmtUpdateReadStatus->bind_param("ii", $tid, $sid);
                    $stmtUpdateReadStatus->execute();
                } else {
                    echo "Error: TID is not present.";
                    return;
                }

                // Query to get the tutors assigned to the student's SID
                $sql = "SELECT Tutor.* FROM Tutor 
                        INNER JOIN StudentAssignment ON Tutor.TID = StudentAssignment.TID 
                        WHERE StudentAssignment.SID = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Error in SQL query: " . $conn->error);
                }

                $stmt->bind_param("i", $sid);
                $stmt->execute();
                $result = $stmt->get_result();

                // Generate tutor list
                $tutors = $result->fetch_all(MYSQLI_ASSOC);

                // Close the database connection
            } else {
                // Student not found
                echo "Student not found.";
                return;
            }
        } catch (Exception $e) {
            // Token verification failed
            echo "Token verification failed.";
            return;
        }
    } else {
        // Token not found
        echo "Token not found.";
        return;
    }

   /*  if(isset($_POST['tid'])) { //this code block is to check if the TID is being sent, can comment it out if u want
        $tid = $_POST['tid'];
        echo "Success: TID {$tid} is present.";
    } else {
        echo "Error: TID is not present.";
    } */

// Query to fetch previous messages from the database
$sqlMessages = "SELECT sender_type, messageContent, sent_at FROM Messages WHERE SID= ?";
$stmtMessages = $conn->prepare($sqlMessages);
$stmtMessages->bind_param("i", $sid);
$stmtMessages->execute();
$resultMessages = $stmtMessages->get_result();
$previousMessages = [];
while ($row = $resultMessages->fetch_assoc()) {
    $previousMessages[] = $row;
} 

    $output = '<style>';
    $output .= '.chat-container {';
        $output .= '  display: flex;';
        $output .= '  flex-direction: column;';
        $output .= '  max-width: 800px;';
        $output .= '  margin: auto;';
        $output .= '  border: 2px solid #333;';
        $output .= '  border-radius: 10px;';
        $output .= '  overflow: hidden;';
        $output .= '  font-family: Arial, sans-serif;';
        $output .= '  font-size: 16px;';
        $output .= '}';
        
        $output .= '.tutor-list {';
        $output .= '  width: 100%;';
        $output .= '  border-right: 2px solid #333;';
        $output .= '  overflow-y: auto;'; // Changed from 'scroll' to 'auto'
        $output .= '  max-height: 100px;';
        $output .= '}';

        $output .= '.tutor-name {';
            $output .= '  padding: 10px;';
            $output .= '  border-bottom: 2px solid #333;';
            $output .= '  background-color: #f9f9f9;';
            $output .= '  text-align: center;';
            $output .= '  font-weight: bold;';
            $output .= '}';

        $output .= '.chat-box {';
        $output .= '  width: 100%;';
        $output .= '  height: 550px;';
        $output .= '  overflow-y: auto;'; // Changed from 'scroll' to 'auto'
        $output .= '  padding: 10px;';
        $output .= '  background-color: #f9f9f9;';
        $output .= '}';
    
    $output .= '.chat-message {';
        $output .= '  padding: 10px;';
        $output .= '  clear: both;';
        $output .= '}';

        $output .= '.sent-time {';
            $output .= '  align-self: flex-end;';
            $output .= '  font-size: 0.8em;';
            $output .= '  color: #000000;';
            $output .= '}';
        
        $output .= '.chat-bubble {';
        $output .= '  display: inline-block;';
        $output .= '  margin-bottom: 10px;';
        $output .= '  padding: 15px;';
        $output .= '  border-radius: 20px;';
        $output .= '  color: #fff;';
        $output .= '  word-wrap: break-word;';
        $output .= '}';
        
        $output .= '.you {';
        $output .= '  float: left;';
        $output .= '  text-align: left;';
        $output .= '  background-color: #007bff;';
        $output .= '}';
        
        $output .= '.me {';
        $output .= '  float: right;';
        $output .= '  text-align: right;';
        $output .= '  background-color: #28a745;';
        $output .= '}';
    
        $output .= '.chat-input {';
            $output .= '  display: flex;'; // Use flexbox to align items
            $output .= '  align-items: center;'; // Center items vertically
            $output .= '  padding: 10px;';
            $output .= '  border-top: 2px solid #333;';
            $output .= '}';

$output .= '.chat-input textarea {';
$output .= '  flex: 1;'; // Take remaining space
$output .= '  height: auto;';
$output .= '  padding: 10px;';
$output .= '  border: 2px solid #333;';
$output .= '  border-radius: 10px;';
$output .= '  margin-right: 5px;';
$output .= '  overflow: hidden;';
$output .= '}';

$output .= '.send-btn {';
    $output .= '  padding: 10px;';
    $output .= '  border: none;';
    $output .= '  border-radius: 10px;';
    $output .= '  background-color: #007bff;';
    $output .= '  color: #fff;';
    $output .= '  cursor: pointer;';
    $output .= '  font-weight: bold;';
    $output .= '}';
    

    $output .= '.send-btn:hover {';
        $output .= '  background-color: #0056b3;';
        $output .= '}';

    $output .= '.search-box {';
    $output .= '  width: 100%;';
    $output .= '  padding: 10px;';
    $output .= '  border-bottom: 2px solid #333;';
    $output .= '}';
    
    $output .= '.search-box input[type="text"] {';
    $output .= '  width: 100%;';
    $output .= '  padding: 10px;';
    $output .= '  border: 2px solid #333;';
    $output .= '  border-radius: 10px;';
    $output .= '}';
    $output .= '</style>';

    $output .= '<div class="chat-container">';
    $output .= '<div class="chat-box" id="chatBox">';
    $output .= '<div class="tutor-name">';
   /*  $output .= $tid; // Assuming $tid contains the tutor's name */

    $output .= '</div>';
    // Query to get the full name of the tutor based on TID
$sqlFullName = "SELECT FName, LName FROM Tutor WHERE TID = ?";
$stmtFullName = $conn->prepare($sqlFullName);
$stmtFullName->bind_param("i", $tid);
$stmtFullName->execute();
$resultFullName = $stmtFullName->get_result();

    // Check if tutor found
if ($resultFullName->num_rows > 0) {
    $rowFullName = $resultFullName->fetch_assoc();
    $fullName = $rowFullName['FName'] . ' ' . $rowFullName['LName'];
    $output .= '<div class="tutor-name">';
    $output .= $fullName; // Displaying full name of the tutor
    $output .= '</div>';
} else {
    $output .= '<div class="tutor-name">';
    $output .= 'Tutor Not Found'; // Display if tutor not found
    $output .= '</div>';
}

    foreach ($previousMessages as $message) {
        if ($message['sender_type'] == 'Student') {
            $senderClass = 'me'; // 'me' if sender is student
        } else {
            $senderClass = 'you'; // 'you' if sender is tutor
        }
        $output .= '<div class="chat-message">';
        $output .= "<div class='chat-bubble $senderClass'>{$message['messageContent']}";
        $output .= "<div class='sent-time'>$message[sent_at]</div>"; // Include sent_at timestamp within the chat-bubble div
        $output .= '</div>';
        $output .= '</div>';
    }
    


// JavaScript for handling tutor click event

$output .= '<script>';
$output .= '$(document).ready(function() {';
$output .= '  $("#searchTutor").on("input", function() {';
$output .= '    var searchText = $(this).val().toLowerCase();';
$output .= '    $(".tutor").each(function() {';
$output .= '      var tutorName = $(this).text().toLowerCase();';
$output .= '      if (tutorName.includes(searchText)) {';
$output .= '        $(this).show();';
$output .= '      } else {';
$output .= '        $(this).hide();';
$output .= '      }';
$output .= '    });';
$output .= '  });';
$output .= '});';
$output .= '</script>';

$output .= '<script>';
$output .= '$(document).ready(function() {';
$output .= '  $(".tutor").click(function() {';
$output .= '    var tutorName = $(this).text();';
$output .= '    console.log("Selected tutor name: " + tutorName);'; // Just for demonstration
$output .= '    $("#selectedTutorName").text(tutorName);'; // Update the displayed tutor name
$output .= '  });';
$output .= '});';
$output .= '</script>';

    return $output;
}

// Call the function to generate the UI
echo Chat();
$conn->close();
?>

