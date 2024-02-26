<?php
require_once('vendor/autoload.php'); // Include the JWT library

use \Firebase\JWT\JWT;

// Retrieve the JWT token from the query parameter 'token'
$token = $_GET['token'] ?? '';

try {
    // Verify and decode the JWT token
    $decoded = JWT::decode($token, 'your_secret_key', array('HS256'));

    // Extract the email value from the decoded token
    $email = $decoded->email ?? 'Guest'; // Assuming 'email' is a property in your JWT payload

    // Connect to your database
    include "db.php";

    // Fetch tutor information based on the email
    $sql = "SELECT TID, FName, LName, Email FROM Tutor WHERE Email='$email'";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Error executing SQL query: " . $conn->error);
    }

    // Check if any rows were returned
    if ($result->num_rows > 0) {
        // Tutor found, fetch and display information
        $row = $result->fetch_assoc();
        $tutorID = $row['TID'];
        $tutorName = $row['FName'] . ' ' . $row['LName'];
        $tutorEmail = $row['Email'];

        // Display welcome message along with tutor information
        echo "<p>Your Tutor ID is: $tutorID</p>";
        echo "<p>Your Tutor Email is: $tutorEmail</p>";
    } else {
        // Tutor not found in the database
        echo "<h1>Welcome, Guest</h1>";
        echo "<p>Tutor information not found.</p>";
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
    <h1>Welcome, <?php echo $tutorName; ?></h1>
    <!-- Display the SName value in an h1 tag -->
</body>
</html>