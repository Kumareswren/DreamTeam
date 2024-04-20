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
    /* $tid = $_SESSION["TID"]; */
    $token = $_COOKIE['token'];

// Decode the JWT token to extract the email
$decoded = JWT::decode($token, 'your_secret_key', array('HS256'));
$user_email = $decoded->email;

// Check if the user exists in the database
$sql_check_user = "SELECT * FROM Tutor WHERE Email = '$user_email'";
$result_check_user = $conn->query($sql_check_user);
$row = $result_check_user->fetch_assoc();
$tid = $row['TID'];

    // Insert message into database table
    $sql = "INSERT INTO Messages (TID, SID, messageContent, sender_type, receiver_type) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        // Assuming the sender is a Tutor and receiver is a Student
        $sender_type = "Tutor";
        $receiver_type = "Student";
        $stmt->bind_param("iisss", $tid, $sid, $message, $sender_type, $receiver_type);
        if ($stmt->execute()) {
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
                    ->setFrom(['venturesrsk@gmail.com' => 'Your Tutor'])
                    ->setTo([$student_email])
                    ->setBody('Dear student, you have received a new message from your tutor. You can view it in the website. Thanks.');

                // Send the message
                $result = $mailer->send($message);
                if (!$result) {
                    // Error sending email
                    echo "Error sending email to student.";
                }
            } else {
                echo "Student email not found.";
            }

        } else {
            // Error occurred while executing query
            http_response_code(500);
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Error occurred in preparing statement
        http_response_code(500);
        echo "Error: " . $conn->error;
    }
} else {
    // Invalid request or missing parameters
    http_response_code(400);
    echo "Error occurred: No message found.";
}
?>
