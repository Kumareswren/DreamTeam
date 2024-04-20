<?php
// Include database connection
require_once('db.php');
session_start();

// Function to fetch notes for a course
function courseNotes($courseId) {
    global $conn;
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
        $output .= '<table class="table table-striped">';
        $output .= '<thead><tr><th>Note</th><th>Download</th><th>Comment</th><th>Uploaded On</th></tr></thead>';
        $output .= '<tbody>';
        while ($row = $result->fetch_assoc()) {
            $output .= '<tr>';
            $output .= '<td>' . $row['Note'] . '</td>';
            // Provide option only for download, omitting the preview link
            $output .= '<td><a href="' . $row['URL'] . '" download>Download</a></td>'; 
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

