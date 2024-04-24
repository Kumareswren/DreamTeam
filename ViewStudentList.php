<?php
session_start();
require_once('db.php'); // Include the database connection file
require_once('vendor/autoload.php'); // Include the JWT library

use \Firebase\JWT\JWT;

header("Content-Type: text/html"); // Set content type to HTML
    
function generateStudentList($conn, $result, $limit, $offset) {
    $output = '<h2 class="mt-5 mb-4">My Student List</h2>'; // Add the heading
    $output .= '<input type="text" id="searchInput" placeholder="Search for student" class="form-control mb-3">'; 
    $output .= '<div id="studentList">';
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
    $output .= '</div>'; // Close #studentList

    // Pagination
    $output .= '<div class="pagination">';
    if ($offset > 0) {
        $output .= '<button class="prev-btn" data-offset="' . ($offset - $limit) . '">Prev</button>';
    }
    if ($result->num_rows == $limit) {
        $output .= '<button class="next-btn" data-offset="' . ($offset + $limit) . '">Next</button>';
    }
    $output .= '</div>';

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
            $_SESSION['TID'] = $tid; // Assuming $tid holds the tutor's ID

            // Prepare SQL query to log system activity
            $activity_type = "Show Student list";
            $page_name = "tutorDashboard.php";

            $full_user_agent = $_SERVER['HTTP_USER_AGENT'];
            // Regular expression to extract the browser name
            if (preg_match('/Edg\/([\d.]+)/i', $full_user_agent, $matches)) {
                $browser_name = 'Edge';
            } elseif (preg_match('/(Firefox|Chrome|Safari|Opera)/i', $full_user_agent, $matches)) {
                $browser_name = $matches[1];
            } else {
                $browser_name = "Unknown"; // Default to "Unknown" if browser name cannot be determined
            }
           
            $user_id = $tid; // Assuming $tid holds the tutor's ID
            $user_type = "Tutor";
            
            $insert_query = "INSERT INTO SystemActivity (UserID, UserType, ActivityType, PageName, BrowserName) 
                             VALUES ('$user_id', '$user_type', '$activity_type', '$page_name', '$browser_name')";
            
            // Execute the query
            if ($conn->query($insert_query) !== TRUE) {
                // Handle error if insert query fails
                echo "Error inserting system activity: " . $conn->error;
            }

            // Pagination variables
            $limit = 4; // Limit of students per page
            $offset = isset($_GET['offset']) ? $_GET['offset'] : 0; // Offset for pagination

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
            $studentListHTML = generateStudentList($conn, $result, $limit, $offset);

            $trailAction = "Checked student list assigned to him/her";
            insertTrailRecord($conn, $trailAction);

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
    echo "Token not found.";
}

function insertTrailRecord($conn, $trailAction) {
    $token = $_COOKIE['token'];
    $secretKey = 'your_secret_key';
    $decoded = JWT::decode($token, $secretKey, array('HS256'));
    $userId = $decoded->userId;
    $userRole = $decoded->role;
    $ipAddress = $_SERVER['REMOTE_ADDR'];

    // Prepare and execute the SQL query to insert into trail table
    $trailSql = "INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?, ?, ?, ?)";
    $trailStmt = $conn->prepare($trailSql);
    $trailStmt->bind_param("isss", $userId, $userRole, $ipAddress, $trailAction);
    if ($trailStmt->execute()) {
        // Trail record inserted successfully
        // You can handle success here if needed
    } else {
        // Error inserting into trail table
        echo "Error inserting into trail table: " . $trailStmt->error;
    }
    $trailStmt->close();
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

    // Pagination
    $(document).on('click', '.prev-btn', function() {
        var offset = $(this).data("offset");
        loadPage(offset);
    });

    $(document).on('click', '.next-btn', function() {
        var offset = $(this).data("offset");
        loadPage(offset);
        $(this).hide();
    });

    $(document).on('click', '.studentPageLink', function(event) {
        event.preventDefault();
        var page = $(this).data("page");
        loadPage(page);
    });

    function loadPage(offset) {
        $.ajax({
            url: "studentListPagination.php",
            type: "POST",
            data: { offset: offset },
            success: function(data) {
                $("#studentList").html(data);
            },
            error: function(xhr, status, error) {
                console.error("An error occurred:", error);
            }
        });
    }
});
</script>
