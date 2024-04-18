<?php
session_start();
use \Firebase\JWT\JWT;
require_once('vendor/autoload.php'); 
require_once('db.php'); 

$token = $_COOKIE['token'];

// Decode the JWT token to extract the email
$decoded = JWT::decode($token, 'your_secret_key', array('HS256'));
$user_email = $decoded->email;

// Check if the user exists in the database
$sql_check_user = "SELECT * FROM Tutor WHERE Email = '$user_email'";
$result_check_user = $conn->query($sql_check_user);
$row = $result_check_user->fetch_assoc();
$tid = $row['TID'];

// Fetch student names associated with the tutor's TID
$sql_fetch_students = "SELECT s.SID, s.FName, s.LName
                       FROM StudentAssignment sa
                       INNER JOIN Student s ON sa.SID = s.SID
                       WHERE sa.TID = '$tid'";
$result_fetch_students = $conn->query($sql_fetch_students);

// Generate the table
$output = '<h3>Overview of your Conversations</h3>';
$output .= '<div class="table-responsive">';
$output .= '<table class="table table-striped table-hover">';
$output .= '<thead>';
$output .= '<tr>';
$output .= '<th>Student Name</th>';
$output .= '<th>Unread Messages</th>';
$output .= '<th>Message History</th>';
$output .= '<th>Reply</th>';
$output .= '</tr>';
$output .= '</thead>';
$output .= '<tbody>';

while ($student_row = $result_fetch_students->fetch_assoc()) {
    // Get SID and student name
    $sid = $student_row['SID'];
    $student_name = $student_row['FName'] . ' ' . $student_row['LName'];

    // Fetch the count of unread messages for the student
    $sql_unread_messages = "SELECT COUNT(*) AS unread_messages_count
                            FROM Messages
                            WHERE TID = '$tid'
                            AND SID = '$sid'
                            AND sender_type = 'Student'
                            AND readStatus IS NULL";
    $result_unread_messages = $conn->query($sql_unread_messages);
    $unread_messages_row = $result_unread_messages->fetch_assoc();
    $unread_messages_count = $unread_messages_row['unread_messages_count'];

    // Add the student row to the table
    $output .= '<tr>';
    $output .= '<td>' . $student_name . '</td>';
    $output .= '<td>' . $unread_messages_count . '</td>';
    $output .= '<td><button class="btn btn-primary message-history-btn" data-sid="'. $sid.'>">Message History</button></td>';
    $output .= '<td><button class="btn btn-success reply-btn">Reply</button></td>'; //reply-btn data-sid="'. $sid.' ">
    $output .= '</tr>';
}

$output .= '</tbody>';
$output .= '</table>';
$output .= '</div>';
  
  $_SESSION['TID'] = $sid;

  // Prepare SQL query to log system activity
  $activity_type = "Show Chat";
  $page_name = "TutorDashboard.php";
  $browser_name = $_SERVER['HTTP_USER_AGENT'];
  $user_id = $tid; 
  $user_type = "Tutor";

  $insert_query = "INSERT INTO SystemActivity (UserID, UserType, ActivityType, PageName, BrowserName) 
                   VALUES ('$user_id', '$user_type', '$activity_type', '$page_name', '$browser_name')";
  if ($conn->query($insert_query) !== TRUE) {
      // Handle error if insert query fails
      echo "Error inserting system activity: " . $conn->error;
  }
// Wrap the "Start New Conversation" button in a container div and apply CSS for positioning
$output .= '<div style="text-align: center; margin-top: 10px; margin-right:30px; ">';
$output .= '<button class="btn btn-warning start-conversation-btn" data-tid="'. $tid.'">Start New Conversation</button>';
$output .= '</div>';

// Echo the generated HTML
echo $output;
?>



<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> 
<script>
   //Start New Conversation button
   $(document).ready(function(){
    $(".start-conversation-btn").click(function(){
        var tid = $(this).data('tid');
       
        $.ajax({
            url: "chatSendMessageTutor.php",    // tutorReplyForm.php, chatSendMessageTutor.php, 
            type: "POST",
            data: {tid: tid }, // You can pass data here 
            success: function(response){
                $('#componentContainer').html(response);
                console.log(response);

                // Add auto-resizing functionality after loading the chat
                var textarea = $('#chatInput');
                
                textarea.on('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });

                // Adjust initial height to fit one line
                textarea.css('height', 'auto');
                textarea.css('height', textarea[0].scrollHeight + 'px');
            },
            error: function(xhr, status, error){
                /* console.error(xhr.responseText); */
                console.error("Error: " + error);
            }
        });
    });
});   

//Message History button
$(document).ready(function(){
    $(".message-history-btn").click(function(){
        var sid = $(this).data('sid');
       
        $.ajax({
            url: "chatTutorBackend.php",    
            type: "POST",
            data: {sid: sid }, // You can pass data here 
            success: function(response){
                $('#componentContainer').html(response);
                console.log(response);

                // Add auto-resizing functionality after loading the chat
                var textarea = $('#chatInput');
                
                textarea.on('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });

                // Adjust initial height to fit one line
                textarea.css('height', 'auto');
                textarea.css('height', textarea[0].scrollHeight + 'px');
            },
            error: function(xhr, status, error){
                /* console.error(xhr.responseText); */
                console.error("Error: " + error);
            }
        });
    });
}); 


//Reply button
$(document).ready(function(){
    $(".reply-btn").click(function(){
        //Get the SID corresponding to clickedRow from messagehistory button
        var sid = $(this).closest('tr').find('.message-history-btn').data('sid');
       
        $.ajax({
            url: "addTutorReplyForm.php",
            type: "POST",
            data: {sid: sid}, // Pass SID as POST data
            success: function(response){
                $('#componentContainer').html(response);
                console.log(response);
            },
            error: function(xhr, status, error){
                console.error("Error: " + error);
            }
        });
    });
});


</script>