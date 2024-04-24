<?php
use \Firebase\JWT\JWT;
session_start();

// Include necessary files and initialize the database connection
include "db.php";
require_once('vendor/autoload.php'); // Include the JWT library

// Check if the user is an admin (you may have a better way to check this)
$isAdmin = true; // Set this based on your authentication logic

if (!$isAdmin) {
    // Redirect to the login page or unauthorized access page
    header("Location: index.php");
    exit();
}
// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $component = $_POST['component'];
    if ($component === 'adminRegister') {
    // Retrieve form data
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $password = $_POST['password'];

    // Check if the email already exists in Student or Tutor table
    $checkSql = "SELECT COUNT(*) as count FROM Student WHERE Email = ? UNION SELECT COUNT(*) as count FROM Tutor WHERE Email = ?";
    $checkStmt = $conn->prepare($checkSql);

    if (!$checkStmt) {
        die("Error in preparing statement: " . $conn->error);
    }

    $checkStmt->bind_param("ss", $email, $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
$rowCount = 0;

    while ($row = $checkResult->fetch_assoc()) {
        $rowCount += $row['count'];
    }

    $checkStmt->close();

    if ($rowCount > 0) {
        // Email already exists, show error message
        echo "Email is already registered";
        exit();
    }

    // Insert new student or tutor into the respective table
    $userType = $_POST['user_type'];

    if ($userType === 'student') {
        $sql = "INSERT INTO Student (FName, LName, Email, Contact, SPass) VALUES (?, ?, ?, ?, ?)";
        
    } elseif ($userType === 'tutor') {
        $sql = "INSERT INTO Tutor (FName, LName, Email, Contact, TPass) VALUES (?, ?, ?, ?, ?)";
    } else {
        // Handle invalid user type
        echo "Invalid user type";
        exit();
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $firstName, $lastName, $email, $contact, $password);

    if ($stmt->execute()) {

        $token = $_COOKIE['token'];
        $secretKey = 'your_secret_key';
        $decoded = JWT::decode($token, $secretKey, array('HS256'));
        $userId = $decoded->userId;
        $userRole = $decoded->role;
        $ipAddress = $_SERVER['REMOTE_ADDR'];

        if ($userType === 'student') {
            $trailAction = "Registered new student: $firstName $lastName";
            $trailSql = "INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?, ?, ?, ?)";
            $trailStmt = $conn->prepare($trailSql);
            $trailStmt->bind_param("isss", $userId, $userRole, $ipAddress, $trailAction);
            $trailStmt->execute();
            $trailStmt->close();
        }

        // Log the registration action to the trail table for tutor
        if ($userType === 'tutor') {
            $trailAction = "Registered new tutor: $firstName $lastName";
            $trailSql = "INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?, ?, ?, ?)";
            $trailStmt = $conn->prepare($trailSql);
            $trailStmt->bind_param("isss", $userId, $userRole, $ipAddress, $trailAction);
            $trailStmt->execute();
            $trailStmt->close();
        }

        // Send registration success email
        $transport = (new Swift_SmtpTransport('smtp.gmail.com', 587, 'tls'))
            ->setUsername('venturesrsk@gmail.com')
            ->setPassword('zohh take gpri knhn');

        $mailer = new Swift_Mailer($transport);

        $message = (new Swift_Message('Registration Successful'))
            ->setFrom(['venturesrsk@gmail.com' => 'System bot'])
            ->setTo([$email])
            ->setBody("Registration Successful $userType.");
        
        // Send the message
        $mailer->send($message);
        echo "success";
        
        
    
         // Log system activity
         $user_id = isset($_SESSION['AID']) ? $_SESSION['AID'] : null;
         $activity_type = "Register user";
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
         $user_type = "Admin";

         $insert_query = "INSERT INTO SystemActivity (UserID, UserType, ActivityType, PageName, BrowserName) 
                          VALUES (?, ?, ?, ?, ?)";
         $insert_stmt = $conn->prepare($insert_query);
         $insert_stmt->bind_param("issss", $user_id, $user_type, $activity_type, $page_name, $browser_name);

         if ($insert_stmt->execute()) {
             
         } else {
             // Handle error if insert query fails
             echo "Error inserting system activity: " . $conn->error;
         }
        exit();
    } else {
        // Registration failed, handle the error (e.g., duplicate email)
        echo "Registration failed";
        exit();
    }

    $stmt->close();
    $conn->close();
}else{}
}
?>