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
                $conn->close();
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

    // Simulated previous messages
    $previousMessages = [
        ['sender' => 'student', 'message' => 'Hi, I need help with my assignment.'],
        ['sender' => 'tutor', 'message' => 'Sure, I can help you with that.'],
        ['sender' => 'student', 'message' => 'Hi, I need help with my assignment.'],
        ['sender' => 'tutor', 'message' => 'Sure, I can help you with that.'],    ['sender' => 'student', 'message' => 'Hi, I need help with my assignment.'],
        ['sender' => 'tutor', 'message' => 'Sure, I can help you with that.'],
        ['sender' => 'student', 'message' => 'Great! When can we discuss?']
    ];

// Remove the hard-coded student list
/*
    //  list of students
    $students = [
        ['SID' => 1, 'FName' => 'John', 'LName' => 'Doe'],
        ['SID' => 2, 'FName' => 'Jane', 'LName' => 'Smith'],
        ['SID' => 3, 'FName' => 'Alice', 'LName' => 'Johnson'],
        ['SID' => 4, 'FName' => 'John', 'LName' => 'Doe'],
        ['SID' => 5, 'FName' => 'Jane', 'LName' => 'Smith'],
        ['SID' => 6, 'FName' => 'John', 'LName' => 'Doe'],
        ['SID' => 7, 'FName' => 'Jane', 'LName' => 'Smith'],
        
 ];
*/
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
        $output .= '  height: 400px;';
        $output .= '  overflow-y: auto;'; // Changed from 'scroll' to 'auto'
        $output .= '  padding: 10px;';
        $output .= '  background-color: #f9f9f9;';
        $output .= '}';
    
    $output .= '.chat-message {';
        $output .= '  padding: 10px;';
        $output .= '  clear: both;';
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

// Generate the list of students
$studentList = '<div class="student-list-container">';
$studentList .= '<div class="search-box">';
$studentList .= '<input type="text" id="searchStudent" placeholder="Search student...">';
$studentList .= '</div>'; // Closing search-box div
// Check if there are students in the list
if (empty($students)) {
    $studentList .= '<div class="student-list">';
    $studentList .= 'No students found.';
    $studentList .= '</div>'; // Closing student-list div
} else {
    $studentList .= '<div class="student-list">';
    $studentList .= '<ul id="studentList">';
    foreach ($students as $student) {
        $studentList .= "<li class='student' data-sid='{$student['SID']}'>{$student['FName']} {$student['LName']}</li>";
    }
    $studentList .= '</ul>';
    $studentList .= '</div>'; // Closing student-list div
}

$studentList .= '</div>'; // Closing student-list-container div


    $output .= '<div class="chat-container">';
    $output .= $studentList;
    $output .= '<div class="chat-box-container">';
    $output .= '<div class="chat-container">';
    $output .= '<div class="chat-box" id="chatBox">';
    $output .= '<div class="student-name">';

$output .= '  Student Name Here'; // Replace this with the actual student name
$output .= '</div>';

    // Previous messages
    foreach ($previousMessages as $message) {
        $senderClass = ($message['sender'] == 'student') ? 'you' : 'me';
        $output .= '<div class="chat-message">';
        $output .= "<div class='chat-bubble $senderClass'>{$message['message']}</div>";
        $output .= '</div>';
    }

    $output .= '</div>'; // Closing chat-box div

    $output .= '<div class="chat-input">';
    $output .= '<textarea id="chatInput" placeholder="Type your message..." rows="4"></textarea>';
    $output .= '<button class="send-btn" onclick="sendMessage()">Send</button>';
    $output .= '</div>'; // Closing chat-input div



    $output .= '</div>'; // Closing chat-container div
    $output .= '</div>'; // Closing chat-box-container div
    $output .= '</div>'; // Closing chat-container div

   // JavaScript for handling student click event
$output .= '<script>';
$output .= '$(document).ready(function() {';
$output .= '  $(".student").click(function() {';
$output .= '    var studentId = $(this).data("sid");';
$output .= '    console.log("Selected student ID: " + studentId);'; // Just for demonstration
$output .= '  });';
$output .= '});';
$output .= '</script>';

    return $output;
}

// Call the function to generate the UI
echo Chat();
?>
