<?php
session_start();
include_once 'db.php';

// Check if tutorialAnswerID is provided via POST data
$tutorialAnswerID = isset($_POST['tutorialAnswerID']) ? $_POST['tutorialAnswerID'] : '';
/* include_once "db.php"; */
$tutorComment = '';

// Fetch tutorComment for the given tutorialAnswerID from the database
/* $sql = "SELECT tutorComment FROM TutorialAnswer WHERE tutorialAnswerID = '$tutorialAnswerID'"; */
/* $result = mysqli_query($conn, $sql);

// Check if a row is fetched
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    // Check if tutorComment is already filled
    if (!empty($row['tutorComment'])) {
        // If tutorComment is already filled, display a message indicating that a comment already exists
        echo '<p>You already added comment to this student submission.</p>';
    } else { */

$output = '<form id="tutorCommentForm" enctype="multipart/form-data">'; 
$output .= '<input type="hidden" id="tutorialAnswerID" name="tutorialAnswerID" value="' . $tutorialAnswerID . '">'; // Include tutorialAnswerID as a hidden input field
$output .= '<div class="form-group mt-3">';
$output .= '<label for="tutorComment">Your Comment:</label>';
$output .= '<textarea class="form-control" id="tutorComment" name="tutorComment" rows="3" placeholder="Enter your remark">' . $tutorComment . '</textarea>';
$output .= '</div>';
$output .= '<button type="button" class="btn btn-primary mt-3" onclick="submitForm()">Submit Remark</button>';
$output .= '</form>';
$output .= '<div id="messageBox"></div>';
echo $output;
/*     }
}
mysqli_close($conn); //close db connection */
mysqli_close($conn);
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>

//here add ajax code to reference tutorRemarkUpdate.php

function submitForm() {
    var tutorialAnswerID = $('input[name="tutorialAnswerID"]').val();// Get tutorialAnswerID from input field

    // Get tutorComment from textarea
    var tutorComment = $('#tutorComment').val().trim();

    // Check if tutorComment is not empty
    if (tutorComment !== '') {
        $.ajax({
            url: 'tutorRemarkUpdate.php',
            type: 'POST',
            data: {
                tutorialAnswerID: tutorialAnswerID,
                tutorComment: tutorComment
            },
            success: function(response) {
                console.log(response);
                //var responseObject = JSON.parse(response);
                $('#messageBox').html('<div class="alert alert-success" role="alert">Your remark has been sent</div>');
                $('#tutorComment').val(''); // Clear comment textarea
            },
            error: function(xhr, status, error) {
                $('#messageBox').html('<div class="alert alert-danger" role="alert">An error occurred. Please try again later.</div>');
                console.error(error);
            }
        });
    } else {
        $('#messageBox').html('<div class="alert alert-danger" role="alert">Please enter a comment.</div>');
    }
}

$(document).ready(function() {
    // Define the maximum number of characters allowed for tutorComment
    var maxCharacters = 140;

    // Event listener for input in tutorComment textarea
    $('#tutorComment').on('input', function() {
        // Get the current value of the textarea
        var comment = $(this).val();

        // Get the length of the comment
        var charCount = comment.length;

        // Check if the character count exceeds the maximum limit
        if (charCount > maxCharacters) {
            // Trim the comment to the maximum character limit
            var trimmedComment = comment.substring(0, maxCharacters);
            
            // Update the value of the textarea with the trimmed comment
            $(this).val(trimmedComment);
        }

        // Update the character count display
        $('#charCount').text(charCount + '/' + maxCharacters);
    });
});

</script>
