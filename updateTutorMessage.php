<?php
// Assuming your database connection is established and stored in $conn
include_once 'db.php';
use \Firebase\JWT\JWT;
require_once('vendor/autoload.php'); 
session_start();

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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["message"]) && isset($_POST["sid"])) {
    // Retrieve message and SID from POST data
    $message = $_POST["message"];
    $sid = $_POST["sid"];

    // Retrieve the TID from the session
    $token = $_COOKIE['token'];
    $decoded = JWT::decode($token, 'your_secret_key', array('HS256'));
    $user_email = $decoded->email;

    // Check if the user exists in the database and get the TID
    $sql_check_user = "SELECT * FROM Tutor WHERE Email = ?";
    $stmt_check_user = $conn->prepare($sql_check_user);
    $stmt_check_user->bind_param("s", $user_email);
    if ($stmt_check_user->execute()) {
        $result_check_user = $stmt_check_user->get_result();
        if ($result_check_user->num_rows > 0) {
            $row = $result_check_user->fetch_assoc();
            $tid = $row['TID'];

            // Retrieve student's FName from Student table
            $sql_get_fname = "SELECT FName FROM Student WHERE SID = ?";
            $stmt_get_fname = $conn->prepare($sql_get_fname);
            $stmt_get_fname->bind_param("i", $sid);
            if ($stmt_get_fname->execute()) {
                $result_get_fname = $stmt_get_fname->get_result();
                if ($result_get_fname->num_rows > 0) {
                    $row_fname = $result_get_fname->fetch_assoc();
                    $student_fname = $row_fname['FName'];

                    // Insert message into database table
                    $sql_insert_message = "INSERT INTO Messages (TID, SID, messageContent, sender_type, receiver_type) VALUES (?, ?, ?, ?, ?)";
                    $stmt_insert_message = $conn->prepare($sql_insert_message);
                    if ($stmt_insert_message) {
                        // Assuming the sender is a Tutor and receiver is a Student
                        $sender_type = "Tutor";
                        $receiver_type = "Student";
                        /* $stmt_insert_message->bind_param("iisss", $tid, $sid, $message, $sender_type, $receiver_type); */
                        $sanitized_message = htmlentities($message);
                        $stmt_insert_message->bind_param("iisss", $tid, $sid, htmlentities($message), $sender_type, $receiver_type);
                        if ($stmt_insert_message->execute()) {
                            // Message inserted successfully
                            http_response_code(200);
            echo "Message sent successfully.";

            // Retrieve student's email based on SID
            $sql_student_email = "SELECT Email FROM Student WHERE SID = ?";
            $stmt_student_email = $conn->prepare($sql_student_email);
            $stmt_student_email->bind_param("i", $sid);
            $stmt_student_email->execute();
            $result_student_email = $stmt_student_email->get_result();

            if ($result_student_email->num_rows > 0) {
                $row_student_email = $result_student_email->fetch_assoc();
                $student_email = $row_student_email['Email'];

                // Send email notification to the student
                $message = (new Swift_Message('Dear student, you have received a new message from your tutor'))
                    ->setFrom(['venturesrsk@gmail.com' => 'e-Tutor'])
                    ->setTo([$student_email])
                    ->setBody('Dear student, you have received a new message from your tutor. You can view it in the website. Thanks.');

                // Send the message
                $result = $mailer->send($message);
                            // Prepare the trail action
                            $trailAction = "Sent message to student $student_fname";

                            // Insert a record into the trail table
                            $sql_insert_trail = "INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?, ?, ?, ?)";
                            $stmt_insert_trail = $conn->prepare($sql_insert_trail);
                            if ($stmt_insert_trail) {
                                $userId = $tid; // Assuming the user ID is the Tutor's ID
                                $userRole = "tutor"; // Assuming the user role is a Tutor
                                $ipAddress = $_SERVER['REMOTE_ADDR'];
                                $stmt_insert_trail->bind_param("isss", $userId, $userRole, $ipAddress, $trailAction);
                                if ($stmt_insert_trail->execute()) {
                                    // Trail record inserted successfully
                                    http_response_code(200);
                                    echo "Message sent successfully.";
                                } else {
                                    // Error inserting into trail table
                                    http_response_code(500);
                                    echo "Error inserting into trail table: " . $stmt_insert_trail->error;
                                }
                                $stmt_insert_trail->close();
                            } else {
                                // Error preparing trail statement
                                http_response_code(500);
                                echo "Error preparing trail statement: " . $conn->error;
                            }
                        } else {
                            // Error occurred while executing insert message query
                            http_response_code(500);
                            echo "Error inserting message: " . $stmt_insert_message->error;
                        }
                        $stmt_insert_message->close();
                    } else {
                        // Error preparing insert message statement
                        http_response_code(500);
                        echo "Error preparing insert message statement: " . $conn->error;
                    }
                } else {
                    // Student not found
                    http_response_code(404);
                    echo "Student not found.";
                }
            } else {
                // Error executing get FName query
                http_response_code(500);
                echo "Error executing get FName query: " . $stmt_get_fname->error;
            }
        } else {
            // User not found
            http_response_code(404);
            echo "Tutor not found.";
        }
    } else {
        // Error executing check user query
        http_response_code(500);
        echo "Error executing check user query: " . $stmt_check_user->error;
    }
    $stmt_check_user->close();
}} else {
    // Invalid request or missing parameters
    http_response_code(400);
    echo "Error occurred: No message found.";
}
?>
