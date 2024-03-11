<?php
use \Firebase\JWT\JWT;
session_start();

include "db.php";
require_once('vendor/autoload.php'); 

$isAdmin = true;

if (!$isAdmin) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $courseName = trim($_POST["course_name"]);
    $courseDescription = trim($_POST["course_description"]);
    $startDate = $_POST["start_date"] . '-01';
    $endDate = $_POST["end_date"] . '-01';
    $tutorID = $_POST["tutor_id"];
    
    // Check if the course with the same name, start date, and tutor ID already exists
    $check_sqlCourse = "SELECT * FROM Course WHERE courseName = ? AND startDate = ? AND TID = ?";
    $check_stmtCourse = $conn->prepare($check_sqlCourse);
    $check_stmtCourse->bind_param("ssi", $courseName, $startDate, $tutorID);
    $check_stmtCourse->execute();
    $check_resultCourse = $check_stmtCourse->get_result();

    if ($check_resultCourse->num_rows > 0) {
        echo "Course with the same name, start date, and tutor already exists.";
        exit();
    }

    // Insert the course into the database
    $sqlCourse = "INSERT INTO Course (courseName, startDate, endDate, courseDesc, TID) VALUES (?, ?, ?, ?, ?)";
    $stmtCourse = $conn->prepare($sqlCourse);
    $stmtCourse->bind_param("ssssi", $courseName, $startDate, $endDate, $courseDescription, $tutorID);

    if ($stmtCourse->execute()) {
        echo "<script>alert('Course created successfully.');</script>";
        unset($_SESSION['message_type']);
        unset($_SESSION['course_creation_error']);
        
        echo "<script>clearForm();</script>";
        exit();
    } else {
        echo "<script>alert('Failed to create course. Please try again later.');</script>";
        exit();
    }
}
?>