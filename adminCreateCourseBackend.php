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
    $currentYear = date("Y");
    $startDate = $_POST["start_date"] . '-01';
    $endDate = $_POST["end_date"] . '-01';
    $tutorID = $_POST["tutor_id"];
    $AID = $_SESSION['AID']; // Retrieve admin ID from session
    // Prepare SQL query to log system activity
$activity_type = "Create Course";
$page_name = "adminDashboard.php";

$full_user_agent = $_SERVER['HTTP_USER_AGENT'];
// Regular expression to extract the browser name
if (preg_match('/Edg\/([\d.]+)/i', $full_user_agent, $matches)) {
   $browser_name = 'Edge';
} elseif (preg_match('/(Firefox|Chrome|Safari|Opera)/i', $full_user_agent, $matches)) {
   $browser_name = $matches[1];
} else {
   $browser_name = "Unknown"; // Default to "Unknown" if browser name cannot be determined
}

$insert_query = "INSERT INTO SystemActivity (UserID, UserType, ActivityType, PageName, BrowserName) 
                 VALUES ('$AID', 'admin', '$activity_type', '$page_name', '$browser_name')";
if ($conn->query($insert_query) !== TRUE) {
    // Handle error if insert query fails
    echo "<script>alert('Error inserting system activity: " . $conn->error . "');</script>";
    exit();
}

// Calculate the minimum allowed date (one year from the current year)
$minimumDate = strtotime(date("Y-m-d", strtotime("+1 year")));

// Check if selected dates are valid
if ($startDate < $minimumDate || $endDate < $minimumDate) {
    echo "<script>alert('Please select start and end dates that are more than one year from the current year.');</script>";
    exit();
}

    // Server-side validation
    $regex = '/^[A-Za-z\s]+$/'; // Regular expression to match letters and spaces
    if (!preg_match($regex, $courseName)) {
        echo "<script>alert('Please enter a valid string without mathematical symbols in the Course Name field.');</script>";
        exit();
    }

    if (!preg_match($regex, $courseDescription)) {
        echo "<script>alert('Please enter a valid string without mathematical symbols in the Course Description field.');</script>";
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
        
        $token = $_COOKIE['token'];
        $secretKey = 'your_secret_key';
        $decoded = JWT::decode($token, $secretKey, array('HS256'));
        $userId = $decoded->userId;
        $userRole = $decoded->role;
        $ipAddress = $_SERVER['REMOTE_ADDR'];

        $trailAction = "Created course: $courseName";
        $trailSql = "INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?, ?, ?, ?)";
        $trailStmt = $conn->prepare($trailSql);
        $trailStmt->bind_param("isss", $userId, $userRole, $ipAddress, $trailAction);
        $trailStmt->execute();
        $trailStmt->close();

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
