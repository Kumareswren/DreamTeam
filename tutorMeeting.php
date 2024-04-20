<?php
use \Firebase\JWT\JWT;
session_start();

// Include necessary files and initialize the database connection
include "db.php";
require_once('vendor/autoload.php'); // Include the JWT library

// Check if the user is a tutor (you may have a better way to check this)
$isTutor = true; // Set this based on your authentication logic

if (!$isTutor) {
    // Redirect to the login page or unauthorized access page
    header("Location: index.php");
    exit();
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
        $sqlTutor = "SELECT TID FROM Tutor WHERE Email=?";
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

            // Fetch meeting list for the tutor based on their TID
            $meetingQuery = "SELECT ms.meetingID, ms.courseTitle, ms.meetingDate, ms.meetingTime, ms.meetingLocation, ms.meetingDesc, s.fname, s.lname 
                             FROM MeetingStudent ms
                             INNER JOIN Student s ON ms.SID = s.SID
                             WHERE ms.TID = ? AND ms.status = 'Pending'";
            $stmtMeeting = $conn->prepare($meetingQuery);
            if (!$stmtMeeting) {
                die("Error in SQL query: " . $conn->error);
            }

            $stmtMeeting->bind_param("i", $tid);
            $stmtMeeting->execute();
            $meetingResult = $stmtMeeting->get_result();

            // Check if meeting list fetched successfully
            if ($meetingResult->num_rows > 0) {
                // Display the meeting list in a nicer HTML table
                echo "<!DOCTYPE html>";
                echo "<html lang='en'>";
                echo "<head>";
                echo "<meta charset='UTF-8'>";
                echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
                echo "<title>Tutor Meeting List</title>";
                echo "<link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>";
                echo "</head>";
                echo "<body>";
                echo "<div class='container mt-5'>";
                echo "<h2>Tutor Meeting List</h2>";
                // search stuff
                echo "<div class='search-container'>";
                echo "<input type='text' id='searchInput' class='form-control' placeholder='Search for specific meetings...'>";
                echo "</div>"; //end of search stuff
                echo "<div class='table-responsive'>"; 
                echo "<table class='table table-bordered mt-3'>";
                echo "<thead class='thead-dark'>";
                echo "<tr>";
                echo "<th>Meeting ID</th>";
                echo "<th>Student Name</th>";
                echo "<th>Meeting Date</th>";
                echo "<th>Meeting Time</th>";
                echo "<th>Location</th>";
                echo "<th>Online Link / Location</th>";
                echo "<th>Action</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";

                while ($row = $meetingResult->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$row['meetingID']}</td>";
                    echo "<td>{$row['fname']} {$row['lname']}</td>";
                    echo "<td>{$row['meetingDate']}</td>";
                    echo "<td>{$row['meetingTime']}</td>";
                    echo "<td>{$row['meetingLocation']}</td>";
                    echo "<td>{$row['meetingDesc']}</td>";
                    echo "<td>";
                    echo "<form action='tutorMeetingBackend.php' method='post'>";
                    echo "<select name='action'>";
                    echo "<option value='accept'>Accept</option>";
                    echo "<option value='decline'>Decline</option>";
                    echo "</select>";
                    echo "<input type='hidden' name='meeting_id' value='{$row['meetingID']}'>";
                    echo "<input type='submit' value='Submit' class='btn btn-primary'>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }

                echo "</tbody>";
                echo "</table>";
                echo "</div>";
                echo "</body>";
                echo "</html>";
            } else {
                echo "No meetings found for the tutor.";
            }

            $stmtMeeting->close();
        } else {
            // Tutor not found
            echo "Tutor not found.";
        }

        $stmtTutor->close();
    } catch (Exception $e) {
        // Token verification failed
        echo "Token verification failed.";
    }
} else {
    // Token not found
    echo "Token not found.";
}
?>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$(document).ready(function() {
    $('#searchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
});
</script>
