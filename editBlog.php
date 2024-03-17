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
   
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
 integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
 
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <title>Student Dashboard</title>

    <script>
        
        function verifyToken(token) {
        }

        var jwtToken = getCookie('token');

        if (jwtToken && verifyToken(jwtToken)) {

        } 
        else {
        
            window.location.href = 'index.php';
        }
    </script>

    <style>

  /* Styling for e-Tutor */ .min-vh-100 .fs-5 {
    color: rgb(255, 255, 255); 
    font-family: "garamond"; 
    
}

/* Styling for Sidebar items */  #menu .nav-link .d-none.d-sm-inline {
    color: #ffffff;
}

.custom-div {
    background-color: #FFF6D9;
    padding: 20px;
}

.nav-link {
    font-family: Arial, sans-serif; 
    font-size: 11px; 
    font-weight: 350; 
    padding: 13px 38px;
}

.nav-item:hover{
    color:floralwhite;
    
}

.nav-link:hover{
    background-color: #00425A;
    color: #ffffff;
}

.nav-item:hover .nav-link {
    background-color: #00425A;
    color: #ffffff;
}

.bi-house-fill {
    color: #8fc8bd;
}

.bi-journal-text{
    color: #8fc8bd;
}

.bi-table{
    color: #8fc8bd;
}

.bi-book{
    color: #8fc8bd;
}

.bi-newspaper{
    color: #8fc8bd;
}

.bi-envelope{
    color: #8fc8bd;
}

.bi-box-arrow-left{
    color: #8fc8bd;
}

.bg-secondary{
    background-color: #1F8A70!important;
}

.login-logo{
    display: block;
    width: 40px;
    height: 40px;
    transform: translateY(32px);
}
      /*START GENERAL CSS FOR BACKEND*/
      @import "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/3.1.0/css/font-awesome.min.css";
      body {
  font-size: 16px;
  background: #f3f3f3;
  margin: 0;
}

h1, h2, h3, h4, h5, h6 {
  letter-spacing: -0.035em;
  margin: 0 0 10px;
}

h1 { font-size: 2.5em; }
h2 { font-size: 1.8em; }
h3 { font-size: 1.5em; }

img, p, a { margin: 0; }

a {
  color: #f44236;
  text-decoration: none;
}

a:hover, a:focus, a:active { color: #f44236; }

button {
  font-size: 0.8em;
  text-transform: uppercase;
  padding: 5px 10px;
  margin: 0;
  background: #f3f3f3;
  border: 1px solid rgba(0, 0, 0, .12);
  border-radius: 5px;
}

button:hover { cursor: pointer; }

.body-root { min-height: 85.3vh; }
.body-section { padding: 25px 0; }
.body-content { display: flex; }

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

.msh-logo { width: auto; max-width: 150px; }

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

.form-field.short input, .form-field.short select, .form-field.short textarea {
  max-width: 250px;
}
.form-field.medium input, .form-field.medium select, .form-field.medium textarea {
  max-width: 450px;
}

input[type="checkbox"] {
  height: 20px;
  width: 20px;
}

.form-field.checkbox {
  display: flex;
  align-items: center;
}
.form-field.checkbox label {
  padding: 0 0 0 10px;
}

input[readonly] {
  background: #e9ecef;
}

button.publish, button.update {
  background: #007bff;
  border-color: #007bff;
  color: white;
  transition: background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
}

button.publish:hover, button.update:hover {
  background: #0056b3;
  border-color: #0056b3;
}

.new-post-actions, .edit-post-actions {
  display: flex;
  justify-content: space-between;
  margin-top: 30px;
}

.new-post-actions button, .edit-post-actions button {
  font-size: 1em;
  height: 50px;
  padding: 5px 15px;
}

.field-7 {
  padding-top: 10px;
}
.col-md-6 {
  margin: 0 auto;
  padding: 20px;
}
#showPreviewBtn {
  background-color: #007bff;
  color: white;
  border: none;
  padding: 15px 30px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 1em;
  margin: 30px 0;
  cursor: pointer;
  border-radius: 5px;
  transition: background-color .15s ease-in-out;
}

#showPreviewBtn:hover {
  background-color: #0056b3;
}

.preview-blog {
  border: 1px solid #ddd;
  border-radius: 10px;
  padding: 30px;
  margin-top: 30px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
  background-color: #f8f9fa;
  box-sizing: border-box;
}

.preview-blog h2 {
  font-size: 1.5em;
  color: #495057;
  margin-bottom: 20px;
}

.blog-title-preview {
  font-size: 1.2em;
  color: #007bff;
  margin-bottom: 15px;
}

.blog-description-preview {
  font-size: 1em;
  color: #6c757d;
  margin-bottom: 15px;
  white-space: pre-wrap;
}

.blog-image-preview {
  width: 100%;
  height: auto;
  object-fit: contain;
}

@media (max-width: 768px) {
  .preview-blog {
    padding: 20px;
    margin-top: 20px;
  }
}

@media (max-width: 480px) {
  h1 { font-size: 2em; }
  h2 { font-size: 1.6em; }
  h3 { font-size: 1.3em; }
  button { font-size: 0.9em; }
  .body-root { min-height: 74.5vh; }
  .body-section { padding: 20px 0; }
  .body-content { flex-direction: column; }
  .main-container {
    padding: 20px 30px;
    margin: 0 -16px 20px;
    border-radius: 0;
  }
}
  </style>
</head>

<body>
    
    
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 bg-secondary">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                        <span class="fs-5 d-none d-sm-inline">
                        <img src="icons/online-learning.png" alt="Education Logo" class="login-logo">
                        <span class="fs-5 d-none d-sm-inline etutor-text" style="margin-left: 50px;">e-Tutor</span>
                    </span>
                    </a> 
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start" id="menu">
                        
                        <li class="nav-item">
                          <a href="#" class="nav-link align-middle px-10">
                              <i class="fs-4 bi-house-fill"></i> <span class="ms-1 d-none d-sm-inline">Home</span>
                          </a>
                      </li>

                      <li class="nav-item">
                        <a href="#" class="nav-link align-middle px-10">
                            <i class="fs-4 bi-journal-text"></i> <span class="ms-1 d-none d-sm-inline">Courses</span>
                        </a>
                    </li>

                        <li class="nav-item">
                          <a href="#" class="nav-link align-middle px-10">
                              <i class="fs-4 bi-house-fill"></i> <span class="ms-1 d-none d-sm-inline">Chat</span>
                          </a>
                      </li>
                      
                      <li>
                          <a href="#" class="nav-link px-10 align-middle">
                              <i class="fs-4 bi-table"></i> <span class="ms-1 d-none d-sm-inline">Meetings</span></a>
                      </li>
                      
                      <li>
                        <a href="#" class="nav-link px-10 align-middle">
                            <i class="fs-4 bi-book"></i> <span class="ms-1 d-none d-sm-inline">Tutorial</span> </a>
                    </li>

                    <li>
                      <a href="studentBlog.php" class="nav-link px-10 align-middle">
                          <i class="fs-4 bi-newspaper"></i> <span class="ms-1 d-none d-sm-inline">Blog</span> </a>
                    </li>

                    <li>
                      <a href="#" class="nav-link px-10 align-middle">
                          <i class="fs-4 bi-envelope"></i> <span class="ms-1 d-none d-sm-inline">Email</span> </a>
                    </li>

                      <li>
                          <a href="logout.php" class="nav-link px-10 align-middle">
                              <i class="fs-4 bi-box-arrow-left"></i> <span class="ms-1 d-none d-sm-inline">Logout</span> </a>
                      </li>

                    </ul>
                    
                </div>
            </div>
            <div class="col py-3 custom-div">
                
                <main class="mt-5 pt-3">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-md-12">
                          <h4>Edit Blog Post </h4>
                          
                        </div>
                      </div>
                          <div class="body-root">
      <div class="body-section">
        <div class="body-content">
          <div class="main"> 
            <div class="main-content">
              <div class="main-container new-blog">
              <h2>Create a new blog post</h2>
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



                      <br>
                      </div>
            <div class="col-md-6">
    <!-- Button to show preview -->
    <button id="showPreviewBtn" class="btn btn-primary mb-3">Show Preview</button>
<!-- Preview of the blog post -->
<div class="preview-blog" style="display: none;">
    <h2>This is Preview</h2>
    <div class="blog-title-preview"></div>
    <label class="blog-createdat-preview">Time and Data created: </label>
    <br>
    <div class="blog-description-preview"></div>
    <div class="blog-image-preview"></div>
</div>
</div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
  
    <script src="script.js"></script>
    <script>
    // Function to handle button click event
    document.getElementById("showPreviewBtn").addEventListener("click", function() {
        // Get form field values
        var blogTitle = document.getElementById("blog-title").value;
        var blogDescription = document.getElementById("blog-description").value;
        var blogImage = document.getElementById("upload-image").files[0];

        // Check if all required fields are filled
        if (!blogTitle || !blogDescription || !blogImage) {
            alert('Please fill up everything then show the preview.');
            return;
        }

        // Update preview elements
        document.querySelector(".blog-title-preview").textContent = blogTitle;
        document.querySelector(".blog-description-preview").textContent = blogDescription;
        if (blogImage) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = document.createElement("img");
                img.src = e.target.result;
                document.querySelector(".blog-image-preview").innerHTML = "";
                document.querySelector(".blog-image-preview").appendChild(img);
            };
            reader.readAsDataURL(blogImage);
        } else {
            document.querySelector(".blog-image-preview").innerHTML = "No image selected";
        }

        // Show the preview section
        document.querySelector(".preview-blog").style.display = "block";
    });
</script>
  </body>
</html>