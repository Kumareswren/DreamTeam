<?php
session_start();
require_once('db.php'); // Include the database connection file

function generateStudentList($conn, $result) {
    $output = '<table class="table">';
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

// Retrieve the offset from the AJAX request
$offset = $_POST['offset'];
$limit = 4; // Limit of students per page

// Retrieve the tutor's ID from the session
$tid = $_SESSION['TID'];

// Query to get the students assigned to the tutor's TID with pagination
$sql = "SELECT Student.* FROM Student 
        INNER JOIN StudentAssignment ON Student.SID = StudentAssignment.SID 
        WHERE StudentAssignment.TID = ?
        LIMIT ?, ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error in SQL query: " . $conn->error);
}

$stmt->bind_param("iii", $tid, $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();

// Generate student list with pagination
echo generateStudentList($conn, $result);

// Calculate total number of pages
$sqlTotal = "SELECT COUNT(*) AS total FROM Student 
             INNER JOIN StudentAssignment ON Student.SID = StudentAssignment.SID 
             WHERE StudentAssignment.TID = ?";
$stmtTotal = $conn->prepare($sqlTotal);
if (!$stmtTotal) {
    die("Error in SQL query: " . $conn->error);
}

$stmtTotal->bind_param("i", $tid);
$stmtTotal->execute();
$resultTotal = $stmtTotal->get_result();
$rowTotal = $resultTotal->fetch_assoc();
$totalPages = ceil($rowTotal['total'] / $limit);

// Generate pagination buttons
$prevOffset = $offset - $limit;
$nextOffset = $offset + $limit;

$output = '<div class="pagination">';
if ($offset > 0) {
    $output .= '<button class="prev-btn" data-offset="' . $prevOffset . '">Prev</button>';
}
if ($nextOffset < $rowTotal['total']) {
    $output .= '<button class="next-btn" data-offset="' . $nextOffset . '">Next</button>';
}
$output .= '<div>';
echo $output;

// Close the database connection
$conn->close();
?>
