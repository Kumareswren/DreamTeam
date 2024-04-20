<?php
require_once 'tokenVerify.php';
include "db.php";
use \Firebase\JWT\JWT;

function getStudentBlog() {
  global $conn; // Declare $conn as global
    // Your secret key
    $secretKey = 'your_secret_key'; // Update with your secret key

    // Retrieve the token from the cookie
    if (isset($_COOKIE['token'])) {
        $token = $_COOKIE['token'];

        try {
            // Decode the token to get the email 
            $decoded = JWT::decode($token, $secretKey, array('HS256'));
            $email = $decoded->email;

            // Query to get the user ID and role based on the email
            $stmt = $conn->prepare("SELECT SID AS UserID, 'Student' AS UserRole FROM Student WHERE Email = ? UNION SELECT TID AS UserID, 'Tutor' AS UserRole FROM Tutor WHERE Email = ?");
            $stmt->bind_param("ss", $email, $email);

            // Attempt to execute the SQL query
            if (!$stmt->execute()) {
                die("Error executing SQL query: " . $stmt->error);
            }

            // Get the result
            $result = $stmt->get_result();

            // Check if any rows were affected
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $userID = $row['UserID'];
                $userRole = $row['UserRole'];

                // Prepare SQL query to retrieve blog posts with PostID
                $sql = "SELECT PostID, Title, Content, ImagePath, CreatedAt FROM BlogPost WHERE StudentID = ? OR TutorID = ? ORDER BY PostID DESC";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $userID, $userID);

                // Execute the SQL query
                if (!$stmt->execute()) {
                    die("Error executing SQL query: " . $stmt->error);
                }

                // Get the result
                $result = $stmt->get_result();

                // Store blog posts in an array
                $blogPosts = [];
                while ($row = $result->fetch_assoc()) {
                    $row['UserID'] = $userID;
                    $row['UserRole'] = $userRole;
                    $blogPosts[] = $row;
                }
                function deleteBlogPost() {
                  global $conn; // Declare $conn as global
              
                  // Check if postID is set
                  if (isset($_POST['postID'])) {
                      $postID = $_POST['postID'];
              
                      // Prepare SQL query to delete blog post
                      $sql = "DELETE FROM BlogPost WHERE PostID = ?";
                      $stmt = $conn->prepare($sql);
                      $stmt->bind_param("s", $postID);
              
                      // Execute the SQL query
                      if (!$stmt->execute()) {
                          die("Error executing SQL query: " . $stmt->error);
                      }
              
                      // Check if any rows were affected
                      if ($stmt->affected_rows > 0) {
                          echo "Blog post deleted successfully.";
                      } else {
                          echo "Error deleting blog post.";
                      }
                  } else {
                      echo "No postID provided.";
                  }
              }
              
              // Check if action is set
              if (isset($_POST['action'])) {
                  $action = $_POST['action'];
              
                  // Call the appropriate function based on the action
                  switch ($action) {
                      case 'getStudentBlog':
                          getStudentBlog();
                          break;
                      case 'deleteBlogPost':
                          deleteBlogPost();
                          break;
                  }
              }
              
              echo ' <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />';
              echo '<style>';
              echo 'body {';
                echo '    overflow: hidden; /* Disable scrolling */';
                echo '}';
              echo '.button-container {';
              echo '  text-align: left;';
              echo '}';
              echo '';
              echo 'a.createStudentBlog {';
              echo '  display: flex;';
              echo '  justify-content: center;';
              echo '  align-items: center;';
              echo '  width: 100%; /* Add this line */';
              echo '  text-align: center; /* Add this line */';
              echo '  font-size: 0.9em;';
              echo '  text-transform: uppercase;';
              echo '  padding: 10px 20px;';
              echo '  margin: 0px;';
              echo '  background: #333;';
              echo '  border: 1px solid #333;';
              echo '  border-radius: 5px;';
              echo '  color: #fff;';
              echo '  text-decoration: none;';
              echo '  transition: background-color 0.3s ease;';
              echo '}';
              echo '';
              echo 'a.createStudentBlog:hover {';
              echo '  background-color: #fac821;';
              echo '  border-color: #fac821;';
              echo '}';
              echo '';
              echo '.component-card {';
              echo '    position: relative;';
              echo '    margin: 5px; /* Adjust this value according to your needs */';
              echo '    max-width: 300px; /* Adjust this value according to your needs */';
              echo '    box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);';
              echo '    transition: 0.3s;';
              echo '    box-sizing: border-box; /* Add this line */';
              echo '}';
              echo '';
              echo '.component-card:hover {';
              echo '    box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);';
              echo '}';
              echo '';
              echo '.component-card:hover img {';
              echo '    transform: scale(1.1);';
              echo '}';
              echo '';
              echo '.component-card .component-card_image {';
              echo '    background: #fff;';
              echo '    height: 0;';
              echo '    overflow: hidden;';
              echo '    padding-bottom: 56.2%;';
              echo '    position: relative;';
              echo '}';
              echo '';
              echo '.component-card .component-card_image .component-card_image-inside {';
              echo '    height: 100%;';
              echo '    left: 0;';
              echo '    position: absolute;';
              echo '    top: 0;';
              echo '    width: 100%;';
              echo '}';
              echo '';
              echo '.component-card .component-card_image .component-card_image-inside img {';
              echo '    background-size: cover;';
              echo '    height: auto !important;';
              echo '    transform: scale(1);';
              echo '    transition: all .25s ease-in-out;';
              echo '    width: 100%;';
              echo '}';
              echo '';
              echo '.component-card .blog-detail {';
              echo '    background: #fff;';
              echo '    padding: 10px; /* Adjust this value to reduce space around the content */';
              echo '    position: relative;';
              echo '    top: -20px;';
              echo '    max-height: 200px;';
              echo '    overflow: hidden;';
              echo '    box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);';
              echo '}';
              echo '';
              echo '.component-card .blog-detail h3 {';
              echo '    font-size: 18px;';
              echo '    margin: 0;';
              echo '    text-transform: uppercase;';
              echo '    white-space: nowrap;';
              echo '    overflow: hidden;';
              echo '    text-overflow: ellipsis;';
              echo '}';
              echo '';
              echo '.component-card .blog-detail label {';
              echo '    color: #737373;';
              echo '    font-size: 14px;';
              echo '}';
              echo '';
              echo '.component-card .blog-detail p {';
              echo '    margin-bottom: 1rem;';
              echo '    margin-top: 0;';
              echo '    font-size: 14px;';
              echo '    white-space: nowrap;';
              echo '    overflow: hidden;';
              echo '    text-overflow: ellipsis;';
              echo '}';
              echo '';
              echo '.component-card .blog-detail .btn {';
              echo '    background-color: transparent;';
              echo '    border: 1px solid transparent;';
              echo '    border-radius: .25rem;';
              echo '    color: #212529;';
              echo '    display: inline-block;';
              echo '    font-size: 1rem;';
              echo '    font-weight: 400;';
              echo '    line-height: 1.5;';
              echo '    padding: .375rem .75rem;';
              echo '    text-align: center;';
              echo '    transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;';
              echo '    user-select: none;';
              echo '    vertical-align: middle;';
              echo '}';
              echo '';
              echo '.component-card .blog-detail .btn:hover {';
              echo '    background-color: #fac821;';
              echo '    color: #212529;';
              echo '    text-decoration: none;';
              echo '}';
              echo '';
              echo '.component-card .blog-detail .btn-read-more {';
              echo '    background: transparent;';
              echo '    border-radius: 0;';
              echo '    border: 2px solid #fac821;';
              echo '    outline: none;';
              echo '    text-transform: uppercase;';
              echo '    transition: background-color 0.3s ease, border-color 0.3s ease;';
              echo '}';
              echo '';
              echo '.component-card .blog-detail .btn-read-more:hover {';
              echo '    background-color: #fac821;';
              echo '    border-color: #333;';
              echo '}';
              echo '';
              echo '#componentContainer .swiper-container {';
              echo '    overflow: hidden; /* Ensure that the Swiper container does not overflow its parent */';
              echo '    width: 100%; /* Set the width to 100% to fill the parent container */';
              echo '}';
              echo '</style>';
           
              
// Output HTML markup for blog component
 
// Prepare SQL query to log system activity
$activity_type = "Blog";
$page_name = "studentDashboard.php";
$full_user_agent = $_SERVER['HTTP_USER_AGENT'];
// Regular expression to extract the browser name
if (preg_match('/Edg\/([\d.]+)/i', $full_user_agent, $matches)) {
    $browser_name = 'Edge';
} elseif (preg_match('/(Firefox|Chrome|Safari|Opera)/i', $full_user_agent, $matches)) {
    $browser_name = $matches[1];
} else {
    $browser_name = "Unknown"; // Default to "Unknown" if browser name cannot be determined
}

$user_id = $userID; // Assuming $userID holds the student's ID
$user_type = "Student";

$insert_query = "INSERT INTO SystemActivity (UserID, UserType, ActivityType, PageName, BrowserName) 
                 VALUES ('$user_id', '$user_type', '$activity_type', '$page_name', '$browser_name')";
if ($conn->query($insert_query) !== TRUE) {
    // Handle error if insert query fails
    echo "Error inserting system activity: " . $conn->error;
}

echo '<div class="swiper-container">';
echo '<div class="swiper-wrapper">';
foreach ($blogPosts as $post) {

    echo '<div class="swiper-slide col-md-4">';
    echo '<div class="component-card">';
    echo '<div class="component-card_image">';
    echo '<div class="component-card_image-inside">';
    echo '<img src="' . $post['ImagePath'] . '" alt="' . $post['Title'] . '" title="' . $post['Title'] . '" />';
    echo '</div>';
    echo '</div>';
    echo '<div class="blog-detail">';
    echo '<h3>' . $post['Title'] . '</h3>';
    echo '<label>' . $post['CreatedAt'] . '</label>';
    echo '<p>' . $post['Content'] . '</p>';
    echo '<a class="btn btn-read-more" href="viewBlog.php?id=' . $post['PostID'] . '">Read More</a>';



// Edit button
echo '<a class="edit-blog-btn btn btn-primary" data-postid="' . $post['PostID'] . '">Edit blog</a>';



    // Delete button
    echo '<form class="delete-blog-form" method="post" style="display: inline;">';
    echo '<input type="hidden" name="postID" value="' . $post['PostID'] . '">';
    echo '<input type="submit" value="Delete blog" class="btn btn-danger">';
    echo '</form>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
echo '</div>';
echo '</div>';


            } else {
                // User not found
                echo "User not found.";
            }
        } catch (Exception $e) {
            // Token verification failed
            echo "Token verification failed.";
        }
    } else {
        // Token not found
        echo "Token not found.";
    }
}


// Call the function to output the blog component
getStudentBlog();

?>