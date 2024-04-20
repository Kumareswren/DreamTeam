<?php
session_start();
include 'db.php';
require_once('vendor/autoload.php');

use \Firebase\JWT\JWT;

try {
    // Create the Transport instance (using SMTP transport for example)
    $transport = (new Swift_SmtpTransport('smtp.gmail.com', 587, 'tls')) 
    ->setUsername('venturesrsk@gmail.com')
    ->setPassword('zohh take gpri knhn');

    //****************here************************** */
$mailer = new Swift_Mailer($transport);
} catch (Exception $e) {
    // Handle any errors that occur during transport/mail creation
    echo "Error creating mailer: " . $e->getMessage();
    exit(); // Exit the script if mailer creation fails
}

function sendResponse($statusCode, $message) {
    http_response_code($statusCode);
    echo $message;
}


//collect email from jwt token , select SID from Student where email = $email
// Retrieve the token from the cookie - change to student table stuff
if (isset($_COOKIE['token'])) {
    $token = $_COOKIE['token'];

    $secretKey = 'your_secret_key'; // decoding token 2get email part here
    try {
        $decoded = JWT::decode($token, $secretKey, array('HS256'));
        $email = $decoded->email;

        // Query to get the SID
        $sqlStudent = "SELECT SID FROM student WHERE Email=?";
        $stmtStudent = $conn->prepare($sqlStudent);
        if (!$stmtStudent) {
            die("Error in SQL query: " . $conn->error);
        }

        $stmtStudent->bind_param("s", $email);
        $stmtStudent->execute();
        $resultStudent = $stmtStudent->get_result();

        // Check if tutor found
        if ($resultStudent->num_rows > 0) {
            $rowStudent = $resultStudent->fetch_assoc();
            $SID = $rowStudent['SID']; 
            $_SESSION['SID'] = $SID;
        }
            
    } catch (Exception $e) {
        // Handle JWT decoding exception
        header("HTTP/1.1 400 Bad Request");
        echo "Error decoding JWT: " . $e->getMessage();
        exit;
    }
           

/* var_dump($_GET['tutorialID']);  */ // Debugging: Check tutorialID received via GET
/* var_dump($_POST['tutorialID']); */

// Handle file upload
if (isset($_FILES["tutorialFile"])) {
    // Define the directory where you want to save the uploaded files
    $uploadDir = "submit_answer/";

    // Check if the directory exists, if not, create it
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $file = $_FILES["tutorialFile"];

    // Get the file extension
    $fileExtension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

    // Generate a unique name for the file to prevent overwriting existing files
    $fileName = uniqid() . '.' . $fileExtension;

    // Define allowed file types
    $allowedTypes = array('pdf', 'doc', 'docx');

    // Check if the file type is allowed
    if (in_array($fileExtension, $allowedTypes)) {
        $filePath = $uploadDir . $fileName;

        // Move the uploaded file to the desired directory
        if (move_uploaded_file($file["tmp_name"], $filePath)) {
            // Retrieve tutorialID and SID from session or form data
            $tutorialID = isset($_POST['tutorialID']) ? $_POST['tutorialID'] : ''; // use this line to take tutorialID as POST for student submitting answer for specific tutorials
            $SID = isset($_SESSION['SID']) ? $_SESSION['SID'] : ''; // Check if SID is set in the session
            $tutorialAnswerTitle = isset($_POST['tutorialAnswerTitle']) ? $_POST['tutorialAnswerTitle'] : ''; // Check if tutorialAnswerTitle is set in the form

            if ($SID !== '' && $tutorialAnswerTitle !== '') {
                // Prepare and execute SQL statement to insert file details into the database
                $sqlInsert = "INSERT INTO TutorialAnswer (tutorialID, SID, tutorialAnswerTitle, tutorialAnswerFilePath) 
                              VALUES (?, ?, ?, ?)";
                $stmtInsert = $conn->prepare($sqlInsert);
                $stmtInsert->bind_param("iiss", $tutorialID, $SID, $tutorialAnswerTitle, $filePath);

                // Execute the SQL statement
                if ($stmtInsert->execute()) {
                     // Retrieve the tutor's email
    $sqlGetTutorEmail = "SELECT Tutor.Email
    FROM Tutor
    INNER JOIN StudentAssignment ON Tutor.TID = StudentAssignment.TID
    WHERE StudentAssignment.SID = ?";
$stmtGetTutorEmail = $conn->prepare($sqlGetTutorEmail);
if (!$stmtGetTutorEmail) {
// Handle SQL error
header("HTTP/1.1 500 Internal Server Error");
echo "SQL Error: " . $conn->error;
exit();
}
$stmtGetTutorEmail->bind_param("i", $SID);
if (!$stmtGetTutorEmail->execute()) {
// Handle execution error
header("HTTP/1.1 500 Internal Server Error");
echo "Failed to execute query: " . $stmtGetTutorEmail->error;
exit();
}
$resultTutorEmail = $stmtGetTutorEmail->get_result();

if ($resultTutorEmail->num_rows > 0) {
$rowTutorEmail = $resultTutorEmail->fetch_assoc();
$tutorEmail = $rowTutorEmail['Email'];

// Compose and send email notification to the tutor
$message = (new Swift_Message('New Tutorial Answer Uploaded'))
->setFrom(['venturesrsk@gmail.com' => 'System bot'])
->setTo([$tutorEmail])
->setBody("Dear tutor, a new tutorial answer has been uploaded by a student. You can review it on the website.");

// Send the message
$result = $mailer->send($message);
if (!$result) {
// Error sending email
echo "Error sending email to tutor.";
}
} else {
// Tutor email not found
echo "Tutor email not found.";
}
// Set HTTP status code for success
header("HTTP/1.1 200 OK");
echo "File uploaded successfully.";
} else {
// Set HTTP status code for error
header("HTTP/1.1 500 Internal Server Error");
echo "Failed to insert file details into records.";
                }
            } else {
               // Set HTTP status code for error
               header("HTTP/1.1 400 Bad Request");
               echo "Your submission title or credentials are not found.";
            }
        } else {
           // Set HTTP status code for error
           header("HTTP/1.1 500 Internal Server Error");
           echo "Error uploading file.";
        }
    } else {
        // Set HTTP status code for error
        header("HTTP/1.1 400 Bad Request");
        echo "Only PDF, DOC, and DOCX files are allowed.";
    }
} else {
    // Set HTTP status code for error
    header("HTTP/1.1 400 Bad Request");
    echo "No file uploaded.";
}
} 