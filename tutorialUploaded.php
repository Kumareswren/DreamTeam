<?php
include 'db.php';
include 'tutorUploadSession.php';
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Define the directory where you want to save the uploaded files
    $uploadDir = "upload_tutorial/";

    // Check if the directory exists, if not, create it
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Handle file upload
    if (isset($_FILES["tutorialFile"])) {
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
                // Retrieve tutorID from token
                if (isset($_COOKIE['token'])) {
                    $token = $_COOKIE['token'];

                    // Decode the token to get the email 
                    $secretKey = 'your_secret_key'; // Update with your secret key
                    try {
                        $decoded = JWT::decode($token, $secretKey, array('HS256'));
                        $email = $decoded->email;

                        // Query to get the TID for the tutor's email
                        $sqlTutor = "SELECT TID FROM Tutor WHERE Email=?";
                        $stmtTutor = $conn->prepare($sqlTutor);
                        if (!$stmtTutor) {
                            header("HTTP/1.1 500 Internal Server Error");
                            die("Error in SQL query: " . $conn->error);
                        }

                        $stmtTutor->bind_param("s", $email);
                        $stmtTutor->execute();
                        $resultTutor = $stmtTutor->get_result();

                        // Check if tutor found
                        if ($resultTutor->num_rows > 0) {
                            $rowTutor = $resultTutor->fetch_assoc();
                            $tutorID = $rowTutor['TID'];//in tutorCourses.php - line 66 - it starts with $TID not$tutorID

                            // Prepare and execute SQL statement to insert file details into the database
                            $tutorialTitle = isset($_POST['tutorialTitle']) ? $_POST['tutorialTitle'] : 'Tutorial Title';
                            $tutorialDescription = isset($_POST['tutorialDescription']) ? $_POST['tutorialDescription'] : 'Tutorial Description';
                            
                            // Retrieve courseId from session
                            $courseId = isset($_SESSION['courseId']) ? $_SESSION['courseId'] : '';

                            $sqlInsert = "INSERT INTO Tutorial (tutorID, courseID, tutorialTitle, tutorialDescription, tutorialFilePath) 
                                          VALUES (?, ?, ?, ?, ?)";
                            $stmtInsert = $conn->prepare($sqlInsert);
                            $stmtInsert->bind_param("iisss", $tutorID, $courseId, $tutorialTitle, $tutorialDescription, $filePath);
                            
                            // Execute the SQL statement
                            if ($stmtInsert->execute()) {
                                // Retrieve student email associated with the matching course ID
                                $sqlStudentEmail = "SELECT Student.Email
                                                    FROM Student
                                                    INNER JOIN CourseStudent ON Student.SID = CourseStudent.SID
                                                    WHERE CourseStudent.courseID = ?";
                                $stmtStudentEmail = $conn->prepare($sqlStudentEmail);
                                $stmtStudentEmail->bind_param("i", $courseId);
                                $stmtStudentEmail->execute();
                                $resultStudentEmail = $stmtStudentEmail->get_result();

                                if ($resultStudentEmail->num_rows > 0) {
                                    $rowStudentEmail = $resultStudentEmail->fetch_assoc();
                                    $student_email = $rowStudentEmail['Email'];

                                    // Send email notification to students
                                    $mailer = new Swift_Mailer($transport);

                                    $message = (new Swift_Message('New Tutorial Uploaded'))
                                        ->setFrom(['venturesrsk@gmail.com' => 'System bot'])
                                        ->setTo([$student_email])
                                        ->setBody("Dear student, your tutor has uploaded new tutorial. You can access and submit your answer in the website. Thanks.");

                                    // Send the message
                                    $result = $mailer->send($message);
                                $actionPerformed = "Uploaded tutorial: $tutorialTitle";
                                $sqlTrail = "INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?, ?, ?, ?)";
                                $stmtTrail = $conn->prepare($sqlTrail);
                                if (!$stmtTrail) {
                                    // Send internal server error response
                                    http_response_code(500);
                                    echo "Error preparing trail statement: " . $conn->error;
                                    exit;
                                }
                                // Retrieve userID and userRole from decoded token
                                $userId = $decoded->userId;
                                $userRole = $decoded->role;
                                $ipAddress = $_SERVER['REMOTE_ADDR'];
                                $stmtTrail->bind_param("isss", $userId, $userRole, $ipAddress, $actionPerformed);
                                if (!$stmtTrail->execute()) {
                                    // Send internal server error response
                                    http_response_code(500);
                                    echo "Error inserting into trail table: " . $stmtTrail->error;
                                    exit;
                                }
                                header("HTTP/1.1 200 OK");
                                echo "Tutorial uploaded successfully";
                            } else {
                                // Set HTTP status code for error
                                header("HTTP/1.1 500 Internal Server Error");
                                echo "Failed to insert file details into database.";
                            }
                        } else {
                             // Set HTTP status code for error (tutor not found)
                             header("HTTP/1.1 404 Not Found");
                             echo "Tutor not found.";
                        }}
                    } catch (Exception $e) {
                        // Set HTTP status code for error (token decoding error)
                        header("HTTP/1.1 400 Bad Request");
                        echo "Error decoding token: " . $e->getMessage();
                    }
                } else {
                     // Set HTTP status code for error (token not found)
                     header("HTTP/1.1 400 Bad Request");
                     echo "Token not found.";
                }
            } else {
               // Set HTTP status code for error (file upload error)
               header("HTTP/1.1 500 Internal Server Error");
               echo "Error uploading file.";
            }
        } else {
             // Set HTTP status code for error (invalid file type)
             header("HTTP/1.1 400 Bad Request");
             echo "Only PDF, DOC, and DOCX files are allowed.";
        }
    } else {
        // Set HTTP status code for error (no file uploaded)
        header("HTTP/1.1 400 Bad Request");
        echo "No file uploaded.";
    }
}
?>
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> -->