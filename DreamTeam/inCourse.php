<?php
session_start();

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
    $output .= '</ul>';

    // Tab content
    $output .= '<div class="tab-content mt-3" id="myTabContent">';
    $output .= '<div class="tab-pane fade show active" id="tab1" role="tabpanel" aria-labelledby="tab1-tab">';
    $output .= '<h3>Notes Content here</h3>';
    $output .= '<p>This is the content of tab Notes.</p>';
    $output .= '</div>';
    $output .= '<div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab2-tab">';
    $output .= '<h3>Tutorial Content here</h3>';
    $output .= '<p>This is the content of tab Tutorial.</p>';
    $output .= '</div>';
    $output .= '<div class="tab-pane fade" id="tab3" role="tabpanel" aria-labelledby="tab3-tab">';
    $output .= '<h3>Students</h3>';
    $output .= '<div id="studentList"></div>';
    $output .= '<button id="addStudentBtn">Add Student</button>';
    $output .= '</div>';
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
   $(document).ready(function() {
    // Function to load the addCourseStudent component
    function loadAddCourseStudent(courseId) {
        // Make an AJAX request to load the addCourseStudent component
        $.ajax({
            url: 'addCourseStudent.php',
            type: 'GET',
            data: { courseId: courseId }, // Pass courseId as a parameter
            success: function(response) {
                // Populate the studentList container with the addCourseStudent component
                $('#studentList').html(response);
            },
            error: function(xhr, status, error) {
                console.error(error);
                // Display error message to the user
                $('#studentList').html('<p>Error loading addCourseStudent component. Please try again later.</p>');
            }
        });
    }

    // Event listener for the Add Student button
    $(document).on('click', '#addStudentBtn', function() {
        // Retrieve the courseId from the hidden input
        $('#tab3 h3').hide();
        $('#addStudentBtn').hide();
        var courseId = $('#courseId').val();

        // Load the addCourseStudent component with the courseId
        loadAddCourseStudent(courseId);
    });

    // Event listener for the form submission
    $(document).on('submit', '#studentForm', function(event) {
        event.preventDefault(); // Prevent the default form submission

        // Serialize the form data
        var formData = $(this).serialize();

        // Submit the form data via AJAX
        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: formData,
            success: function(response) {
                // Clear the form data
                $('#studentForm')[0].reset();
                // Show success message or handle response accordingly
                alert('Students added successfully to the course!');
                
                // Reload student list after successful insertion
                reloadStudentList();
                $('#tab3 h3').show();
                $('#addStudentBtn').show();
            },
            error: function(xhr, status, error) {
                console.error(error);
                // Display error message to the user
                alert('Error adding students to the course. Please try again later.');
            }
        });
    });

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
                $('#addStudentBtn').show();
            },
            error: function(xhr, status, error) {
                console.error(error);
                // Display error message to the user
                $('#studentList').html('<p>Error loading students. Please try again later.</p>');
            }
        });
    }

    // Event listener for the Students tab
    $(document).on('click', '#tab3-tab', reloadStudentList);
});


</script>
