<?php
require_once 'db.php';

// Define how many results you want per page for students
$results_per_page_student = 5;

// Get the current page
if (!empty($_POST['page'])) {
    $page = $_POST['page'];
} else {
    $page = 1;
}

// Calculate the offset
$offset = ($page - 1) * $results_per_page_student;

// SQL query to fetch student data with pagination
$sqlStudent = "SELECT SID, FName, LName FROM Student LIMIT $offset, $results_per_page_student";
$resultStudent = $conn->query($sqlStudent);

// Populate student table
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
?>
<script>
    // Rebind the click event for openActivity button
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

    // Rebind the click event for studentPageLink
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
</script>
