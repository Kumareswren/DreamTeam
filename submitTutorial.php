<?php
session_start(); 

$tutorialID = isset($_POST['tutorialID']) ? $_POST['tutorialID'] : '';

$output = '<form id="tutorialForm" enctype="multipart/form-data">';
$output .= '<input type="hidden" name="tutorialID" value="' . $tutorialID . '">'; // Include tutorialID as a hidden input field
$output .= '<div class="form-group mb-3">'; // Add margin bottom class
$output .= '<label for="tutorialFile" class="mb-2">Select File:</label>'; // Add margin bottom class
$output .= '<input type="file" class="form-control-file mb-2" id="tutorialFile" name="tutorialFile">'; // Add margin bottom class
$output .= '</div>';

if(isset($_SESSION['SID'])) { // Add formfield for SID, tutorialAnswerTitle - Assuming SID is stored in session variable
    $output .= '<input type="hidden" name="SID" value="' . $_SESSION['SID'] . '">'; // SID - hidden input
} else {
    $output .= '<input type="hidden" name="SID" value="">';
}

$output .= '<div class="form-group mb-3">'; // Add margin bottom class
$output .= '<label for="tutorialAnswerTitle" class="mb-2">Tutorial Answer Title:</label>'; // Add margin bottom class
$output .= '<input type="text" class="form-control mb-2" id="tutorialAnswerTitle" name="tutorialAnswerTitle" placeholder="Enter tutorial answer title">'; // Add margin bottom class
$output .= '</div>';

$output .= '<button type="submit" class="btn btn-primary mt-3">Upload Tutorial</button>'; // Add margin top class
/* $output .= '<button type="button" class="btn btn-primary mt-3" onclick="submitForm()">Upload Tutorial</button>';  */
/* $output .= '<button type="button" class="btn btn-primary mt-3" onclick="submitForm()">Submit Remark</button>'; */
$output .= '</form>';
$output .= '<div id="messageBox"></div>';

echo $output;
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>

$(document).on('submit', '#tutorialForm', function(event) {
    event.preventDefault(); // Prevent default form submission

    // Serialize form data
    var formData = new FormData(this);

    $.ajax({
        url: 'uploadTutorialAnswer.php',
        type: 'POST',
        data: formData,
        processData: false, // Prevent jQuery from automatically processing the data
        contentType: false, // Prevent jQuery from setting contentType
        success: function(response, status, xhr) {
            // Check HTTP status code for success
            if (xhr.status === 200) {
                $('#messageBox').html('<div class="alert alert-success">' + response + '</div>');
            } else {
                $('#messageBox').html('<div class="alert alert-danger">Unexpected response from server</div>');
            }
        },
        error: function(xhr, status, error) {
            // Check HTTP status code for error
            if (xhr.status === 400) {
                $('#messageBox').html('<div class="alert alert-danger">Error: ' + error + '</div>');
            } else if (xhr.status === 500) {
                $('#messageBox').html('<div class="alert alert-danger">Internal Server Error</div>');
            } else {
                $('#messageBox').html('<div class="alert alert-danger">Unexpected error occurred</div>');
            }
        }
    });
});
</script>