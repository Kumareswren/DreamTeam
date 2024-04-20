<?php
require_once 'db.php';
function displayDashboardComponent($conn) {
    echo '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Dashboard</title>

                <!-- Font Awesome CSS -->
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/css/all.min.css" integrity="sha256-mmgLkCYLUQbXn0B1SRqzHar6dCnv9oZFPEC1g1cwlkk=" crossorigin="anonymous" />
            
                </head>
            <body>';
            

    echo "<h2>Dashboard</h2><br/>";

    // SQL query to fetch student data
    $sqlStudent = "SELECT SID, FName FROM Student";
    $resultStudent = $conn->query($sqlStudent);

    // SQL query to fetch tutor data
    $sqlTutor = "SELECT TID, FName FROM Tutor";
    $resultTutor = $conn->query($sqlTutor);

    // Student tab content
    echo '<ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="student-tab" data-toggle="tab" href="#student" role="tab" aria-controls="student" aria-selected="true">Student</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tutor-tab" data-toggle="tab" href="#tutor" role="tab" aria-controls="tutor" aria-selected="false">Tutor</a>
            </li>
        </ul>
        <div class="tab-content" id="dashboardTabsContent">
            <div class="tab-pane fade show active pt-4" id="student" role="tabpanel" aria-labelledby="student-tab">
                <h4>Students</h4>
                <input type="text" id="studentSearchInput" placeholder="Search students..." class="form-control mb-3">
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
        while ($row = $resultStudent->fetch_assoc()) {
            echo '<tr>
                    <td>' . $row['FName'] . '</td>
                    <td><button class="btn btn-primary openDashboard" data-id="' . $row['SID'] . '" data-type ="student">View Dashboard</button></td>
                </tr>';
        }
    } else {
        echo '<tr><td colspan="2">No students found</td></tr>';
    }
    echo '</tbody>
        </table>
    </div>';

    // Tutor tab content
    echo '<div class="tab-pane fade pt-4" id="tutor" role="tabpanel" aria-labelledby="tutor-tab">
            <h4>Tutors</h4>
            <input type="text" id="tutorSearchInput" placeholder="Search tutors..." class="form-control mb-3">'; 
    echo '<table class="table">
            
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="tutorList">';
    // Populate tutor table
    if ($resultTutor->num_rows > 0) {
        while ($row = $resultTutor->fetch_assoc()) {
            echo '<tr>
                    <td>' . $row['FName'] . '</td>
                    <td><button class="btn btn-primary openDashboard" data-id="' . $row['TID'] . '" data-type ="tutor">View Dashboard</button></td>
                </tr>';
        }
    } else {
        echo '<tr><td colspan="2">No tutors found</td></tr>';
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
            $(".openDashboard").click(function(event) {
                event.preventDefault();
                var id = $(this).data("id");
                var userType = $(this).data("type");
                $.ajax({
                    url: "dashboard.php",
                    type: "POST",
                    data: { user_role: userType , userID: id},
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

    <script>
        $(document).ready(function() {
            // Search function for students
            $("#studentSearchInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#studentList tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // Your existing code for opening dashboard on button click
        });
    </script>

    <script>
    $("#tutorSearchInput").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#tutorList tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
    </script>

</body>
</html>';
}

// Assuming $conn is your database connection
// Call the function to display the dashboard component
displayDashboardComponent($conn);

?>
