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

    $AID = $_SESSION['AID']; // Retrieve admin ID from session
    // Prepare SQL query to log system activity
$activity_type = "Create Course";
$page_name = "adminDashboard.php";
$browser_name = $_SERVER['HTTP_USER_AGENT'];

$insert_query = "INSERT INTO SystemActivity (UserID, UserType, ActivityType, PageName, BrowserName) 
                 VALUES ('$AID', 'admin', '$activity_type', '$page_name', '$browser_name')";
if ($conn->query($insert_query) !== TRUE) {
    // Handle error if insert query fails
    echo "<script>alert('Error inserting system activity: " . $conn->error . "');</script>";
    exit();
}
    // Server-side validation
    $regex = '/^[A-Za-z\s]+$/'; // Regular expression to match letters and spaces
    if (!preg_match($regex, $courseName)) {
        echo "<script>alert('Please enter a valid string without mathematical symbols in the Course Name field.');</script>";
        exit();
    }

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
        // Fetch the tutor's email address from the database
        $getTutorEmailSQL = "SELECT email FROM Tutor WHERE TID = ?";
        $getTutorEmailStmt = $conn->prepare($getTutorEmailSQL);
        $getTutorEmailStmt->bind_param("i", $tutorID);
        $getTutorEmailStmt->execute();
        $getTutorEmailResult = $getTutorEmailStmt->get_result();

        if ($getTutorEmailResult->num_rows > 0) {
            $tutorData = $getTutorEmailResult->fetch_assoc();
            $tutorEmail = $tutorData['email'];

            // Create and send the email to the tutor
            $transport = (new Swift_SmtpTransport('smtp.gmail.com', 587, 'tls')) // Replace with your SMTP server details
                ->setUsername('venturesrsk@gmail.com')
                ->setPassword('zohh take gpri knhn');
            
            $mailer = new Swift_Mailer($transport);
             
            $message = (new Swift_Message('Course Assigned'))
                ->setFrom(['venturesrsk@gmail.com' => 'System bot'])
                ->setTo([$tutorEmail]) // Use the tutor's email fetched from the database
                ->setBody($courseName . " has been assigned to you. You can check it in your dashboard");
            
            // Send the message
            $mailer->send($message);
        }

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
