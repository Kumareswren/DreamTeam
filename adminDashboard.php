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

    // Fetch admin information based on the email
    $sql = "SELECT AID, FName, LName, Email FROM Admin WHERE Email='$email'";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Error executing SQL query: " . $conn->error);
    }

    // Check if any rows were returned
    if ($result->num_rows > 0) {
        // Admin found, fetch and display information
        $row = $result->fetch_assoc();
        $adminID = $row['AID'];
        $adminName = $row['FName'] . ' ' . $row['LName'];
        $adminEmail = $row['Email'];

        // Display welcome message along with admin information

        echo "<p>Your Admin ID is: $adminID</p>";
        echo "<p>Your Admin Email is: $adminEmail</p>";
    } else {
        // Admin not found in the database
        echo "<h1>Welcome, Guest</h1>";
        echo "<p>Admin information not found.</p>";
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
    <h1>Welcome, <?php echo $adminName; ?></h1>
    <!-- Display the SName value in an h1 tag -->
</body>
</html>