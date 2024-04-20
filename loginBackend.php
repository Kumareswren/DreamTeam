<?php
include "db.php";
require_once('vendor/autoload.php'); // Include the JWT library

use \Firebase\JWT\JWT;

// Retrieve form data and sanitize inputs
$email = mysqli_real_escape_string($conn, $_POST['email']);
$pass = mysqli_real_escape_string($conn, $_POST['password']);
$expirationTime = time() + 3600;

// Function to generate JWT token
function generateToken($email, $role, $userId, $ipAddress, $expirationTime) {
    $secretKey = 'your_secret_key';
    $payload = [
        'email' => $email,
        'role' => $role,
        'userId' => $userId,
        'ipAddress' => $ipAddress,
        'time' => $expirationTime
    ];
    return JWT::encode($payload, $secretKey);
}

// Retrieve the user's IP address
$userIP = $_SERVER['REMOTE_ADDR'];

// SQL queries to check user credentials
$sqlStudent = "SELECT * FROM student WHERE Email='$email' AND SPass='$pass'";
$sqlTutor = "SELECT * FROM tutor WHERE Email='$email' AND TPass='$pass'";
$sqlAdmin = "SELECT * FROM admin WHERE Email='$email' AND APass='$pass'";

// Execute the queries
$resultStudent = $conn->query($sqlStudent);
$resultTutor = $conn->query($sqlTutor);
$resultAdmin = $conn->query($sqlAdmin);

// Check if user credentials match and generate JWT token accordingly
// Check if user credentials match and generate JWT token accordingly
if ($resultStudent->num_rows > 0) {
    $row = $resultStudent->fetch_assoc(); // Fetch the row for the student
    $token = generateToken($email, 'student', $row['SID'], $userIP, $expirationTime);
    setcookie('token', $token, time() + (86400 * 30), "/");
    // Insert record into Trail table
    $stmt = $conn->prepare("INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?, ?, ?, 'Logged in')");
    $role = 'student';
    $stmt->bind_param("iss", $row['SID'], $role, $userIP); // Pass $role by value
    $stmt->execute();
    header("Location: studentDashboard.php");
    exit();
} elseif ($resultTutor->num_rows > 0) {
    $row = $resultTutor->fetch_assoc(); // Fetch the row for the tutor
    $token = generateToken($email, 'tutor', $row['TID'], $userIP, $expirationTime);
    setcookie('token', $token, time() + (86400 * 30), "/");
    // Insert record into Trail table
    $stmt = $conn->prepare("INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?, ?, ?, 'Logged in')");
    $role = 'tutor';
    $stmt->bind_param("iss", $row['TID'], $role, $userIP); // Pass $role by value
    $stmt->execute();
    header("Location: tutorDashboard.php");
    exit();
} elseif ($resultAdmin->num_rows > 0) {
    $row = $resultAdmin->fetch_assoc(); // Fetch the row for the admin
    $token = generateToken($email, 'admin', $row['AID'], $userIP, $expirationTime);
    setcookie('token', $token, time() + (86400 * 30), "/");
    // Insert record into Trail table
    $stmt = $conn->prepare("INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?, ?, ?, 'Logged in')");
    $role = 'admin';
    $stmt->bind_param("iss", $row['AID'], $role, $userIP); // Pass $role by value
    $stmt->execute();
    header("Location: adminDashboard.php");
    exit();
} else {
    // User not found or invalid credentials
    header("Location: index.php?error=Invalid%20email%20or%20password.%20Please%20try%20again");
    exit();
}

$conn->close();
?>
