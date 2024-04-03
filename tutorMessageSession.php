<?php
// Check if the necessary key exists in the $_POST array
if (isset($_POST['tid'])) {
    // Retrieve tid from the POST data
    $tid = $_POST['tid'];

    // Set the tid session variable
    $_SESSION['TID'] = $tid;

    /* echo "Session variable 'TID' set successfully."; */
} else {
    // If the key is missing, display an error message
    echo "Error: Missing 'tid' in POST data.";
}
?>
