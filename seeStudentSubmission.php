<?php
include 'db.php';

$output = '';

$tutorialID = isset($_POST['tutorialID']) ? $_POST['tutorialID'] : '';// Check if tutorialID is provided via POST


$output .= "Received tutorial ID: " . $tutorialID . "<br>";// Print out tutorialID to check if it's correctly received


if (!empty($tutorialID)) {// Proceed only if tutorialID is provided
    $sql = "SELECT CONCAT(s.FName, ' ', s.LName) AS StudentName,
    ta.tutorialAnswerID,
    ta.tutorialAnswerTitle, 
    ta.uploadDate, 
    ta.tutorialAnswerFilePath, 
    ta.tutorComment
FROM TutorialAnswer AS ta
INNER JOIN Student AS s ON ta.SID = s.SID
WHERE ta.tutorialID = $tutorialID";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $output .= '<div class="table-responsive">';
        $output .= '<table class="table table-striped">';
        $output .= '<thead>';
        $output .= '<tr>';
        $output .= '<th>Student Name</th>';
        $output .= '<th>Submission Title</th>';
        $output .= '<th>Upload Date</th>';
        $output .= '<th>Download</th>';
        $output .= '<th>Remarks</th>'; // Added Remarks column
        $output .= '<th>Add Remarks</th>';
        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '<tbody>';

        while ($row = $result->fetch_assoc()) {
           /*  $tutorialAnswerID = $row['tutorialAnswerID']; */
            $output .= '<tr>';
            $output .= '<td>' . $row['StudentName'] . '</td>';
            $output .= '<td>' . $row['tutorialAnswerTitle'] . '</td>';
            $output .= '<td>' . $row['uploadDate'] . '</td>';
            $output .= '<td><a href="' . $row['tutorialAnswerFilePath'] . '" class="btn btn-primary" download onclick="downloadClicked(\'' . $row['tutorialAnswerTitle'] . '_' . $row['StudentName'] . '\')">Download</a></td>';
            $output .= '<td>' . $row['tutorComment'] . '</td>';
            /* $output .= '<button class="btn btn-success btn-add-remark" data-tutorial-answer-id="' . $tutorialAnswerID . '">Add Remark</button>'; */
            $output .= '<td><button class="btn btn-success btn-add-remark" data-tutorial-answer-id="' . $row['tutorialAnswerID'] . '">Add Remark</button></td>';
            $output .= '</tr>';
        }

        $output .= '</tbody>';
        $output .= '</table>';
        $output .= '</div>'; // end of table-responsive
    } else {
        $output .= "No submissions found for this tutorial.";
    }
} else {
    $output .= "Error: Tutorial ID not provided.";
}

echo $output;

$conn->close();
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>

$(document).ready(function() { //Comment button component when clicked
    $('.btn-add-remark').click(function(event) {
        event.preventDefault(); // Prevent default button behavior (form submission)

        var tutorialAnswerID = $(this).data('tutorial-answer-id');
        $.ajax({
            url: 'addRemarkForm.php', 
            type: 'POST',
            data: { tutorialAnswerID: tutorialAnswerID },
            success: function(response) {
                /* alert(response); */
                $('#uploadedTutorialFiles').html(response);//change this
            },
            error: function(xhr, status, error) {
                // Handle errors if any
                console.error(error);
            }
        });
    });
});

function downloadClicked(noteTitle) {
    // Make AJAX call to insert record into trail table
    $.ajax({
        type: "POST",
        url: "noteTitle.php", // PHP script to handle insertion into trail table
        data: { actionPerformed: noteTitle + " tutorial answer have been downloaded" },
        success: function(response) {
            console.log("Trail record inserted successfully.");
        },
        error: function(xhr, status, error) {
            console.error("Error inserting trail record:", error);
        }
    });
}
</script>