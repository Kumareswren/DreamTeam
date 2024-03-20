<?php
require_once 'tokenVerify.php';
include "db.php";
use \Firebase\JWT\JWT;

function getTutorBlog() {
    global $conn; // Declare $conn as global

    // Your secret key
    $secretKey = 'rNjde95IzZ9CEU1k94aRjHbOX1LvKgM+RX6iv8NfMm8='; // Update with your secret key

    // Retrieve the token from the cookie
    if (isset($_COOKIE['token'])) {
        $token = $_COOKIE['token'];

        try {
            // Decode the token to get the email 
            $decoded = JWT::decode($token, $secretKey, array('HS256'));
            $email = $decoded->email;

            // Query to get the user ID and role based on the email
            $stmt = $conn->prepare("SELECT TID AS UserID, 'Tutor' AS UserRole FROM Tutor WHERE Email = ?");
            $stmt->bind_param("s", $email);

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
                $sql = "SELECT PostID, Title, Content, ImagePath, CreatedAt FROM BlogPost WHERE TutorID = ? ORDER BY PostID DESC";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $userID);

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

                // Output HTML markup for blog component
                echo '<div class="swiper-container" id="blogPostsContainer">';
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

// Call the function to output the tutor's blog component
getTutorBlog();
?>
