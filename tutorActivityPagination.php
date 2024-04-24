<?php
require_once 'db.php';

// Define how many results you want per page for tutors
$results_per_page_tutor = 5;

// Get the current page
if (!empty($_POST['page'])) {
    $page = $_POST['page'];
} else {
    $page = 1;
}

// Calculate the offset
$offset = ($page - 1) * $results_per_page_tutor;

// SQL query to fetch tutor data with pagination
$sqlTutor = "SELECT TID, FName, LName FROM Tutor LIMIT $offset, $results_per_page_tutor";
$resultTutor = $conn->query($sqlTutor);

// Populate tutor table
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

    // Rebind the click event for tutorPageLink
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
</script>
