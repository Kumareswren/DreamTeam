<?php
session_start();
include_once 'db.php';

// Check if TID is provided via POST data
$tid = isset($_POST['tid']) ? $_POST['tid'] : '';
if (!empty($tid)) {
    // Fetch tutor's full name from the database
    $sql = "SELECT FName, LName FROM Tutor WHERE TID = '$tid'"; 
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $fullName = $row['FName'] . ' ' . $row['LName'];
        /* echo "TID retrieved: " . $tid . "<br>"; */
    } else {
        echo "TID not retrieved";
    }
} else {
    echo "TID not provided";
}

// HTML form for tutor's reply
$output = '<form id="tutorReplyForm" enctype="multipart/form-data">'; 
$output .= '<div class="form-group mt-3">';
$output .= '<input type="hidden" id="tid" name="tid" value="' . $tid . '">'; // Include TID as a hidden input field
$output .= '</div>';
$output .= '<div class="form-group text-center mt-3">';
$output .= '<label for="tutorLabel" class="mb-3" style="color: #1F8A70;">To: ' . $fullName . '</label>'; // Dynamically generate the label content with tutor's full name
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
        // Get the TID from the hidden input field
        var tid = $("#tid").val();
        // Get the reply content from the textarea
        var replyContent = $("#tutorReplyContent").val();
        
        
        // Check if replyContent is empty
        if(replyContent.trim() === '') {
            $("#alertBox").html('<div class="alert alert-danger" role="alert">Please type something</div>'); 
            return; // Stop further execution
        }
        
        // AJAX call to tutorReplyChatUpdate.php
        $.ajax({
            url: 'studentReplyChatUpdate.php',
            type: 'POST',
            data: {tid: tid, replyContent: replyContent},
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

