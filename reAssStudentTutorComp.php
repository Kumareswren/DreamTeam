<?php
include "db.php";

function reAssStudentTutorComp($conn, $studentResult, $tutorResult, $errorMessage) {
?>
<div class="container">
    <h2 class="mt-5 mb-4">Re-assign Students to Tutors</h2>
    <form action="assStudentTutorBackend.php" method="post">
        <table class="table">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Assigned Tutor</th>
                    <th>New Tutor</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($studentRow = mysqli_fetch_assoc($studentResult)) {
                    $studentID = $studentRow['SID'];
                    echo "<tr>";
                    echo "<td>{$studentRow['FName']} {$studentRow['LName']}</td>";

                    // Get the currently assigned tutor for the student
                    $assignedTutorQuery = "SELECT t.FName, t.LName 
                                           FROM Tutor t
                                           INNER JOIN StudentAssignment sa ON t.TID = sa.TID
                                           WHERE sa.SID = $studentID";
                    $assignedTutorResult = mysqli_query($conn, $assignedTutorQuery);
                    $assignedTutor = mysqli_fetch_assoc($assignedTutorResult);

                    // Display the currently assigned tutor
                    echo "<td>{$assignedTutor['FName']} {$assignedTutor['LName']}</td>";

                    // Display dropdown list for new tutor
                    echo "<td>";
                    echo "<select class='form-select' name='assignments[$studentID]'>";
                    echo "<option value=''>Select New Tutor</option>"; // Placeholder option

                    // Loop through tutors and create options in dropdown list
                    mysqli_data_seek($tutorResult, 0);
                    while ($tutorRow = mysqli_fetch_assoc($tutorResult)) {
                        // Exclude the currently assigned tutor from the dropdown list
                        if ($tutorRow['TID'] != $assignedTutor['TID']) {
                            echo "<option value='{$tutorRow['TID']}'>{$tutorRow['FName']} {$tutorRow['LName']}</option>";
                        }
                    }
                    echo "</select>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        <?php if ($errorMessage) { ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $errorMessage; ?>
        </div>
        <?php } ?>
        <button type="submit" class="btn btn-primary">Assign New Tutors</button>
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

// Fetch student list from the database
$studentQuery = "SELECT s.SID, s.FName, s.LName 
                FROM Student s 
                INNER JOIN StudentAssignment sa ON s.SID = sa.SID";

$studentResult = mysqli_query($conn, $studentQuery);

// Fetch tutor list from the database
$tutorQuery = "SELECT TID, FName, LName FROM Tutor";
$tutorResult = mysqli_query($conn, $tutorQuery);

reAssStudentTutorComp($conn, $studentResult, $tutorResult, $errorMessage);
?>
