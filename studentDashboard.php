<?php
require_once('vendor/autoload.php'); // Include the JWT library

use \Firebase\JWT\JWT;

// Retrieve the JWT token from the query parameter 'token'
$token = $_GET['token'] ?? '';

// Verify and decode the JWT token
try {
    $decoded = JWT::decode($token, 'your_secret_key', array('HS256'));
    // Extract the SName value from the decoded token
    $email = $decoded->email ?? 'Guest'; // Assuming 'SName' is a property in your JWT payload

    // Connect to your database
    include "db.php";

    // Fetch student information based on the email
    $sqlStudent = "SELECT SID, FName, LName, Email FROM Student WHERE Email='$email'";
    $resultStudent = $conn->query($sqlStudent);

    if (!$resultStudent) {
        throw new Exception("Error executing SQL query: " . $conn->error);
    }

    if ($resultStudent->num_rows > 0) {
        // Student found, fetch and display information
        $row = $resultStudent->fetch_assoc();
        $studentID = $row['SID'];
        $studentName = $row['FName'] . ' ' . $row['LName'];
        $studentEmail = $row['Email'];

        // Display welcome message along with student information
        echo "<p>Your Student ID is: $studentID</p>";
        echo "<p>Your Email is: $studentEmail</p>";
    } else {
        // Student not found in the database
        echo "<h1>Welcome, Guest</h1>";
        echo "<p>Student information not found.</p>";
    }

    // Close database connection
    $conn->close();

} catch (Exception $e) {
    // Handle token verification errors (e.g., invalid token, expired token)
    // You may want to redirect the user to a login page or display an error message
    echo 'Error: ' . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>
    <h1>Welcome, <?php echo $studentName; ?></h1>
    <!-- Display the SName value in an h1 tag -->
</body>
</html>