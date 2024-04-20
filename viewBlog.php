<?php
require_once 'tokenVerify.php';
include "db.php";
use \Firebase\JWT\JWT;

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check if PostID is passed in the URL
if (isset($_GET['id'])) {
    $postID = $_GET['id'];

    // Prepare SQL query to retrieve the blog post with the given PostID
    $sql = "SELECT PostID, Title, Content, ImagePath, CreatedAt FROM BlogPost WHERE PostID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $postID);
    
    // Execute the SQL query
    if (!$stmt->execute()) {
        die("Error executing SQL query: " . $stmt->error);
    }

    // Get the result
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $post = $result->fetch_assoc();
  
      $token = $_COOKIE['token'];
      $secretKey = 'your_secret_key'; // Change to your actual secret key
      $decoded = JWT::decode($token, $secretKey, array('HS256'));
  
      $userId = $decoded->userId;
      $userRole = $decoded->role;
      $ipAddress = $_SERVER['REMOTE_ADDR'];
      $actionPerformed = 'Viewed blog ' . $post['Title']; // Concatenation corrected
  
      // Insert into trail table
      $trailQuery = "INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?,?,?,?)";
      $trailStmt = $conn->prepare($trailQuery);
      $trailStmt->bind_param("ssss", $userId, $userRole, $ipAddress, $actionPerformed); // Binding all parameters
      if (!$trailStmt->execute()) {
          die("Error inserting into trail table: " . $trailStmt->error);
      }
  } else {
      // Post not found
      echo "Post not found.";
      exit; // Stop further execution
  }

} else {
    // PostID not passed in the URL
    echo "PostID not passed in the URL.";
    exit; // Stop further execution
}

// Delete button
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "DELETE FROM BlogPost WHERE PostID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $postID);
    
    // Execute the SQL query
    if (!$stmt->execute()) {
        die("Error executing SQL query: " . $stmt->error);
    } else {
        // Decode JWT token
        $secretKey = 'your_secret_key';
        $token = $_COOKIE['token'];
        $decoded = JWT::decode($token, $secretKey, array('HS256'));

        // Redirect based on user role
        switch ($decoded->role) {
            case 'student':
                header('Location: studentBlog.php');
                break;
            case 'tutor':
                header('Location: tutorBlog.php');
                break;
            default:
                header('Location: index.php');
                break;
        }
        exit;
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
        ;(function ($, window, document, undefined) {

// ########################################
// # Define Functions
// ########################################

function func() {
  
};

// ########################################
// # Initialize all necessary event handlers
// ########################################

var init = function () {
  func();
};

// ########################################
// # Call init on Document Ready
// ########################################

$(init);

})(window.jQuery, this, this.document);
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
    
}.viewBlogContainer {
  margin: 0 auto;
  padding: 20px;
}.viewBlogDetailed {
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  padding: 30px;
  margin-top: 30px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  background-color: #ffffff;
  box-sizing: border-box;
  transition: box-shadow .3s ease-in-out;
}

.viewBlogDetailed:hover {
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.viewBlogDetailed h2 {
  font-size: 1.8em;
  color: #333333;
  margin-bottom: 20px;
}

.viewBlogTitleDetailed {
  font-size: 1.5em;
  color: #007bff;
  margin-bottom: 15px;
}

.viewBlogDescriptionDetailed {
  font-size: 1.2em;
  color: #555555;
  margin-bottom: 15px;
  line-height: 1.6;
  white-space: pre-wrap;
}

.viewBlogImageDetailed {
  width: 50%;
  height: 50%;
  object-fit: contain;
  margin-bottom: 15px;
  display: flex; /* Use flexbox */
  justify-content: center; /* Align horizontally to the center */
  align-items: center; /* Align vertically to the center */
}
.viewBlogImageDetailed img {
  width: 50%;  
  height: 50%;
  object-fit: contain;
}

@media (max-width: 768px) {
  .viewBlogDetailed {
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

                      <div class="viewBlogDetailed" style="display: block;">
    <div class="viewBlogTitleDetailed"><?php echo $post['Title']; ?></div>
    <label>Created at: <?php echo $post['CreatedAt']; ?></label>
    <br><br><br>
    <div class="viewBlogDescriptionDetailed"><?php echo $post['Content']; ?></div>
    <div class="viewBlogImageDetailed"><img src="<?php echo $post['ImagePath']; ?>" alt="<?php echo $post['Title']; ?>"></div>
</div>
<br>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <!-- for charts? --> <script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.2/dist/chart.min.js"></script> 
    <script src="script.js"></script>
  </body>
</html>