<?php
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
