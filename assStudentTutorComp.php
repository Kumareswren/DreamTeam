<?php
session_start();
include "db.php";

function assStudentTutorComp($studentResult, $tutorResult, $errorMessage) {
?>
<div class="container">
    <h2 class="mt-5 mb-4">Assign Students to Tutors</h2>
    <form action="assStudentTutorBackend.php" method="post">
        <table class="table">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Tutor</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Reset tutor query to beginning
                mysqli_data_seek($tutorResult, 0);

                // Loop through each student and create a table row with dropdown list
                while ($studentRow = mysqli_fetch_assoc($studentResult)) {
                    $studentID = $studentRow['SID'];
                    echo "<tr>";
                    echo "<td>{$studentRow['FName']} {$studentRow['LName']}</td>";
                    echo "<td>";
                    echo "<select class='form-select' name='assignments[$studentID]'>";
                    echo "<option value=''>Select Tutor</option>"; // Placeholder option
                    // Loop through tutors and create options in dropdown list
                    while ($tutorRow = mysqli_fetch_assoc($tutorResult)) {
                        echo "<option value='{$tutorRow['TID']}'>{$tutorRow['FName']} {$tutorRow['LName']}</option>";
                    }
                    echo "</select>";
                    echo "</td>";
                    echo "</tr>";
                    // Reset tutor query to beginning for the next student
                    mysqli_data_seek($tutorResult, 0);
                }
                ?>
            </tbody>
        </table>
        <?php if ($errorMessage) { ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $errorMessage; ?>
        </div>
        <?php } ?>
        <button type="submit" class="btn btn-primary">Assign Tutors</button>
    </form>
</div>
<?php
}

if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    // Clear the error message from the session
    unset($_SESSION['error_message']);
}
else{
    $errorMessage = null;
}
// Check if admin ID is set in the session
if (isset($_SESSION['AID'])) {
    $AID = $_SESSION['AID']; // Get the admin ID from the session

    // Prepare SQL query to log system activity
    $activity_type = "Assign Students to Tutors";
    $page_name = "adminDashboard.php";
 $full_user_agent = $_SERVER['HTTP_USER_AGENT'];
 // Regular expression to extract the browser name
if (preg_match('/Edg\/([\d.]+)/i', $full_user_agent, $matches)) {
    $browser_name = 'Edge';
} elseif (preg_match('/(Firefox|Chrome|Safari|Opera)/i', $full_user_agent, $matches)) {
    $browser_name = $matches[1];
} else {
    $browser_name = "Unknown"; // Default to "Unknown" if browser name cannot be determined
}
    $user_id = $AID;
    $user_type = "Admin";

    $insert_query = "INSERT INTO SystemActivity (UserID, UserType, ActivityType, PageName, BrowserName) 
                     VALUES ('$user_id', '$user_type', '$activity_type', '$page_name', '$browser_name')";

    // Execute the query
    if ($conn->query($insert_query) !== TRUE) {
        // Handle error if insert query fails
        echo "Error inserting system activity: " . $conn->error;
    }
}
else {
    echo "Admin ID not found in session.";
}

// Fetch student list from the database
$studentQuery = "SELECT s.SID, s.FName, s.LName 
                FROM Student s 
                LEFT JOIN StudentAssignment sa ON s.SID = sa.SID 
                WHERE sa.SID IS NULL";

$studentResult = mysqli_query($conn, $studentQuery);

// Fetch tutor list from the database
$tutorQuery = "SELECT TID, FName, LName FROM Tutor";
$tutorResult = mysqli_query($conn, $tutorQuery);

assStudentTutorComp($studentResult, $tutorResult, $errorMessage);
?>
