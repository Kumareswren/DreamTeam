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
$sql_check_user = "SELECT * FROM Student WHERE Email = '$user_email'";
$result_check_user = $conn->query($sql_check_user);
$row = $result_check_user->fetch_assoc();
$sid = $row['SID'];

// Fetch tutorname associated w/ student's SID - 5:53 am, pickup from here
$sql_fetch_tutor_details = "SELECT sa.TID, CONCAT(t.FName, ' ', t.LName) AS TutorName
                            FROM StudentAssignment sa
                            INNER JOIN Tutor t ON sa.TID = t.TID
                            WHERE sa.SID = '$sid'";
$result_fetch_tutor_details = $conn->query($sql_fetch_tutor_details);

// Check if any records were fetched
if ($result_fetch_tutor_details->num_rows > 0) {
    // Generate the table
    $output = '<h3>Overview of your Conversation</h3>';
    $output .= '<div class="table-responsive">';
    $output .= '<table class="table table-striped table-hover">';
    $output .= '<thead>';
    $output .= '<tr>';
    $output .= '<th>Tutor Name</th>';
    $output .= '<th>Unread Messages</th>';
    $output .= '<th>Message History</th>';
    $output .= '<th>Reply</th>';
    $output .= '</tr>';
    $output .= '</thead>';
    $output .= '<tbody>';

    while ($tutor_row = $result_fetch_tutor_details->fetch_assoc()) {
        // Get SID and student name
        $tid = $tutor_row['TID'];
        $tutor_name = $tutor_row['TutorName'];

        // Fetch the count of unread messages for the student
        $sql_unread_messages = "SELECT COUNT(*) AS unread_messages_count
                                FROM Messages
                                WHERE TID = '$tid'
                                AND SID = '$sid'
                                AND sender_type = 'Tutor'
                                AND readStatus IS NULL";
        $result_unread_messages = $conn->query($sql_unread_messages);
        $unread_messages_row = $result_unread_messages->fetch_assoc();
        $unread_messages_count = $unread_messages_row['unread_messages_count'];

        // Add the student row to the table
        $output .= '<tr>';
        $output .= '<td>' . $tutor_name . '</td>';
        $output .= '<td>' . $unread_messages_count . '</td>';
        $output .= '<td><button class="btn btn-primary message-history-btn" data-tid="'. $tid.'" onclick="historyClicked(\'' . $tutor_name . '\')">Message History</button></td>';
        $output .= '<td><button class="btn btn-success reply-btn">Reply</button></td>'; //reply-btn data-sid="'. $sid.' ">
        $output .= '</tr>';
    }

    $output .= '</tbody>';
    $output .= '</table>';
    $output .= '</div>';
} else {
    // No records found
    $output = '<h3>No records found</h3>';
}

// Echo the generated HTML
echo $output;

?>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> 
<script>

//Message History button
$(document).ready(function(){
    $(".message-history-btn").click(function(){
        var tid = $(this).data('tid');
       
        $.ajax({
            url: "chatStudentBackend.php",    
            type: "POST",
            data: {tid: tid }, // You can pass data here 
            success: function(response){
                $('#componentContainer').html(response);
                var textarea = $('#chatInput');
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
        //Get the TID corresponding to clickedRow from messagehistory button
        var tid = $(this).closest('tr').find('.message-history-btn').data('tid');
       
        $.ajax({
            url: "addStudentReplyForm.php",
            type: "POST",
            data: {tid: tid}, // Pass SID as POST data
            success: function(response){
                $('#componentContainer').html(response);
            },
            error: function(xhr, status, error){
                console.error("Error: " + error);
            }
        });
    });
});

function historyClicked(tutorName) {
    // Make AJAX call to insert record into trail table
    $.ajax({
        type: "POST",
        url: "noteTitle.php", // PHP script to handle insertion into trail table
        data: { actionPerformed: "Opened message history with tutor " + tutorName },
        success: function(response) {
            console.log("Trail record inserted successfully.");
        },
        error: function(xhr, status, error) {
            console.error("Error inserting trail record:", error);
        }
    });
}


</script>