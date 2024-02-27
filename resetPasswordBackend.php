<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];

    // Check if the email exists in student table
    // Replace 'your_student_table' with the actual name of your student table
    $sql = "SELECT * FROM Student WHERE Email = '$email'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        // Email exists in student table
        $table = 'student';
        $passwordAttribute = 'SPass';
    }

    // If email doesn't exist in student table, check tutor table
    if (!isset($table)) {
        // Replace 'your_tutor_table' with the actual name of your tutor table
        $sql = "SELECT * FROM Tutor WHERE Email = '$email'";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) > 0) {
            // Email exists in tutor table
            $table = 'tutor';
            $passwordAttribute = 'TPass';
        }
    }

    // If email doesn't exist in tutor table, check admin table
    if (!isset($table)) {
        // Replace 'your_admin_table' with the actual name of your admin table
        $sql = "SELECT * FROM Admin WHERE Email = '$email'";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) > 0) {
            // Email exists in admin table
            $table = 'admin';
            $passwordAttribute = 'APass';
        }
    }

    // If email doesn't exist in any table, show error message
    if (!isset($table)) {
        header("Location: resetPassword.php?error=Email%20does%20not%20exist.");
        exit();
    }

    // Generate a random password (6 characters)
    $new_password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);

    // Update password in the respective table
    $update_sql = "UPDATE $table SET $passwordAttribute = '$new_password' WHERE Email = '$email'";
    if (mysqli_query($conn, $update_sql)) {
        // Password updated successfully
        // Send email with the new password (you need to implement email functionality)
        $email_subject = "Password Reset";
        $email_body = "Your new password is: $new_password"; // Customize the email message as needed
        // Replace 'youremail@example.com' with your email address
        mail($email, $email_subject, $email_body, 'From: kumareswren@gmail.com');

        // Redirect to reset success page
        header("Location: index.php");
        exit();
    } else {
        // Error updating password
        header("Location: resetPassword.php?error=Error%20resetting%20password.%20Please%20try%20again.");
        exit();
    }
}
?>
