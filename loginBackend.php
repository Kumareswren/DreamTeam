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
$sqlStudent = "SELECT * FROM student WHERE Email=?";
$sqlTutor = "SELECT * FROM tutor WHERE Email=?";
$sqlAdmin = "SELECT * FROM admin WHERE Email=?";

// Execute the queries
$stmtStudent = $conn->prepare($sqlStudent);
$stmtStudent->bind_param("s", $email);
$stmtStudent->execute();
$resultStudent = $stmtStudent->get_result();

$stmtTutor = $conn->prepare($sqlTutor);
$stmtTutor->bind_param("s", $email);
$stmtTutor->execute();
$resultTutor = $stmtTutor->get_result();

$stmtAdmin = $conn->prepare($sqlAdmin);
$stmtAdmin->bind_param("s", $email);
$stmtAdmin->execute();
$resultAdmin = $stmtAdmin->get_result();

// Check if user credentials match and generate JWT token accordingly
if ($resultStudent->num_rows > 0) {
    $row = $resultStudent->fetch_assoc(); // Fetch the row for the student
    if (password_verify($pass, $row['SPass'])) {
        $token = generateToken($email, 'student', $row['SID'], $userIP, $expirationTime);
        setcookie('token', $token, time() + (86400 * 30), "/");
        // Insert record into Trail table
        $stmt = $conn->prepare("INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?, ?, ?, 'Logged in')");
        $role = 'student';
        $stmt->bind_param("iss", $row['SID'], $role, $userIP); // Pass $role by value
        $stmt->execute();
        header("Location: studentDashboard.php");
        exit();
    }
} elseif ($resultTutor->num_rows > 0) {
    $row = $resultTutor->fetch_assoc(); // Fetch the row for the tutor
    if (password_verify($pass, $row['TPass'])) {
        $token = generateToken($email, 'tutor', $row['TID'], $userIP, $expirationTime);
        setcookie('token', $token, time() + (86400 * 30), "/");
        // Insert record into Trail table
        $stmt = $conn->prepare("INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?, ?, ?, 'Logged in')");
        $role = 'tutor';
        $stmt->bind_param("iss", $row['TID'], $role, $userIP); // Pass $role by value
        $stmt->execute();
        header("Location: tutorDashboard.php");
        exit();
    }
} elseif ($resultAdmin->num_rows > 0) {
    $row = $resultAdmin->fetch_assoc(); // Fetch the row for the admin
    if ($pass === $row['APass']) {
        $token = generateToken($email, 'admin', $row['AID'], $userIP, $expirationTime);
        setcookie('token', $token, time() + (86400 * 30), "/");
        // Insert record into Trail table
        $stmt = $conn->prepare("INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?, ?, ?, 'Logged in')");
        $role = 'admin';
        $stmt->bind_param("iss", $row['AID'], $role, $userIP); // Pass $role by value
        $stmt->execute();
        header("Location: adminDashboard.php");
        exit();
    }
} 

// User not found or invalid credentials
header("Location: index.php?error=Invalid%20email%20or%20password.%20Please%20try%20again");
exit();

$conn->close();
?>
