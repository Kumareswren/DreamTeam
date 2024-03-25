<?php
require_once 'tokenVerify.php';
include "db.php";
use \Firebase\JWT\JWT;

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check if the form has been submitted with the updated blog post data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['PostID'], $_POST['Title'], $_POST['Content'], $_FILES['ImagePath'])) {
    // Get the PostID, Title, Content, and ImagePath from the POST request
    $postID = $_POST['PostID'];
    $title = $_POST['Title'];
    $content = $_POST['Content'];

    // Handle the file upload
    $target_dir = "media/"; // Specify the directory where you want to save the uploaded files
    $target_file = $target_dir . basename($_FILES["ImagePath"]["name"]);
    move_uploaded_file($_FILES["ImagePath"]["tmp_name"], $target_file);

    // Prepare SQL query to update the blog post
    $sql = "UPDATE BlogPost SET Title = ?, Content = ?, ImagePath = ? WHERE PostID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $title, $content, $target_file, $postID);

    // Execute the SQL query
    if (!$stmt->execute()) {
        die("Error executing SQL query: " . $stmt->error);
    }

    // Redirect to viewBlog.php
    header('Location: viewBlog.php?id=' . $postID);
    exit;
} else if (isset($_GET['id'])) {
    // Get the PostID from the URL parameter
    $postID = $_GET['id'];

    // Validate PostID - it should be an integer
    if (!filter_var($postID, FILTER_VALIDATE_INT)) {
        die("Invalid PostID.");
    }

    // Prepare SQL query to retrieve the blog post with the given PostID
    $sql = "SELECT PostID, Title, Content, ImagePath, CreatedAt FROM BlogPost WHERE PostID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $postID); // Use "i" for integer
    
    // Execute the SQL query
    if (!$stmt->execute()) {
        die("Error executing SQL query: " . $stmt->error);
    }

    // Get the result
    $result = $stmt->get_result();

    // Check if any rows were affected
    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
    } else {
        // Post not found
        echo "Post not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
.main {
    width: 100%;
    height: auto;
}

.main-container {
    background: #fff;
    border: 1px solid rgba(0, 0, 0, .12);
    border-radius: 5px;
    padding: 25px 30px;
    margin: 0 auto;
    max-width: 960px;
}

form {
    padding: 30px;
    background: #f8f9fa;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
}

label {
    display: block;
    font-size: 1em;
    padding-bottom: 10px;
    color: #495057;
}

input, textarea, select {
    font-size: 1em;
    width: 100%;
    margin: 0;
    padding: 15px 12px;
    box-sizing: border-box;
    border: 1px solid rgba(0, 0, 0, .12);
    border-radius: 5px;
    box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
    transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
}

input:focus, textarea:focus, select:focus {
    outline: none;
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.form-field {
    margin-bottom: 20px;
}

input[type="checkbox"] {
    height: 20px;
    width: 20px;
}

input[readonly] {
    background: #e9ecef;
}

button.publish {
    background: #007bff;
    border-color: #007bff;
    color: white;
    transition: background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
}

button.publish:hover {
    background: #0056b3;
    border-color: #0056b3;
}

@media (max-width: 480px) {
    .main-container {
        padding: 20px 30px;
        margin: 0 -16px 20px;
        border-radius: 0;
    }
}
</style>

</head>

<body>
          <div class="main"> 
            <div class="main-content">
              <div class="main-container new-blog">
              <div class="new-blog-form">
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
        <?php if(isset($_GET['id'])): ?>
            <!-- Your existing code for when $_GET['id'] is set -->
            <input type="hidden" name="PostID" value="<?php echo $post['PostID']; ?>">
            <div class="form-field">
                <label for="blog-title">Blog Title</label>
                <input id="blog-title" type="text" name="Title" value="<?php echo $post['Title']; ?>" required>
            </div>
            <div class="form-field">
                <label for="blog-description">Blog Description</label>
                <textarea id="blog-description" rows="3" name="Content" required><?php echo $post['Content']; ?></textarea>
            </div>
            <div class="form-field">
                <!-- Display the current image -->
                <img src="<?php echo $post['ImagePath']; ?>" alt="Current Image" style="width: 100px; height: 100px;">
                <label for="upload-image">Upload Image</label>
                <input id="upload-image" type="file" name="ImagePath" accept="image/*" required>
            </div>
        <?php else: ?>
            <!-- Your existing code for when $_GET['id'] is not set -->
            <input type="hidden" name="PostID" value="0">
            <div class="form-field">
                <label for="blog-title">Blog Title</label>
                <input id="blog-title" type="text" name="Title" required>
            </div>
            <div class="form-field">
                <label for="blog-description">Blog Description</label>
                <textarea id="blog-description" rows="3" name="Content" required></textarea>
            </div>
            <div class="form-field">
                <label for="upload-image">Upload Image</label>
                <input id="upload-image" type="file" name="ImagePath" accept="image/*" required>
            </div>
        <?php endif; ?>
        <!-- Hidden fields for user role and email -->
        <input type="hidden" name="user-role" value="<?php echo $userRole; ?>">
        <input type="hidden" name="user-email" value="<?php echo $userEmail; ?>">
        
        <button class="publish" type="submit">Publish</button>
    </form>
</div>
</div>



                      <br>
                      </div>
        
            </div>
        </div>
    </div>


  </body>
</html>