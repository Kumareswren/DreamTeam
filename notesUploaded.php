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
    $uploadDir = "upload_notes/";

    // Check if the directory exists, if not, create it
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Handle file upload
    if (isset($_FILES["noteFile"])) {
        $file = $_FILES["noteFile"];

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
                            die("Error in SQL query: " . $conn->error);
                        }

                        $stmtTutor->bind_param("s", $email);
                        $stmtTutor->execute();
                        $resultTutor = $stmtTutor->get_result();

                        // Check if tutor found
                        if ($resultTutor->num_rows > 0) {
                            $rowTutor = $resultTutor->fetch_assoc();
                            $tutorID = $rowTutor['TID'];

                            // Prepare and execute SQL statement to insert file details into the database
                            $noteTitle = isset($_POST['noteTitle']) ? $_POST['noteTitle'] : 'Note Title';
                            $noteDescription = isset($_POST['noteDescription']) ? $_POST['noteDescription'] : 'Note Description';

                            // Retrieve courseId from session
                            $courseId = $_SESSION['courseId'];

                            $sqlInsert = "INSERT INTO Note (tutorID, courseID, noteTitle, noteDescription, noteFilePath) 
                                      VALUES (?, ?, ?, ?, ?)";
                            $stmtInsert = $conn->prepare($sqlInsert);
                            $stmtInsert->bind_param("iisss", $tutorID, $courseId, $noteTitle, $noteDescription, $filePath);

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

                                    $message = (new Swift_Message('New Notes Uploaded'))
                                        ->setFrom(['venturesrsk@gmail.com' => 'System bot'])
                                        ->setTo([$student_email])
                                        ->setBody("Dear student, your tutor has uploaded new notes. You can now access them using the website.");

                                    // Send the message
                                    $result = $mailer->send($message);
                                    if ($result) {
                                        // Email sent successfully
                                        header("HTTP/1.1 200 OK");
                                        echo "Notes uploaded successfully";
                                        exit();
                                    } else {
                                        // Error sending email
                                        http_response_code(500);
                                        echo "Failed to upload notes";
                                        exit();
                                    }
                                } else {
                                    echo "No student found for the given course ID.";
                                }
                            } else {
                                // Send internal server error response
                                http_response_code(500);
                                echo "Failed to insert file details into database.";
                            }
                        } else {
                            // Send not found response
                            http_response_code(404);
                            echo "Tutor not found.";
                        }
                    } catch (Exception $e) {
                        // Send bad request response
                        http_response_code(400);
                        echo "Error decoding token: " . $e->getMessage();
                    }
                } else {
                    // Send bad request response
                    http_response_code(400);
                    echo "Token not found.";
                }
            } else {
                // Send internal server error response
                http_response_code(500);
                echo "Error uploading file.";
            }
        } else {
            // Send bad request response
            http_response_code(400);
            echo "Only PDF, DOC, and DOCX files are allowed.";
        }
    } else {
        // Send bad request response
        http_response_code(400);
        echo "No file uploaded.";
    }
} 
?>
