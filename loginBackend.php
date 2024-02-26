<?php
include "db.php";
require_once('vendor/autoload.php'); // Include the JWT library

use \Firebase\JWT\JWT;

// Retrieve form data (assuming $email and $pass are already populated)
$email = $_POST['email'];
$pass = $_POST['password'];

// Function to generate JWT token
function generateToken($email, $role) {
    $secretKey = 'your_secret_key';
    $payload = ['email' => $email, 'role' => $role];
    return JWT::encode($payload, $secretKey);
}

// SQL query to check if the email and password exist in the database
$sqlStudent = "SELECT * FROM student WHERE Email='$email' AND SPass='$pass'";
$resultStudent = $conn->query($sqlStudent);

$sqlTutor = "SELECT * FROM tutor WHERE Email='$email' AND TPass='$pass'";
$resultTutor = $conn->query($sqlTutor);

$sqlAdmin = "SELECT * FROM admin WHERE Email='$email' AND APass='$pass'";
$resultAdmin = $conn->query($sqlAdmin);

if ($resultStudent->num_rows > 0) {
    // Student found, generate JWT token for student
    $token = generateToken($email, 'student');
    header("Location: studentDashboard.php?token=$token");
    exit();
} elseif ($resultTutor->num_rows > 0) {
    // Tutor found, generate JWT token for tutor
    $token = generateToken($email, 'tutor');
    header("Location: tutorDashboard.php?token=$token");
    exit();
} elseif ($resultAdmin->num_rows > 0) {
    // Admin found, generate JWT token for admin
    $token = generateToken($email, 'admin');
    header("Location: adminDashboard.php?token=$token");
    exit();
} else {
   // User not found or invalid credentials
   header("Location: index.php?error=Invalid%20email%20or%20password.%20Please%20try%20again");
   exit();
}

$conn->close();
?>
