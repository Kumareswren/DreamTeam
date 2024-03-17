<?php
require_once 'tokenVerify.php';
include "db.php";
use \Firebase\JWT\JWT;

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Retrieve the token from the cookie
if (isset($_COOKIE['token'])) {
    $token = $_COOKIE['token'];

    // Your secret key
    $secretKey = 'rNjde95IzZ9CEU1k94aRjHbOX1LvKgM+RX6iv8NfMm8='; // Update with your secret key

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
}
.button-container {
  text-align: left;
}

a.createStudentBlog {
  display: inline-block;
  font-size: 0.9em;
  text-transform: uppercase;
  padding: 10px 20px;
  margin: 0px;
  background: #333;
  border: 1px solid #333;
  border-radius: 5px;
  color: #fff;
  text-decoration: none;
  transition: background-color 0.3s ease;
}

a.createStudentBlog:hover {
  background-color: #fac821;
  border-color: #fac821;
}

.component-card {
  position: relative;
  margin-bottom: 10px;
  max-width: 90%; /* Adjust the max-width to a percentage to make it responsive */
  margin: 10px auto;
  box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
  transition: 0.3s;
}

.component-card:hover {
  box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
}

.component-card:hover img {
  transform: scale(1.1);
}

.component-card .component-card_image {
  background: #fff;
  height: 0;
  overflow: hidden;
  padding-bottom: 56.2%;
  position: relative;
}

.component-card .component-card_image .component-card_image-inside {
  height: 100%;
  left: 0;
  position: absolute;
  top: 0;
  width: 100%;
}

.component-card .component-card_image .component-card_image-inside img {
  background-size: cover;
  height: auto !important;
  transform: scale(1);
  transition: all .25s ease-in-out;
  width: 100%;
}

.component-card .blog-detail {
  background: #fff;
  margin: 0 20px; /* Adjust the horizontal margin if necessary */
  padding: 20px;
  position: relative;
  top: -20px; /* Adjust this value to move the white box up or down */
  max-height: 200px; /* Increase the max-height if necessary */
  overflow: hidden;
  box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
}

.component-card .blog-detail h3 {
  font-size: 18px;
  margin: 0;
  text-transform: uppercase;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.component-card .blog-detail label {
  color: #737373;
  font-size: 14px;
}

.component-card .blog-detail p {
  margin-bottom: 1rem;
  margin-top: 0;
  font-size: 14px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.component-card .blog-detail .btn {
  background-color: transparent;
  border: 1px solid transparent;
  border-radius: .25rem;
  color: #212529;
  display: inline-block;
  font-size: 1rem;
  font-weight: 400;
  line-height: 1.5;
  padding: .375rem .75rem;
  text-align: center;
  transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
  user-select: none;
  vertical-align: middle;
}

.component-card .blog-detail .btn:hover {
  background-color: #fac821;
  color: #212529;
  text-decoration: none;
}

.component-card .blog-detail .btn-read-more {
  background: transparent;
  border-radius: 0;
  border: 2px solid #fac821;
  outline: none;
  text-transform: uppercase;
  transition: background-color 0.3s ease, border-color 0.3s ease;
}

.component-card .blog-detail .btn-read-more:hover {
  background-color: #fac821;
  border-color: #333;
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
                      <a href="studentBlog" class="nav-link px-10 align-middle">
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
                          <h4>Welcome to your blog </h4>
                          
                        </div>
                      </div>
                      <div class="container my-5">
    <div class="row">
    <?php foreach ($blogPosts as $post): ?>
    <div class="col-6">
        <div class="component-card">
            <div class="component-card_image">
                <div class="component-card_image-inside">
                    <img src="<?php echo $post['ImagePath']; ?>" alt="<?php echo $post['Title']; ?>" title="<?php echo $post['Title']; ?>" />
                </div>
            </div>
            <div class="blog-detail">
                <h3><?php echo $post['Title']; ?></h3>
                <label><?php echo $post['CreatedAt']; ?></label>
                <p><?php echo $post['Content']; ?></p>
                <a class="btn btn-read-more" href="viewBlog.php?id=<?php echo $post['PostID']; ?>">Read More</a>
            </div>
        </div>
    </div>
<?php endforeach; ?>
    </div>
</div>
                      <div class="new-post-actions">
  <div class="button-container">
    <a class="createStudentBlog" href="createStudentBlog.php">Click to Create New Blog</a>
  </div>
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