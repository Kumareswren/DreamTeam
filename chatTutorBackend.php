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

            // Query to get the TID for the tutor's email
            $sqlTutor = "SELECT TID FROM tutor WHERE Email=?";
            $stmtTutor = $conn->prepare($sqlTutor);
            if (!$stmtTutor) {
                die("Error in SQL query: " . $conn->error);
            }

            $stmtTutor->bind_param("s", $email);
            $stmtTutor->execute();
            $resultTutor = $stmtTutor->get_result();

            // Check if tutor found
            if ($resultTutor->num_rows > 0) {
                $rowTutor = $resultTutor->fetch_assoc();
                $tid = $rowTutor['TID'];

                // Retrieve SID from $_POST array
                if(isset($_POST['sid'])) {
                    $sid = $_POST['sid'];

                    // Update readStatus in Messages table where TID, SID, and sender_type match
                    $sqlUpdateReadStatus = "UPDATE Messages SET readStatus = 'Read' WHERE TID = ? AND SID = ? AND sender_type = 'Student'";
                    $stmtUpdateReadStatus = $conn->prepare($sqlUpdateReadStatus);
                    if (!$stmtUpdateReadStatus) {
                        die("Error in SQL query: " . $conn->error);
                    }
                    $stmtUpdateReadStatus->bind_param("ii", $tid, $sid);
                    $stmtUpdateReadStatus->execute();
                } else {
                    echo "Error: SID is not present.";
                    return;
                }

                // Query to get the students assigned to the tutor's TID
                $sql = "SELECT Student.* FROM Student 
                        INNER JOIN StudentAssignment ON Student.SID = StudentAssignment.SID 
                        WHERE StudentAssignment.TID = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Error in SQL query: " . $conn->error);
                }

                $stmt->bind_param("i", $tid);
                $stmt->execute();
                $result = $stmt->get_result();

                // Generate student list
                $students = $result->fetch_all(MYSQLI_ASSOC);

                // Close the database connection
      
            } else {
                // Tutor not found
                echo "Tutor not found.";
                return;
            }
        } catch (Exception $e) {
            // Token verification failed
            echo "Token verification failed.";
            return;
        }
    } else {
        // Token not found
        echo "Token not found.";
        return;
    }

  /*   if(isset($_POST['sid'])) { //this code block is to check if the SID is being sent, can comment it out if u want
        $sid = $_POST['sid'];
        echo "Success: SID {$sid} is present.";
    } else {
        echo "Error: SID is not present.";
    } */

// Query to fetch previous messages from the database
$sqlMessages = "SELECT sender_type, messageContent, sent_at FROM Messages WHERE SID = ?";
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
        
        $output .= '.student-list {';
        $output .= '  width: 100%;';
        $output .= '  border-right: 2px solid #333;';
        $output .= '  overflow-y: auto;'; // Changed from 'scroll' to 'auto'
        $output .= '  max-height: 100px;';
        $output .= '}';

        $output .= '.student-name {';
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
    $output .= '<div class="student-name">';
   /*  $output .= $sid; // Assuming $sid contains the student's name */

    $output .= '</div>';
    // Query to get the full name of the student based on SID
$sqlFullName = "SELECT FName, LName FROM Student WHERE SID = ?";
$stmtFullName = $conn->prepare($sqlFullName);
$stmtFullName->bind_param("i", $sid);
$stmtFullName->execute();
$resultFullName = $stmtFullName->get_result();

    // Check if student found
if ($resultFullName->num_rows > 0) {
    $rowFullName = $resultFullName->fetch_assoc();
    $fullName = $rowFullName['FName'] . ' ' . $rowFullName['LName'];
    $output .= '<div class="student-name">';
    $output .= $fullName; // Displaying full name of the student
    $output .= '</div>';
} else {
    $output .= '<div class="student-name">';
    $output .= 'Student Not Found'; // Display if student not found
    $output .= '</div>';
}

    foreach ($previousMessages as $message) {
        if ($message['sender_type'] == 'Tutor') {
            $senderClass = 'me'; // 'me' if sender is tutor
        } else {
            $senderClass = 'you'; // 'you' if sender is student
        }
        $output .= '<div class="chat-message">';
        $output .= "<div class='chat-bubble $senderClass'>{$message['messageContent']}";
        $output .= "<div class='sent-time'>$message[sent_at]</div>"; // Include sent_at timestamp within the chat-bubble div
        $output .= '</div>';
        $output .= '</div>';
    }
    


// JavaScript for handling student click event

$output .= '<script>';
$output .= '$(document).ready(function() {';
$output .= '  $("#searchStudent").on("input", function() {';
$output .= '    var searchText = $(this).val().toLowerCase();';
$output .= '    $(".student").each(function() {';
$output .= '      var studentName = $(this).text().toLowerCase();';
$output .= '      if (studentName.includes(searchText)) {';
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
$output .= '  $(".student").click(function() {';
$output .= '    var studentName = $(this).text();';
$output .= '    console.log("Selected student name: " + studentName);'; // Just for demonstration
$output .= '    $("#selectedStudentName").text(studentName);'; // Update the displayed student name
$output .= '  });';
$output .= '});';
$output .= '</script>';

    return $output;
}

// Call the function to generate the UI
echo Chat();
$conn->close();
?>
