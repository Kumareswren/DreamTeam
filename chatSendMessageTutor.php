<?php
session_start();
require_once('db.php'); 
require_once('vendor/autoload.php'); // Include the JWT library

/* echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>'; */
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
        echo "Token not found.";
        return;
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
    $output .= '  max-height: 150px;'; // Adjusted max-height
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
    $output .= '  height: 300px;'; // Adjusted height
    $output .= '  overflow-y: auto;'; // Changed from 'scroll' to 'auto'
    $output .= '  padding: 10px;';
    $output .= '  background-color: #f9f9f9;';
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

    $output .= '<div class="student-name">';
    $output .= '  <span id="selectedStudentName"></span>';
        // Form for description input and send button
        $output .= '<form id="chatForm">';
        $output .= '<div class="chat-input">';
        $output .= '<textarea id="chatInput" placeholder="Type your message..." rows="4"></textarea>';
        $output .= '<button type="submit" class="send-btn">Send</button>';
        $output .= '</form>';
    
    $output .= '</div>';

    $output .= '<div class="mt-3" id="alertBox"></div>'; // Add the alertBox div
    $output .= '</div>';

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
    $output .= '    var studentSid = $(this).data("sid");'; // Retrieve SID attribute
    $output .= '    var tid = ' . $tid . ';';  //retrieve TID from session
    /* $output .= '    var tid = ' . $tid . ';';  */ //just added
    $output .= '    console.log("Selected student name: " + studentName);'; // Just for demonstration
    $output .= '    $("#selectedStudentName").text(studentName);'; // Update the displayed student name
    $output .= '    $(".send-btn").data("sid", studentSid);'; // Set SID as data attribute in the send button
    
    /* $output .= '    $(".send-btn").data({"sid": studentSid, "tid": tid});'; */ //just added
    $output .= '  });';
    $output .= '});';
    $output .= '</script>';
    

    return $output;
}

// Call the function to generate the UI
echo Chat();

// Retrieve the POST data
/* if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle the POST data here
    $user_email = $_POST['email'];
    // Process the email data as needed
    // For example:
    echo "Received email: " . $user_email;  // uncomment it to check if the correct email is received
} */

?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>

$(document).ready(function() {
    $(".send-btn").click(function(e) {
        e.preventDefault(); 

        var message = $("#chatInput").val(); // take user.input from chatInput textarea

        message = $('<h1/>').text(message).html();
        // Retrieve the SID from the send button data attribute
        var sid = $(this).data("sid");

        /* var tid = $(this).data("tid"); */ //just added

        $.ajax({
            type: "POST",
            url: "updateTutorMessage.php",
            data: {
                message: message,
                sid: sid,

                /* tid: tid */ //just added
            },
            success: function(response) {
                $("#alertBox").html('<div class="alert alert-success" role="alert">Message sent successfully.</div>');
                // Clear the chat input after successful submission
                $("#chatInput").val('');
                $("#selectedStudentName").text('');
            },
            error: function(xhr, status, error) {
                $("#alertBox").html('<div class="alert alert-danger" role="alert">Please type something </div>');
            }
        });
    });
});


</script>