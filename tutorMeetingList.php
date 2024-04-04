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
    $_SESSION['message'] = "Unauthorized access.";
    header("Location: tutorMeetingList.php");
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
            $_SESSION['message'] = "Error in SQL query: " . $conn->error;
            header("Location: tutorMeetingList.php");
            exit();
        }

        $stmtTutor->bind_param("s", $email);
        $stmtTutor->execute();
        $resultTutor = $stmtTutor->get_result();

        // Check if tutor found
        if ($resultTutor->num_rows > 0) {
            $rowTutor = $resultTutor->fetch_assoc();
            $tid = $rowTutor['TID'];

            // Fetch meeting list for the tutor based on their TID, ordered by meeting date and time
            $meetingQuery = "SELECT ms.meetingDate, ms.meetingTime, ms.meetingLocation, ms.meetingDesc, s.fname, s.lname, ms.status
                             FROM MeetingStudent ms
                             INNER JOIN Student s ON ms.SID = s.SID
                             WHERE ms.TID = ?
                             ORDER BY ms.meetingDate DESC, ms.meetingTime DESC"; // Order by meeting date and time

            $stmtMeeting = $conn->prepare($meetingQuery);
            if (!$stmtMeeting) {
                $_SESSION['message'] = "Error in SQL query: " . $conn->error;
                header("Location: tutorMeetingList.php");
                exit();
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
                echo "<table class='table table-bordered mt-3'>";
                echo "<thead class='thead-dark'>";
                echo "<tr>";
                echo "<th>Student Name</th>";
                echo "<th>Meeting Date</th>";
                echo "<th>Meeting Time</th>";
                echo "<th>Location</th>";
                echo "<th>Online Link / Location</th>";
                echo "<th>Status</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";

                while ($row = $meetingResult->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$row['fname']} {$row['lname']}</td>";
                    echo "<td>{$row['meetingDate']}</td>";
                    echo "<td>{$row['meetingTime']}</td>";
                    echo "<td>{$row['meetingLocation']}</td>";
                    echo "<td>{$row['meetingDesc']}</td>";
                    echo "<td>";
                    if ($row['status'] == 'Accepted') {
                        echo "<span class='badge badge-success'>Accepted</span>";
                    } elseif ($row['status'] == 'Declined') {
                        echo "<span class='badge badge-danger'>Declined</span>";
                    } else {
                        echo "<span class='badge badge-secondary'>{$row['status']}</span>";
                    }
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
            $_SESSION['message'] = "Tutor not found.";
            header("Location: tutorMeetingList.php");
            exit();
        }

        $stmtTutor->close();
    } catch (Exception $e) {
        // Token verification failed
        $_SESSION['message'] = "Token verification failed.";
        header("Location: tutorMeetingList.php");
        exit();
    }
} else {
    // Token not found
    $_SESSION['message'] = "Token not found.";
    header("Location: tutorMeetingList.php");
    exit();
}
?>
