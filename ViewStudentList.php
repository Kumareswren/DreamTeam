<?php
session_start();
require_once('db.php'); // Include the database connection file
require_once('vendor/autoload.php'); // Include the JWT library

use \Firebase\JWT\JWT;

header("Content-Type: text/html"); // Set content type to HTML
    
    function generateStudentList($conn, $result) {
        $output = '<h2 class="mt-5 mb-4">My Student List</h2>'; // Add the heading
        $output .= '<input type="text" id="searchInput" placeholder="Search for student" class="form-control mb-3">'; //search input field
        $output .= '<table class="table">';
        $output .= '<thead>';
        $output .= '<tr>';
        $output .= '<th>First Name</th>';
        $output .= '<th>Last Name</th>';
        $output .= '<th>Email</th>';
        $output .= '<th>Contact</th>';
        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '<tbody>';

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $output .= '<tr>';
            $output .= '<td>' . (isset($row['FName']) ? $row['FName'] : '') . '</td>';
            $output .= '<td>' . (isset($row['LName']) ? $row['LName'] : '') . '</td>';
            $output .= '<td>' . (isset($row['Email']) ? $row['Email'] : '') . '</td>';
            $output .= '<td>' . (isset($row['Contact']) ? $row['Contact'] : '') . '</td>';
            $output .= '</tr>';
        }
    } else {
        $output .= '<tr><td colspan="4">No students found</td></tr>';
    }

    $output .= '</tbody>';
    $output .= '</table>';

    return $output;
}

// Retrieve the token from the cookie
if (isset($_COOKIE['token'])) {
    $token = $_COOKIE['token'];

    // Decode the token to get the email 
    $secretKey = 'your_secret_key'; // Update with your secret key
    try {
        $decoded = JWT::decode($token, $secretKey, array('HS256'));
        $email = $decoded->email;

        // Query to get the TID for the tutor's email
        $sqlTutor = "SELECT TID FROM tutor WHERE Email=?";
        $stmtTutor = $conn->prepare($sqlTutor);
        if (!$stmtTutor) {
            die("Error in SQL query: " . $conn->error);
        }

        $stmtTutor->bind_param("s", $email);
        $stmtTutor->execute();
        $resultTutor = $stmtTutor->get_result();

        // Check if tutor found
        if ($resultTutor->num_rows > 0) {
            $rowTutor = $resultTutor->fetch_assoc();
            $tid = $rowTutor['TID'];

            // Query to get the students assigned to the tutor's TID
            $sql = "SELECT Student.* FROM Student 
                    INNER JOIN StudentAssignment ON Student.SID = StudentAssignment.SID 
                    WHERE StudentAssignment.TID = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Error in SQL query: " . $conn->error);
            }

            $stmt->bind_param("i", $tid);
            $stmt->execute();
            $result = $stmt->get_result();

            // Generate student list
            $studentListHTML = generateStudentList($conn, $result);

            // Close the database connection
            $conn->close();

            echo $studentListHTML; // Output the HTML table
        } else {
            // Tutor not found
            echo "Tutor not found.";
        }
    } catch (Exception $e) {
        // Token verification failed
        echo "Token verification failed.";
    }
} else {
    // Token not found
    echo "Token not found.";
}
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
            var firstName = $(this).find('td:nth-child(1)').text().toLowerCase();
            var lastName = $(this).find('td:nth-child(2)').text().toLowerCase();
            var email = $(this).find('td:nth-child(3)').text().toLowerCase();
            var contact = $(this).find('td:nth-child(4)').text().toLowerCase();
            if (firstName.includes(searchTerm) || lastName.includes(searchTerm) || email.includes(searchTerm) || contact.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
</script>
