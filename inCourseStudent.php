<?php
session_start();



header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
function generateInCourseDetails($courseId, $courseName, $startDate, $endDate) {
    // Generate HTML for course details
    $output = '<div class="container mt-5">';
    $output .= '<h1>' . $courseName . " (" . $startDate . " - " . $endDate . ")" . '</h1>';

    $output .= '<input type="hidden" id="courseId" value="' . $courseId . '">';

    // Tabs
    $output .= '<ul class="nav nav-tabs mt-5" id="myTab" role="tablist">';
    $output .= '<li class="nav-item">';
    $output .= '<a class="nav-link active" id="tab1-tab" data-toggle="tab" href="#tab1" role="tab" aria-controls="tab1" aria-selected="true">Notes</a>';
    $output .= '</li>';
    $output .= '<li class="nav-item">';
    $output .= '<a class="nav-link" id="tab2-tab" data-toggle="tab" href="#tab2" role="tab" aria-controls="tab2" aria-selected="false">Tutorial</a>';
    $output .= '</li>';
    $output .= '<li class="nav-item">';
    $output .= '<a class="nav-link" id="tab3-tab" data-toggle="tab" href="#tab3" role="tab" aria-controls="tab3" aria-selected="false">Students</a>';
    $output .= '</li>';
    /* $output .= '<li class="nav-item">'; 
    $output .= '<a class="nav-link" id="tab4-tab" data-toggle="tab" href="#tab4" role="tab" aria-controls="tab4" aria-selected="false">Your Submissions</a>';
    $output .= '</li>'; */
    $output .= '</ul>';

      // Tab content
      $output .= '<div class="tab-content mt-3" id="myTabContent">';
      $output .= '<div class="tab-pane fade show active" id="tab1" role="tabpanel" aria-labelledby="tab1-tab">';
      $output .= '<h3>Notes Content here</h3>';
      $output .= '<div id="noteList"></div>'; // Placeholder for notes list
      $output .= '</div>';

      $output .= '<div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab2-tab">';

      $output .= '<div id="uploadedTutorialFiles">';
    $output .= '<h3>Available Tutorials</h3>';
    // Fetch data from the 'tutorial' table
    include 'db.php'; 
    $sql = "SELECT tutorialTitle, tutorialID, tutorialDescription, uploadDate, tutorialFilePath FROM Tutorial WHERE courseId = $courseId";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        // Output table headers
        $output .= '<div class="table-responsive">';
        $output .= '<table class="table table-striped">';
        $output .= '<thead><tr><th>Tutorial Title</th><th>Tutorial Description</th><th>Date</th><th>View</th><th>Answer</th></tr></thead>';
        $output .= '<tbody>';
        // Output data of each row
        while ($row = $result->fetch_assoc()) {
            $tutorialFilePath = $row['tutorialFilePath'];
            $tutorialID = $row['tutorialID'];
            $output .= '<tr>';
            $output .= '<td>' . $row['tutorialTitle'] . '</td>';
            $output .= '<td>' . $row['tutorialDescription'] . '</td>';
            $output .= '<td>' . $row['uploadDate'] . '</td>';
            $output .= '<td><a href="' . $tutorialFilePath . '" class="btn btn-primary" download>Download</a></td>';
            $output .= '<td>';
            $output .= '<input type="file" id="fileInput_' . $row['tutorialTitle'] . '" style="display: none;" onchange="uploadAnswer(this.files, \'' . $tutorialFilePath . '\')">';
            $output .= '<button class="btn btn-success submit-tutorial" data-tutorial-id="' . $tutorialID . '">Submit</button>';

            $output .= '</td>';
            
            $output .= '</tr>';
        }
        $output .= '</tbody>';
        $output .= '</table>';
        $output .= '</div>';
    } else {
        $output .= '<div class="alert alert-info" role="alert">No tutorials found.</div>';
    }
    $conn->close();
    $output .= '</div>';

      $output .= '</div>';

      $output .= '<div class="tab-pane fade" id="tab3" role="tabpanel" aria-labelledby="tab3-tab">';
      $output .= '<h3>Students</h3>';
      $output .= '<div id="studentList"></div>';
      $output .= '</div>';

        /* $output .= '<div class="tab-pane fade" id="tab4" role="tabpanel" aria-labelledby="tab4-tab">';
        $output .= '<h3>Your Submissions</h3>';
        $output .= '<div id="studentSubList"></div>';
        $output .= '</div>';  */ 

      $output .= '</div>';
      $output .= '</div>';

    return $output;
}

// Retrieve course details from session
$courseId = isset($_SESSION['courseId']) ? $_SESSION['courseId'] : '';
$courseName = isset($_SESSION['courseName']) ? $_SESSION['courseName'] : '';
$startDate = isset($_SESSION['startDate']) ? $_SESSION['startDate'] : '';
$endDate = isset($_SESSION['endDate']) ? $_SESSION['endDate'] : '';

// Generate and output the course details HTML
echo generateInCourseDetails($courseId, $courseName, $startDate, $endDate);
?>

<!-- Include Bootstrap JavaScript Library -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> <!-- Include jQuery -->
<script>
/* $(document).on('click', '#tab1-tab', reloadNotesList); */

    // Function to reload the student list after successful insertion
    function reloadStudentList() {
        // Retrieve course ID from the hidden input
        var courseId = $('#courseId').val();

        // Show loading message or spinner
        $('#studentList').html('<p>Loading students...</p>');

        // Make an AJAX request to fetch students for the course
        $.ajax({
            url: 'courseStudent.php',
            type: 'POST',
            data: { courseId: courseId },
            success: function(response) {
                // Populate the student list container with fetched data
                $('#studentList').html(response);
                $('#tab3 h3').show();
            },
            error: function(xhr, status, error) {
                console.error(error);
                // Display error message to the user
                $('#studentList').html('<p>Error loading students. Please try again later.</p>');
            }
        });
    }
        // Function to reload the notes list after successful insertion
    function reloadNotesList() {
        // Retrieve course ID from the hidden input
        var courseId = $('#courseId').val();

        // Show loading message or spinner
        $('#noteList').html('<p>Loading notes...</p>');

        // Make an AJAX request to fetch notes for the course
        $.ajax({
            url: 'courseNoteList.php', // Adjust the URL to your PHP script for fetching notes
            type: 'POST',
            data: { courseId: courseId },
            success: function(response) {
                // Populate the notes list container with fetched data
                $('#noteList').html(response);
                $('#tab1 h3').show();
            },
            error: function(xhr, status, error) {
                console.error(error);
                // Display error message to the user
                $('#noteList').html('<p>Error loading notes. Please try again later.</p>');
            }
        });
    }

// do something like this for SEE ASWERS button - post the
$(document).on('click', '.submit-tutorial', function() {
    // Retrieve the tutorial ID from the data attribute
    var tutorialID = $(this).data('tutorial-id');
    
    // Make an AJAX request to load the form component
    $.ajax({
        url: 'submitTutorial.php',
        type: 'POST', 
        data: { tutorialID: tutorialID }, // Pass tutorial ID in the data object
        success: function(response) { 
            // Inject the form component into the container
            $('#uploadedTutorialFiles').html(response);
        },
        error: function(xhr, status, error) {
            console.error(error);
            // Display error message if component loading fails
            alert('Error loading upload form. Please try again later.');
        }
    });
});



    // Event listener for the Notes tab
    $(document).on('click', '#tab1-tab', reloadNotesList);

    

    // Event listener for the Students tab
    $(document).on('click', '#tab3-tab', reloadStudentList);


</script>

