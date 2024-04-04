<?php
session_start();
include_once 'db.php';

// Check if SID is provided via POST data
$sid = isset($_POST['sid']) ? $_POST['sid'] : '';
if (!empty($sid)) {
    // Fetch student's full name from the database
    $sql = "SELECT FName, LName FROM Student WHERE SID = '$sid'"; 
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $fullName = $row['FName'] . ' ' . $row['LName'];
        /* echo "SID retrieved: " . $sid . "<br>"; */
    } else {
        echo "SID not retrieved";
    }
} else {
    echo "SID not provided";
}

// HTML form for tutor's reply
$output = '<form id="tutorReplyForm" enctype="multipart/form-data">'; 
$output .= '<div class="form-group mt-3">';
$output .= '<input type="hidden" id="sid" name="sid" value="' . $sid . '">'; // Include SID as a hidden input field
$output .= '</div>';
$output .= '<div class="form-group text-center mt-3">';
$output .= '<label for="studentLabel" class="mb-3" style="color: #1F8A70;">To: ' . $fullName . '</label>'; // Dynamically generate the label content with student's full name
$output .= '</div>';
$output .= '<div class="form-group mt-1">';
$output .= '<label for="tutorReplyContent" class="mb-3">Your Reply:</label>';
$output .= '<textarea class="form-control" id="tutorReplyContent" name="tutorReplyContent" rows="3" placeholder="Enter your reply"></textarea>';
$output .= '</div>';
$output .= '<div class="form-group mt-4">';
$output .= '<button type="button" class="btn btn-primary" id="sendReplyBtn">Send Reply</button>';
$output .= '</div>';
$output .= '</form>';
$output .= '<div class="mt-3" id="replyMessageBox"></div>'; // Add margin-top to replyMessageBox
$output .= '<div class="mt-3" id="alertBox"></div>';
echo $output;
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
//Send Reply button
$(document).ready(function(){
    $("#sendReplyBtn").click(function(){
        // Get the SID from the hidden input field
        var sid = $("#sid").val();
        // Get the reply content from the textarea
        var replyContent = $("#tutorReplyContent").val();
        
        // AJAX call to tutorReplyChatUpdate.php
        $.ajax({
            url: 'tutorReplyChatUpdate.php',
            type: 'POST',
            data: {sid: sid, replyContent: replyContent},
            success: function(response) {
                // Handle success response
                $("#alertBox").html(response);
                $("#tutorReplyContent").val('');
            },
            error: function(xhr, status, error) {
                // Handle error response
                console.error(error);
                $("#alertBox").html("Error: " + error);
            }
        });
    });
});

</script>
