<?php

include 'db.php';
use \Firebase\JWT\JWT;
require_once('vendor/autoload.php');

// Retrieve the JWT token from the cookie
$token = $_COOKIE['token'];

// Decode the JWT token to extract the email
$decoded = JWT::decode($token, 'your_secret_key', array('HS256'));
$user_email = $decoded->email;

// Prepare and execute SQL query to retrieve student ID based on email
$stmt = $conn->prepare("SELECT SID FROM Student WHERE Email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $studentId = $row['SID']; // Retrieve student ID (SID)
} else {
    // Handle case where student ID is not found
    // You can set a default value or show an error message
    $studentId = 0; // Default value or handle accordingly
}

// Retrieve the courseId via POST
$courseId = isset($_POST['courseId']) ? intval($_POST['courseId']) : 0;

// Prepare SQL query to fetch data
$sql = "SELECT ta.tutorialAnswerTitle, ta.uploadDate, ta.tutorComment, ta.tutorialAnswerFilePath
        FROM TutorialAnswer ta
        INNER JOIN Tutorial t ON ta.tutorialID = t.tutorialID
        WHERE ta.SID = $studentId 
        AND t.courseID = $courseId
        AND ta.tutorComment IS NOT NULL";

// Execute the query
$result = $conn->query($sql);

// Check if the query execution was successful
if (!$result) {
    // Query execution failed, print the error message
    echo "Error: " . $conn->error;
} else {
    // Check if there are results
    if ($result->num_rows > 0) {
        // Output search input field
        echo '<input type="text" id="searchInput" placeholder="Search for your tutorial..." class="form-control mb-3">';
        
        // Output table header
        $output = '<table class="table table-striped">';
        $output .= '<thead><tr><th>Title</th><th>Uploaded on</th><th>Comment from your Tutor</th><th>Your Answer</th></tr></thead>';
        $output .= '<tbody>';

        // Output data of each row
        while ($row = $result->fetch_assoc()) {
            $output .= '<tr>';
            $output .= '<td>' . $row['tutorialAnswerTitle'] . '</td>';
            $output .= '<td>' . $row['uploadDate'] . '</td>';
            $output .= '<td>' . $row['tutorComment'] . '</td>';
            // Add a button to download the file associated with tutorialFilePath
            $output .= '<td><a href="' . $row['tutorialAnswerFilePath'] . '" class="btn btn-primary" download>View</a></td>';
            $output .= '</tr>';
        }

        // Close table body and table
        $output .= '</tbody>';
        $output .= '</table>';

        // Output the result
        echo $output;
    } else {
        // If no records found
        echo '<p>No submissions found.</p>';
    }
}

// Close database connection
$conn->close();
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$(document).ready(function() {
    // Add an event listener to the search input field
    $('#searchInput').on('input', function() {
        // Get the search term
        var searchTerm = $(this).val().toLowerCase();

        // Filter the table rows based on the search term
        $('tbody tr').each(function() {
            var title = $(this).find('td:nth-child(1)').text().toLowerCase();
            var comment = $(this).find('td:nth-child(3)').text().toLowerCase();
            if (title.includes(searchTerm) || comment.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
</script>
