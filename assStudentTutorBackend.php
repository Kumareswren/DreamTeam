<?php

session_start();
// Include database connection
include "db.php";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Extract the assignments array from the form data
    if (isset($_POST['assignments']) && is_array($_POST['assignments'])) {
        // Prepare an array to store valid student-tutor assignments
        $validAssignments = array();

        // Loop through the submitted assignments
        foreach ($_POST['assignments'] as $studentID => $tutorID) {
            // Check if a tutor is selected for the student
            if (!empty($tutorID)) {
                // Add the student-tutor pair to the valid assignments array
                $validAssignments[] = array('studentID' => $studentID, 'tutorID' => $tutorID);
            }
        }

        // Perform insertion of valid assignments into the database
        if (!empty($validAssignments)) {
            // Prepare the SQL statement
            $insertQuery = "INSERT INTO StudentAssignment (SID, TID) VALUES (?, ?)";

            // Prepare the statement
            $stmt = mysqli_prepare($conn, $insertQuery);

            // Bind parameters and execute the statement for each valid assignment
            foreach ($validAssignments as $assignment) {
                mysqli_stmt_bind_param($stmt, "ii", $assignment['studentID'], $assignment['tutorID']);
                mysqli_stmt_execute($stmt);
            }

            // Close the statement
            mysqli_stmt_close($stmt);

            // Return a success message
            echo "success";
            exit();
        } else {
            // No valid assignments to insert, handle accordingly (e.g., show error message)
            echo "No valid assignments to insert.";
            exit();
        }
    } else {
        // Handle the case where no assignments array is found in the form data
        echo "No assignments submitted.";
        exit();
    }
}
?>
