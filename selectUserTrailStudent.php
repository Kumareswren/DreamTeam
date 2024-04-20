<?php
require_once 'db.php';
require_once('vendor/autoload.php');
use \Firebase\JWT\JWT;

function displayUsersComponent($conn) {
    echo '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Select user</title>

                <!-- Font Awesome CSS -->
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/css/all.min.css" integrity="sha256-mmgLkCYLUQbXn0B1SRqzHar6dCnv9oZFPEC1g1cwlkk=" crossorigin="anonymous" />
            
            </head>
            <body>';
            

    echo "<h2>Select user to check activity</h2><br/>";

    $token = $_COOKIE['token'];
    $secretKey = 'your_secret_key';
    $decoded = JWT::decode($token, $secretKey, array('HS256'));
    $userId = $decoded->userId;

    // SQL query to fetch student data
    $sqlStudent = "SELECT SID, FName FROM Student WHERE SID = $userId";
    $resultStudent = $conn->query($sqlStudent);

    // SQL query to fetch tutor data
    if ($resultStudent->num_rows > 0) {
        // User is a student, fetch tutor's name using SID
        $rowStudent = $resultStudent->fetch_assoc();
        $studentName = $rowStudent['FName'];
    
        // SQL query to fetch TID from StudentAssignment table
        $sqlStudentAssignment = "SELECT TID FROM StudentAssignment WHERE SID = $userId";
        $resultStudentAssignment = $conn->query($sqlStudentAssignment);
        $rowStudentAssignment = $resultStudentAssignment->fetch_assoc();
        $tutorId = $rowStudentAssignment['TID'];
    
    }

    // Student tab content
    echo '<ul class="nav nav-tabs" id="userTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="student-tab" data-toggle="tab" href="#student" role="tab" aria-controls="student" aria-selected="true">Student</a>
            </li>';
    // Check if the user is a student, then display the tutor tab
    if ($resultStudent->num_rows > 0) {
        echo '<li class="nav-item">
                <a class="nav-link" id="tutor-tab" data-toggle="tab" href="#tutor" role="tab" aria-controls="tutor" aria-selected="false">Tutor</a>
            </li>';
    }
    echo '</ul>
        <div class="tab-content" id="userTabsContent">
            <div class="tab-pane fade show active pt-4" id="student" role="tabpanel" aria-labelledby="student-tab">
                <h4>Students</h4>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="studentList">';
    // Populate student table
    if ($resultStudent->num_rows > 0) {
        // Since we have already fetched the student data, no need to re-fetch
        echo '<tr>
                <td>' . $rowStudent['FName'] . '</td>
                <td><button class="btn btn-primary openActivity" data-id="' . $rowStudent['SID'] . '" data-type ="student">View Activity</button></td>
            </tr>';
    } else {
        echo '<tr><td colspan="2">No students found</td></tr>';
    }
    echo '</tbody>
        </table>
    </div>';

    // Tutor tab content
    if ($resultStudent->num_rows > 0) {
        echo '<div class="tab-pane fade pt-4" id="tutor" role="tabpanel" aria-labelledby="tutor-tab">
                <h4>Tutors</h4>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="tutorList">';
        // Populate tutor table
        $sqlTutor = "SELECT TID, FName FROM Tutor WHERE TID = $tutorId";
        $resultTutor = $conn->query($sqlTutor);
        if ($resultTutor->num_rows > 0) {
            while ($row = $resultTutor->fetch_assoc()) {
                echo '<tr>
                        <td>' . $row['FName'] . '</td>
                        <td><button class="btn btn-primary openActivity" data-id="' . $row['TID'] . '" data-type ="tutor">View Activity</button></td>
                    </tr>';
            }
        } else {
            echo '<tr><td colspan="2">No tutors found</td></tr>';
        }
        echo '</tbody>
            </table>
        </div>';
    }

    echo '<!-- jQuery and Bootstrap JS -->
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jsonwebtoken/8.5.1/jsonwebtoken.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <!-- Custom Script -->
    <script>
    $(document).ready(function() {
        $(".openActivity").click(function(event) {
            event.preventDefault();
            var id = $(this).data("id");
            var userType = $(this).data("type");
            $.ajax({
                url: "getUserTrail.php",
                type: "POST",
                data: { userID: id, userRole: userType }, // Use userID and userRole
                success: function(data) {
                    $("#componentContainer").html(data);
                },
                error: function(xhr, status, error) {
                    console.error("An error occurred:", error);
                }
            });
        });
    });
    </script>
</body>
</html>';
}

// Assuming $conn is your database connection
// Call the function to display the dashboard component
displayUsersComponent($conn);

?>
