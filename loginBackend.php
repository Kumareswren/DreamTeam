<?php
include "db.php";
require_once('vendor/autoload.php'); // Include the JWT library

use \Firebase\JWT\JWT;

// Retrieve form data (assuming $email and $pass are already populated)
$email = $_POST['email'] ?? '';
$pass = $_POST['password'] ?? '';
$expirationTime = time() + 3600;

// Function to generate JWT token
function generateToken($email, $role, $expirationTime) {
    $secretKey = 'rNjde95IzZ9CEU1k94aRjHbOX1LvKgM+RX6iv8NfMm8=';
    $payload = ['email' => $email, 'role' => $role, 'time' => $expirationTime];
    return JWT::encode($payload, $secretKey);
}

// SQL query to check if the email and password exist in the database
$sqlStudent = "SELECT * FROM Student WHERE Email='$email' AND SPass='$pass'";
$resultStudent = $conn->query($sqlStudent);

$sqlTutor = "SELECT * FROM Tutor WHERE Email='$email' AND TPass='$pass'";
$resultTutor = $conn->query($sqlTutor);

$sqlAdmin = "SELECT * FROM Admin WHERE Email='$email' AND APass='$pass'";
$resultAdmin = $conn->query($sqlAdmin);

// Check if the SQL queries executed successfully
if (!$resultStudent || !$resultTutor || !$resultAdmin) {
    // Error occurred in executing SQL query
    die("Error in executing SQL query: " . $conn->error);
}

// Check if any rows are returned for student
if ($resultStudent->num_rows > 0) {
    // Student exists, update last login time
    $row = $resultStudent->fetch_assoc();
    $studentId = $row['SID'];
    $currentTime = date('Y-m-d H:i:s');
    $updateQuery = "UPDATE Student SET last_login='$currentTime' WHERE SID='$studentId'";
    if (!$conn->query($updateQuery)) {
        // Error occurred in executing update query
        die("Error updating last login time: " . $conn->error);
    }
} elseif ($resultTutor->num_rows > 0) {
    // Tutor exists, update last login time
    $row = $resultTutor->fetch_assoc();
    $tutorId = $row['TID'];
    $currentTime = date('Y-m-d H:i:s');
    $updateQuery = "UPDATE Tutor SET last_login='$currentTime' WHERE TID='$tutorId'";
    if (!$conn->query($updateQuery)) {
        // Error occurred in executing update query
        die("Error updating last login time: " . $conn->error);
    }
} elseif ($resultAdmin->num_rows > 0) {
    // Admin exists, update last login time
    $row = $resultAdmin->fetch_assoc();
    $adminId = $row['AID'];
    $currentTime = date('Y-m-d H:i:s');
    $updateQuery = "UPDATE Admin SET last_login='$currentTime' WHERE AID='$adminId'";
    if (!$conn->query($updateQuery)) {
        // Error occurred in executing update query
        die("Error updating last login time: " . $conn->error);
    }
}

// Generate JWT token and set cookie
if ($resultStudent->num_rows > 0) {
    $token = generateToken($email, 'student', $expirationTime);
    setcookie('token', $token, time() + (86400 * 30), "/");
    header("Location: studentDashboard.php");
    exit();
} elseif ($resultTutor->num_rows > 0) {
    $token = generateToken($email, 'tutor', $expirationTime);
    setcookie('token', $token, time() + (86400 * 30), "/");
    header("Location: tutorDashboard.php");
    exit();
} elseif ($resultAdmin->num_rows > 0) {
    $token = generateToken($email, 'admin', $expirationTime);
    setcookie('token', $token, time() + (86400 * 30), "/");
    header("Location: adminDashboard.php");
    exit();
} else {
    // User not found or invalid credentials
    header("Location: index.php?error=Invalid%20email%20or%20password.%20Please%20try%20again");
    exit();
}

$conn->close();
?>
