<?php
require_once 'db.php';

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

    // SQL query to fetch student data
    $sqlStudent = "SELECT SID, FName, LName FROM Student";
    $resultStudent = $conn->query($sqlStudent);

    // SQL query to fetch tutor data
    $sqlTutor = "SELECT TID, FName, LName FROM Tutor";
    $resultTutor = $conn->query($sqlTutor);

    $sqlAdmin = "SELECT AID, FName, LName FROM Admin";
    $resultAdmin = $conn->query($sqlAdmin);

    // Student tab content
    echo '<ul class="nav nav-tabs" id="userTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="student-tab" data-toggle="tab" href="#student" role="tab" aria-controls="student" aria-selected="true">Student</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tutor-tab" data-toggle="tab" href="#tutor" role="tab" aria-controls="tutor" aria-selected="false">Tutor</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="admin-tab" data-toggle="tab" href="#admin" role="tab" aria-controls="admin" aria-selected="false">Admin</a>
            </li>
        </ul>
        <div class="tab-content" id="userTabsContent">
            <div class="tab-pane fade show active pt-4" id="student" role="tabpanel" aria-labelledby="student-tab">
                <h4>Students</h4>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="studentList">';

    // Define how many results you want per page for students
    $results_per_page_student = 5;

    // Calculate the total number of pages for students
    $sql = "SELECT COUNT(*) AS total FROM Student";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $total_pages_student = ceil($row["total"] / $results_per_page_student);

    // Populate student table
    function displayStudentTable($page, $results_per_page_student, $conn) {
        $sqlStudent = "SELECT SID, FName, LName FROM Student LIMIT " . (($page - 1) * $results_per_page_student) . ", $results_per_page_student";
        $resultStudent = $conn->query($sqlStudent);

        if ($resultStudent->num_rows > 0) {
            while ($row = $resultStudent->fetch_assoc()) {
                echo '<tr>
                        <td>' . $row['SID'] . '</td>
                        <td>' . $row['FName'] . ' ' . $row['LName'] . '</td>
                        <td><button class="btn btn-primary openActivity" data-id="' . $row['SID'] . '" data-type ="student">View Activity</button></td>
                    </tr>';
            }
        } else {
            echo '<tr><td colspan="3">No students found</td></tr>';
        }
    }

    displayStudentTable(1, $results_per_page_student, $conn);

    echo '</tbody>
        </table>
        <div class="pagination" id="studentPagination">';

    // Pagination for students
    for ($i = 1; $i <= $total_pages_student; $i++) {
        echo '<a href="#" class="page-link studentPageLink" data-page="' . $i . '">' . $i . '</a>';
    }

    echo '</div>
    </div>';

    // Tutor tab content
    echo '<div class="tab-pane fade pt-4" id="tutor" role="tabpanel" aria-labelledby="tutor-tab">
            <h4>Tutors</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="tutorList">';

    // Define how many results you want per page for tutors
    $results_per_page_tutor = 5;

    // Calculate the total number of pages for tutors
    $sql = "SELECT COUNT(*) AS total FROM Tutor";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $total_pages_tutor = ceil($row["total"] / $results_per_page_tutor);

    // Populate tutor table
    function displayTutorTable($page, $results_per_page_tutor, $conn) {
        $sqlTutor = "SELECT TID, FName, LName FROM Tutor LIMIT " . (($page - 1) * $results_per_page_tutor) . ", $results_per_page_tutor";
        $resultTutor = $conn->query($sqlTutor);

        if ($resultTutor->num_rows > 0) {
            while ($row = $resultTutor->fetch_assoc()) {
                echo '<tr>
                        <td>' . $row['TID'] . '</td>
                        <td>' . $row['FName'] . ' ' . $row['LName'] . '</td>
                        <td><button class="btn btn-primary openActivity" data-id="' . $row['TID'] . '" data-type ="tutor">View Activity</button></td>
                    </tr>';
            }
        } else {
            echo '<tr><td colspan="3">No tutors found</td></tr>';
        }
    }

    displayTutorTable(1, $results_per_page_tutor, $conn);

    echo '</tbody>
        </table>
        <div class="pagination" id="tutorPagination">';

    // Pagination for tutors
    for ($i = 1; $i <= $total_pages_tutor; $i++) {
        echo '<a href="#" class="page-link tutorPageLink" data-page="' . $i . '">' . $i . '</a>';
    }

    echo '</div>
    </div>';

    echo '<div class="tab-pane fade pt-4" id="admin" role="tabpanel" aria-labelledby="admin-tab">
            <h4>Admins</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="adminList">';
    // Populate tutor table
    if ($resultAdmin->num_rows > 0) {
        while ($row = $resultAdmin->fetch_assoc()) {
            echo '<tr>
                    <td>' . $row['AID'] . '</td>
                    <td>' . $row['FName'] . ' ' . $row['LName'] . '</td>
                    <td><button class="btn btn-primary openActivity" data-id="' . $row['AID'] . '" data-type ="admin">View Activity</button></td>
                </tr>';
        }
    } else {
        echo '<tr><td colspan="3">No admins found</td></tr>';
    }
    echo '</tbody>
        </table>
    </div>
</div>';

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

        $(".studentPageLink").click(function(event) {
            event.preventDefault();
            var page = $(this).data("page");
            $.ajax({
                url: "studentActivityPagination.php",
                type: "POST",
                data: { page: page },
                success: function(data) {
                    $("#studentList").html(data);
                },
                error: function(xhr, status, error) {
                    console.error("An error occurred:", error);
                }
            });
        });

        $(".tutorPageLink").click(function(event) {
            event.preventDefault();
            var page = $(this).data("page");
            $.ajax({
                url: "tutorActivityPagination.php",
                type: "POST",
                data: { page: page },
                success: function(data) {
                    $("#tutorList").html(data);
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
