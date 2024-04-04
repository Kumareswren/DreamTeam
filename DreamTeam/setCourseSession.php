<?php
session_start();

// Clear previous session data
$_SESSION['courseId'] = '';
$_SESSION['courseName'] = '';
$_SESSION['startDate'] = '';
$_SESSION['endDate'] = '';

// Retrieve course details from the POST data
$courseId = $_POST['courseId'];
$courseName = $_POST['courseName'];
$startDate = $_POST['startDate'];
$endDate = $_POST['endDate'];

// Set new session variables
$_SESSION['courseId'] = $courseId;
$_SESSION['courseName'] = $courseName;
$_SESSION['startDate'] = $startDate;
$_SESSION['endDate'] = $endDate;

echo "Session variables set successfully.";
?>
