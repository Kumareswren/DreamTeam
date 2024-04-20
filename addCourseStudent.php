<?php

// Start the session
session_start();

// Include database connection
require_once('db.php');

// Include JWT library
require_once('vendor/autoload.php'); // Adjust the path as needed

use Firebase\JWT\JWT;

function addCourseStudent() {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Check if the 'token' cookie is set and courseId is stored in the session
    if (isset($_COOKIE['token']) && isset($_SESSION['courseId'])) {
        // Retrieve JWT token from cookie
        $jwtToken = $_COOKIE['token'];

        // Retrieve tutor ID from JWT token
        $tutorId = getTutorIdFromToken($jwtToken);

        // Check if tutor ID retrieved successfully
        if ($tutorId === null) {
            return 'Error: Unable to retrieve tutor information from token.';
        }

        // Retrieve courseId from session
        $courseId = $_SESSION['courseId'];

        // Retrieve students assigned to the tutor
        global $conn; // Assuming $conn is your database connection
        $sql = "SELECT s.SID, s.FName, s.Email
            FROM Student s 
            INNER JOIN StudentAssignment sa ON s.SID = sa.SID
            WHERE sa.TID = ? AND s.SID NOT IN (
                SELECT cs.SID FROM CourseStudent cs WHERE cs.courseID = ?
            )";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $tutorId, $courseId);
        $stmt->execute();
        $result = $stmt->get_result();

        // Generate HTML for the add student form
        $output = '
            <div id="addStudentForm">
                <h3>Add Student</h3>
                <form id="studentForm" method="post" action="addCourseStudent.php">
                    <input type="hidden" name="courseId" value="' . $courseId . '">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>Student Email</th>
                                    <th>Select</th>
                                </tr>
                            </thead>
                            <tbody>';

        // Fetch students assigned to the tutor and generate table rows with checkboxes
        while ($row = $result->fetch_assoc()) {
            $output .= '
                <tr>
                    <td>' . $row['SID'] . '</td>
                    <td>' . $row['FName'] . '</td>
                    <td>' . $row['Email'] . '</td>
                    <td><input type="checkbox" name="selectedStudents[]" value="' . $row['SID'] . '"></td>
                </tr>';
        }

        $output .= '
                            </tbody>
                        </table>
                    </div>
                    <input type="submit" class="btn btn-primary" value="Add Selected Students">
                </form>
            </div>';

        return $output;

    } else {
        // Token cookie not set or courseId not set in session, handle accordingly
        return 'Error: Token cookie not set or courseId not set in session.';
    }
}

// Function to decode JWT token and retrieve tutorID from the Tutor table
function getTutorIdFromToken($jwtToken) {
    // Decode JWT token
    $secretKey = 'your_secret_key'; // Replace with your secret key
    try {
        $decoded = JWT::decode($jwtToken, $secretKey, array('HS256'));
    } catch (Exception $e) {
        // Handle JWT decoding error
        return null;
    }

    // Extract email from decoded token
    $email = $decoded->email;

    // Retrieve tutorID from Tutor table using the email
    global $conn; // Assuming $conn is your database connection
    $sql = "SELECT TID FROM Tutor WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if tutor exists
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['TID'];
    } else {
        // Tutor not found
        return null;
    }
}

function addSelectedStudents($courseId, $selectedStudents) {
    global $conn;

    // Prepare the SQL statement for inserting into CourseStudent table
    $sql = "INSERT INTO CourseStudent (courseID, SID) VALUES ";
    $values = array();
    foreach ($selectedStudents as $studentId) {
        $values[] = "($courseId, $studentId)";
    }
    $sql .= implode(",", $values);

    // Execute the SQL query for inserting into CourseStudent table
    if ($conn->query($sql) === TRUE) {
        $courseName = $_SESSION['courseName']; // Set your course name here
        
        // Fetch students' email addresses and insert into Trail table for each student
        foreach ($selectedStudents as $studentId) {
            // Fetch student details
            $sqlStudent = "SELECT FName, Email FROM Student WHERE SID = $studentId";
            $resultStudent = $conn->query($sqlStudent);
            if ($resultStudent && $rowStudent = $resultStudent->fetch_assoc()) {
                $studentName = $rowStudent['FName'];
                $studentEmail = $rowStudent['Email'];
                
                // Send email to the student
                sendEmailToStudent($studentEmail, $courseName);
                
                // Insert into trail table for this student
                $trailAction = "Added $studentName to course: $courseName";
                $token = $_COOKIE['token'];
                $secretKey = 'your_secret_key';
                $decoded = JWT::decode($token, $secretKey, array('HS256'));
                $userId = $decoded->userId;
                $userRole = $decoded->role;
                $ipAddress = $_SERVER['REMOTE_ADDR'];

                $trailSql = "INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?, ?, ?, ?)";
                $trailStmt = $conn->prepare($trailSql);
                $trailStmt->bind_param("isss", $userId, $userRole, $ipAddress, $trailAction);
                $trailStmt->execute();
            }
        }

        return true; // Insertion successful
    } else {
        return false; // Insertion failed
    }
}

function sendEmailToStudent($email, $courseName) {
    // Configure Swift Mailer
    $transport = (new Swift_SmtpTransport('smtp.gmail.com', 587, 'tls'))
        ->setUsername('venturesrsk@gmail.com')
        ->setPassword('zohh take gpri knhn'); // Replace with your email password

    $mailer = new Swift_Mailer($transport);

    // Compose the email message
    $message = (new Swift_Message('Course Assigned'))
        ->setFrom(['venturesrsk@gmail.com' => 'System bot'])
        ->setTo([$email])
        ->setBody("You have been assigned to $courseName course. Check your dashboard");

    // Send the email
    $mailer->send($message);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve courseId from the session
    session_start();
    $courseId = $_SESSION['courseId'];

            if (isset($_POST['selectedStudents'])) {
                $selectedStudents = $_POST['selectedStudents'];

                // Add selected students to the CourseStudent table
                $success = addSelectedStudents($courseId, $selectedStudents);

                if ($success) {
                    // Insertion successful, show alert message
                    echo "<script>alert('Students added successfully to the course!');</script>";
                } else {
                    // Insertion failed, show error message
                    echo "<script>alert('Error adding students to the course. Please try again later.');</script>";
                }
            } else {
                // No students selected, show warning message
                echo "<script>alert('No students selected.');</script>";
            }
        }

// Call the function to generate the add student form
echo addCourseStudent();

?>
