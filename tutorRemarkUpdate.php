<?php
session_start();
include_once 'db.php';
require_once('vendor/autoload.php');

use \Firebase\JWT\JWT;

try {
    // Create the Transport instance (using SMTP transport for example)
    $transport = (new Swift_SmtpTransport('smtp.gmail.com', 587, 'tls'))
        ->setUsername('venturesrsk@gmail.com')
        ->setPassword('zohh take gpri knhn');

    $mailer = new Swift_Mailer($transport);
} catch (Exception $e) {
    // Handle any errors that occur during transport/mail creation
    echo "Error creating mailer: " . $e->getMessage();
    exit(); // Exit the script if mailer creation fails
}

function sendResponse($statusCode, $message)
{
    http_response_code($statusCode);
    echo $message;
}

// Collect email from jwt token , select SID from Student where email = $email
// Retrieve the token from the cookie - change to student table stuff
if (isset($_COOKIE['token'])) {
    $token = $_COOKIE['token'];

    $secretKey = 'your_secret_key'; // decoding token 2get email part here
    try {
        $decoded = JWT::decode($token, $secretKey, array('HS256'));
        $email = $decoded->email;

        // Query to get the SID
        $sqlStudent = "SELECT SID FROM Student WHERE Email=?";
        $stmtStudent = $conn->prepare($sqlStudent);
        if (!$stmtStudent) {
            die("Error in SQL query: " . $conn->error);
        }

        $stmtStudent->bind_param("s", $email);
        $stmtStudent->execute();
        $resultStudent = $stmtStudent->get_result();

        // Check if student found
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

    if (isset($_POST['tutorialAnswerID']) && isset($_POST['tutorComment'])) { // Check if tutorialAnswerID and tutorComment are provided via POST data
        // Sanitize the inputs to prevent SQL injection
        $tutorialAnswerID = mysqli_real_escape_string($conn, $_POST['tutorialAnswerID']);
        $tutorComment = mysqli_real_escape_string($conn, $_POST['tutorComment']);

        $sql = "UPDATE TutorialAnswer SET tutorComment = '$tutorComment' WHERE tutorialAnswerID = $tutorialAnswerID";

        if (mysqli_query($conn, $sql)) {
            // Query executed successfully

            // Retrieve student's email based on tutorialAnswerID
            $sqlStudentEmail = "SELECT Student.Email
                                FROM Student
                                INNER JOIN TutorialAnswer ON Student.SID = TutorialAnswer.SID
                                WHERE TutorialAnswer.tutorialAnswerID = $tutorialAnswerID";
            $resultStudentEmail = mysqli_query($conn, $sqlStudentEmail);

            if ($resultStudentEmail) {
                $rowStudentEmail = mysqli_fetch_assoc($resultStudentEmail);
                $studentEmail = $rowStudentEmail['Email'];

                // Compose email notification
                $message = (new Swift_Message('Tutor Comment Added'))
                    ->setFrom(['venturesrsk@gmail.com' => 'E-tutor'])
                    ->setTo([$studentEmail])
                    ->setBody("Dear student, your tutor has commented on your tutorial answer. You can view it on the website. Thanks.");

                // Send the email
                $result = $mailer->send($message);
                if (!$result) {
                    // Error sending email
                    echo "Error sending email to student.";
                }
            } else {
                echo "Error retrieving student email.";
            }

            $response = array('status' => 'success', 'message' => 'Tutor comment sent successfully.');
            echo json_encode($response);
        } else {
            // Query execution failed
            $response = array('status' => 'error', 'message' => 'Error updating tutor comment: ' . mysqli_error($conn));
            echo json_encode($response);
        }
    } else {
        $response = array('status' => 'error', 'message' => 'POST data not provided.');
        echo json_encode($response);
    }
} else {
    $response = array('status' => 'error', 'message' => 'Unauthorized access.');
    echo json_encode($response);
}

mysqli_close($conn);
?>
