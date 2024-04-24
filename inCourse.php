<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

function generateInCourseDetails($courseId, $courseName, $startDate, $endDate) {
    // Generate HTML for course details
    $output = '<div class="container mt-5">';
    $output .= '<h1>' . $courseName . " (" . $startDate . " - " . $endDate . ")" . '</h1>';

    $output .= '<input type="hidden" id="courseId" value="' . $courseId . '">';
    $output .= '<input type="hidden" id="courseName" name="courseName" value="' . $courseName . '">';
    $output .= '<input type="hidden" id="startDate" name="startDate" value="' . $startDate . '">';
    $output .= '<input type="hidden" id="endDate" name="endDate" value="' . $endDate . '">';
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
   /*  $output .= '<li class="nav-item">';
    $output .= '<a class="nav-link" id="tab4-tab" data-toggle="tab" href="#tab4" role="tab" aria-controls="tab4" aria-selected="false">Answers</a>';
    $output .= '</li>'; */
    $output .= '</ul>';
    
    // Tab content
    $output .= '<div class="tab-content mt-3" id="myTabContent">';
    $output .= '<div class="tab-pane fade show active" id="tab1" role="tabpanel" aria-labelledby="tab1-tab">';
    
    $output .= '<h2>Upload Notes</h2>';
    $output .= '<form id="noteForm" enctype="multipart/form-data">';
    $output .= '<div class="form-group mb-3">';
    $output .= '<label for="noteFile">Select File:</label>';
    $output .= '<input type="file" class="form-control-file" id="noteFile" name="noteFile" accept=".docx, .pptx, .pdf">';
    $output .= '</div>';
    $output .= '<div class="form-group mb-3">';
    $output .= '<label for="noteDescription">Note Description:</label>';
    $output .= '<input type="text" class="form-control" id="noteDescription" name="noteDescription" placeholder="Enter note description">';
    $output .= '</div>';
    $output .= '<div class="form-group mb-3">';
    $output .= '<label for="noteTitle">Note Title:</label>';
    $output .= '<input type="text" class="form-control" id="noteTitle" name="noteTitle" placeholder="Enter note title">';
    $output .= '</div>';
    $output .= '<button type="submit" class="btn btn-success">Upload Note</button>';
    $output .= '</form>';
    $output .= '<div id="uploadNoteResult" class="mt-3"></div>';
    
    $output .= '<div id="uploadedNotesFiles" class="mt-5">';
    $output .= '<h3>Uploaded Notes</h3>';
    // Fetch data from the 'note' table
    include 'db.php'; // Include database connection
    $sql = "SELECT noteID, noteTitle, noteDescription, uploadDate, noteFilePath FROM Note WHERE courseID = $courseId"; 
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        // Output table headers
        $output .= '<div class="table-responsive">';
        $output .= '<input type="text" id="searchInput" class="form-control mb-3" placeholder="Search">';
        $output .= '<table class="table table-striped">';
        $output .= '<thead><tr><th>Note Title</th><th>Note Description</th><th>Date</th><th>URL</th><th>Delete</th></tr></thead>';
        $output .= '<tbody>';
        // Output data of each row
        while ($row = $result->fetch_assoc()) {
            $noteFilePath = $row['noteFilePath']; 
            $output .= '<tr>';
            $output .= '<td>' . $row['noteTitle'] . '</td>';
            $output .= '<td>' . $row['noteDescription'] . '</td>';
            $output .= '<td>' . $row['uploadDate'] . '</td>';
            $output .= '<td><a href="' . $noteFilePath . '" class="btn btn-primary" download onclick="downloadClicked(\'' . $row['noteTitle'] . '\')">Download</a></td>';
            $output .= '<td><button class="btn btn-danger delete-note" data-note-id="' . $row['noteID'] . '">Delete</button></td>';
            $output .= '</tr>';
        }
        $output .= '</tbody>';
        $output .= '</table>';
        $output .= '</div>';
    } else {
        $output .= '<div class="alert alert-info" role="alert">No notes found.</div>';
    }
    $conn->close();
    $output .= '</div>';

        //notes  form ended
    $output .= '</div>';
    $output .= '<div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab2-tab">';
    $output .= '<h3>Tutorials of the Course</h3>';
    $output .= '<p>Here are your tutorials</p>';
    //form to upload tutorials
    $output .= '<form id="tutorialForm" enctype="multipart/form-data" action="tutorialUploaded.php" method="post">';
    
    $output .= '<div class="form-group mb-3">';
    $output .= '<label for="tutorialFile">Select File:</label>';
    $output .= '<input type="file" class="form-control-file" id="tutorialFile" name="tutorialFile">';
    $output .= '</div>';
    $output .= '<div class="form-group mb-3">';
    $output .= '<label for="tutorialDescription">Tutorial Description:</label>';
    $output .= '<input type="text" class="form-control" id="tutorialDescription" name="tutorialDescription" placeholder="Enter tutorial description">';
    $output .= '</div>';
    $output .= '<div class="form-group mb-3">';
    $output .= '<label for="tutorialTitle">Tutorial Title:</label>';
    $output .= '<input type="text" class="form-control" id="tutorialTitle" name="tutorialTitle" placeholder="Enter tutorial title">';
    $output .= '</div>';
    $output .= '<div class="form-group d-flex align-items-center mt-3" style="height: 100px;">'; //start flex containter
    $output .= '<button type="submit" class="btn btn-success mr-5">Upload Tutorial</button>';
    $output .= '<div id="uploadMessage"></div>'; //inside flexbox -- should i change this ti messageBox? - refer to line 390
    $output .= '</div>'; //flex container end
    $output .= '</form>';
     
    
    // Display section for uploaded tutorials
    $output .= '<div id="uploadResult" class="mt-5"></div>';
    $output .= '<div id="uploadedTutorialFiles" class="mt-5">';
    $output .= '<h3>Uploaded Tutorials</h3>';

     include 'db.php'; 
     $sql = "SELECT * FROM Tutorial WHERE courseID = $courseId"; 
     $result = $conn->query($sql);
     if ($result->num_rows > 0) {
         $output .= '<div class="table-responsive">';
         $output .= '<input type="text" id="tutorialSearchInput" class="form-control mb-3" placeholder="Search">';
         $output .= '<table class="table table-striped">';
         $output .= '<thead><tr><th>Tutorial Title</th><th>Tutorial Description</th><th>Date</th><th>Download</th><th>Submits</th></tr></thead>';
         $output .= '<tbody>';
         // Output data of each row
         while ($row = $result->fetch_assoc()) {
            $tutorialID = $row['tutorialID'];
            $tutorialFilePath = $row['tutorialFilePath'];
             $output .= '<tr>';
             $output .= '<td>' . $row['tutorialTitle'] . '</td>';
             $output .= '<td>' . $row['tutorialDescription'] . '</td>';
             $output .= '<td>' . $row['uploadDate'] . '</td>';
             $output .= '<td><a href="' . $tutorialFilePath . '" class="btn btn-primary" download onclick="downloadTutorialClicked(\'' . $row['tutorialTitle'] . '\')">Download</a></td>';
             $output .= '<td><button class="btn btn-success btn-see-answers" data-tutorial-id="' . $tutorialID . '" >See Answers</button></td>'; // on click -> ajaxcomponent that displays matching tutorialAnswers
             $output .= '<td><button class="btn btn-danger delete-tutorial" data-tutorial-id="' . $tutorialID . '">Delete</button></td>';
             $output .= '</tr>';
         }
         $output .= '</tbody>';
         $output .= '</table>';
         $output .= '</div>';
     } else {
         $output .= '<div class="alert alert-info" role="alert">No tutorials found.</div>';
     }
     $conn->close();

    $output .= '</div>'; // tutorial form ended

    $output .= '</div>';
    //starting the students tab stuff
    $output .= '<div class="tab-pane fade" id="tab3" role="tabpanel" aria-labelledby="tab3-tab">';
    $output .= '<h3>Students</h3>';
    // Move the studentList container inside the 'Students' tab's content division
    $output .= '<div id="studentList"></div>';
    $output .= '<button id="addStudentBtn">Add Student</button>';
    $output .= '</div>';

    $output .= '</div>'; //end of tab content

    $output .= '</div>'; //end of container

    return $output;
}

// Retrieve course details from session
$courseId = isset($_SESSION['courseId']) ? $_SESSION['courseId'] : '';
$courseName = isset($_SESSION['courseName']) ? $_SESSION['courseName'] : '';
$startDate = isset($_SESSION['startDate']) ? $_SESSION['startDate'] : '';
$endDate = isset($_SESSION['endDate']) ? $_SESSION['endDate'] : '';

// Generate and output the course details HTML
echo generateInCourseDetails($courseId, $courseName, $startDate, $endDate);

// Handle file upload if form is submitted

?>

<!-- Include Bootstrap JavaScript Library -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> <!-- Include jQuery -->
<script>

$(document).ready(function() {
    $('#searchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
});

$(document).ready(function() {
    $('#tutorialSearchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#uploadedTutorialFiles tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
});

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

                                //Valsan section -- NOTES part
 
 $(document).ready(function() {
            // Event listener for form submission in inCourse.php
            $('#noteForm').submit(function(event) {
                event.preventDefault(); // Prevent the default form submission

                // Serialize the form data
                var formData = new FormData($(this)[0]);
                formData.append('courseId', $('#courseId').val());

                // Make an AJAX request to submit the form data
                $.ajax({
                    url: 'notesUploaded.php', // Endpoint to handle form submission
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response, textStatus, xhr) {
                        // Check the status code to determine success or failure
                if (xhr.status === 200) {
                    // Display the success message
                    $('#uploadNoteResult').html('<div class="alert alert-success" role="alert">' + response + '</div>');
                    // Update the list of uploaded files
                    updateUploadedFilesList(formData.get('noteFile').name);
                } else {
                    // Display the error message
                    $('#uploadNoteResult').text(response);
                    // Handle error
                }
            },
                    error: function(xhr, status, error) {
                        console.error(error);
                        // Handle error
                    }
                });
            });
        });

// Update the list of uploaded files after successful file upload
function updateUploadedFilesList(fileName) {
    // Append the new file to the list
    $('#fileList').append('<li>' + fileName + '</li>');
}

});
                                //Valsan section -- TUTORIALS part
$(document).ready(function() {
    // Event listener for tutorial file upload
    $('#tutorialForm').submit(function(event) {
        event.preventDefault(); // Prevent the default form submission

        // Serialize the form data
        var formData = new FormData($(this)[0]);
        formData.append('courseId', $('#courseId').val());

        // Make an AJAX request to submit the form data
        $.ajax({
            url: 'tutorialUploaded.php', // Endpoint to handle form submission
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response, textStatus, xhr) {
                // Check the HTTP status code in the response
                if (xhr.status === 200) {
                    // Success
                    $('#uploadMessage').html('<div class="alert alert-success" role="alert">' + response + '</div>');
                    /* $('#uploadResult').html(response); */
                    updateUploadedFilesList(response.fileNames);
                } else {
                    // Error
                    $('#uploadMessage').html('<div class="alert alert-danger" role="alert">Error: ' + response + '</div>');
                }
            },
            error: function(xhr, status, error) {
                // Display error message to the user
                $('#uploadMessage').html('<div class="alert alert-danger" role="alert">Please select a file</div>');
            }
        });
    });
});

// SEE ANSWERS button clicked - jQuery
$(document).ready(function(){
    $('.btn-see-answers').click(function(){
        var tutorialID = $(this).data('tutorial-id'); // Retrieve tutorial ID from data attribute
        $.ajax({
            type: 'POST',
            url: 'seeStudentSubmission.php',
            data: { tutorialID: tutorialID }, // Pass tutorial ID to PHP script
            success: function(response){
                $('#uploadedTutorialFiles').html(response);
            },
            error: function(xhr, status, error) {
                console.error(error);
                // Debugging: Log any errors to the console
                console.log("AJAX Error: " + error);
            }

        });
    });
});

function downloadClicked(noteTitle) {
    // Make AJAX call to insert record into trail table
    $.ajax({
        type: "POST",
        url: "noteTitle.php", // PHP script to handle insertion into trail table
        data: { actionPerformed: noteTitle + " notes have been downloaded" },
        success: function(response) {
            console.log("Trail record inserted successfully.");
        },
        error: function(xhr, status, error) {
            console.error("Error inserting trail record:", error);
        }
    });
}

function downloadTutorialClicked(noteTitle) {
    // Make AJAX call to insert record into trail table
    $.ajax({
        type: "POST",
        url: "noteTitle.php", // PHP script to handle insertion into trail table
        data: { actionPerformed: noteTitle + " tutorial have been downloaded" },
        success: function(response) {
            console.log("Trail record inserted successfully.");
        },
        error: function(xhr, status, error) {
            console.error("Error inserting trail record:", error);
        }
    });
}

$(document).ready(function(){
    $('.delete-note').click(function(){
        var noteID = $(this).data('note-id');
        var rowToDelete = $(this).closest('tr'); // Identify the row to delete
        
        if (confirm("Are you sure you want to delete this note?")) {
        $.ajax({
            url: 'notesDelete.php',
            type: 'post',
            data: {noteID: noteID},
            success: function(response){
                // Handle success response
                /* console.log(response); */
                // Remove the deleted row from the table
                rowToDelete.remove();
            },
            error: function(xhr, status, error){
                // Handle error
                console.error(error);
            }
        });
}});
});

$(document).ready(function(){
    $('.delete-tutorial').click(function(){
        var tutorialID = $(this).data('tutorial-id');
        var rowToDelete = $(this).closest('tr'); // Identify the row to delete
        
        if (confirm("Are you sure you want to delete this tutorial?")) {
        $.ajax({
            url: 'tutorialDelete.php',
            type: 'post',
            data: {tutorialID: tutorialID},
            success: function(response){
                // Handle success response
                /* console.log(response); */
                // Remove the deleted row from the table
                rowToDelete.remove();
            },
            error: function(xhr, status, error){
                // Handle error
                console.error(error);
            }
        });
}});
}); 


</script>

