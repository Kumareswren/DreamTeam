<?php
// Include database connection
require_once('db.php');
session_start();

function courseNotes($courseId) {
    global $conn, $userId, $userRole, $ipAddress;
    $output = '';

    // Query to fetch notes for the course
    $sql = "SELECT noteID, noteTitle AS Note, noteFilePath AS URL, noteDescription AS Comment, uploadDate AS 'Uploaded On'
            FROM Note
            WHERE courseID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $courseId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if notes found
    if ($result->num_rows > 0) {
        $output .= '<div class="table-responsive">';
        $output .= '<input type="text" id="searchInput"  placeholder="Search notes..." class="form-control mb-3">'; // Add search input field
        $output .= '<table class="table table-striped" id="notesTable">'; 
        $output .= '<thead><tr><th>Note</th><th>Download</th><th>Comment</th><th>Uploaded On</th></tr></thead>';
        $output .= '<tbody>';
        while ($row = $result->fetch_assoc()) {
            $output .= '<tr>';
            $output .= '<td>' . $row['Note'] . '</td>';
            // Provide option only for download, adding an onclick event to trigger AJAX call
            $output .= '<td><a href="' . $row['URL'] . '" download onclick="downloadClicked(\'' . $row['Note'] . '\')">Download</a></td>'; 
            $output .= '<td>' . $row['Comment'] . '</td>';
            $output .= '<td>' . $row['Uploaded On'] . '</td>';
            $output .= '</tr>';
        }
        $output .= '</tbody>';
        $output .= '</table>';
        $output .= '</div>';
    } else {
        $output .= '<div class="alert alert-info" role="alert">No notes available for this course.</div>';
    }

    return $output;
}

// Retrieve course ID from AJAX request
$courseId = isset($_SESSION['courseId']) ? $_SESSION['courseId'] : '';

// Fetch notes for the given course ID
echo courseNotes($courseId);

?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Function to handle download button click
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
</script>

<script>
$(document).ready(function() {
    $('#searchInput').on('keyup', function() {
        var searchText = $(this).val().toLowerCase();
        $('#notesTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(searchText) > -1);
        });
    });
});
</script>