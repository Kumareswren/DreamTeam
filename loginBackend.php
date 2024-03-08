<?php
include "db.php";
require_once('vendor/autoload.php'); // Include the JWT library

use \Firebase\JWT\JWT;

// Retrieve form data (assuming $email and $pass are already populated)
$email = $_POST['email'];
$pass = $_POST['password'];
$expirationTime = time() + 3600;

// Function to generate JWT token
function generateToken($email, $role, $expirationTime) {
    $secretKey = 'your_secret_key';
    $payload = ['email' => $email, 'role' => $role, 'time' => $expirationTime];
    return JWT::encode($payload, $secretKey);
}

// SQL query to check if the email and password exist in the database
$sqlStudent = "SELECT * FROM student WHERE Email=? AND SPass=?";
$stmtStudent = $conn->prepare($sqlStudent);
$stmtStudent->bind_param("ss", $email, $pass);
$stmtStudent->execute();
$resultStudent = $stmtStudent->get_result();

$sqlTutor = "SELECT * FROM tutor WHERE Email=? AND TPass=?";
$stmtTutor = $conn->prepare($sqlTutor);
$stmtTutor->bind_param("ss", $email, $pass);
$stmtTutor->execute();
$resultTutor = $stmtTutor->get_result();

$sqlAdmin = "SELECT * FROM admin WHERE Email=? AND APass=?";
$stmtAdmin = $conn->prepare($sqlAdmin);
$stmtAdmin->bind_param("ss", $email, $pass);
$stmtAdmin->execute();
$resultAdmin = $stmtAdmin->get_result();


if ($resultStudent->num_rows > 0) {
    // Student found, generate JWT token for student
    $token = generateToken($email, 'student', $expirationTime);
    setcookie('token', $token, time() + (86400 * 30), "/");
    header("Location: studentDashboard.php");
    exit();
} elseif ($resultTutor->num_rows > 0) {
    // Tutor found, generate JWT token for tutor
    $token = generateToken($email, 'tutor', $expirationTime);
    setcookie('token', $token, time() + (86400 * 30), "/");
    header("Location: tutorDashboard.php");
    exit();
} elseif ($resultAdmin->num_rows > 0) {
    // Admin found, generate JWT token for admin
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
