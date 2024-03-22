<?php
require_once 'tokenVerify.php';
require_once 'db.php'; // Include your database connection file

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

use \Firebase\JWT\JWT;

// Retrieve the JWT token from the cookie
$token = $_COOKIE['token'];

// Decode the JWT token to extract the email
$decoded = JWT::decode($token, 'your_secret_key', array('HS256'));
$user_email = $decoded->email;

// Check if the user exists in the database
$sql_check_user = "SELECT * FROM Student WHERE Email = '$user_email'";
$result_check_user = $conn->query($sql_check_user);

$welcome_message = "";
$last_login_time = ""; // Initialize the variable to avoid errors
$is_first_login = false; // Flag to indicate first login

if ($result_check_user && $result_check_user->num_rows > 0) {
    // User exists in the database
    $row = $result_check_user->fetch_assoc();
    
    // Check if it's the user's first login (last login time is NULL)
    if ($row['last_login'] === null || empty($row['last_login'])) {
        // Update last login time
        date_default_timezone_set('Asia/Kuala_Lumpur');
        $current_time = date('Y-m-d H:i:s');
        $update_query = "UPDATE Student SET last_login = '$current_time' WHERE Email = '$user_email'";
        if ($conn->query($update_query) !== TRUE) {
            // Handle error if update query fails
            echo "Error updating last login time: " . $conn->error;
        } else {
            // Set flag for first login
            $is_first_login = true;
            // Set the welcome message for the first login
            $welcome_message = "Welcome to your Dashboard, " . $row['FName'] . "! This is your first login.";
        }
    } else {
        // User has logged in before, fetch the last login time
        $last_login_time = $row['last_login'];
        $welcome_message = "Welcome back to your Dashboard, " . $row['FName'] . "!";
    }
} else {
    // User doesn't exist in the database
    $welcome_message = "Unknown User";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
   
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
                                <a href="#" class="nav-link align-middle px-10 courses-link">
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
                      <a href="#" class="nav-link align-middle px-10 blogs-link">
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
                    <p>Last Login: <?php echo $last_login_time; ?></p> <!-- Display the last login time here -->
                </div>
            </div>
            <div class="col py-3 custom-div">
                
                <main class="mt-5 pt-3">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-md-12" id="componentContainer">
                          <h4>Welcome to your Dashboard, </h4>
                          
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
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script> 
    $(document).ready(function() {
    
    $('.courses-link').click(function(event) {
        //event.preventDefault();
        $.ajax({
            url: 'studentCourses.php',
            success: function(data) {
                $('#componentContainer').html(data);
            },
            error: function(xhr, status, error) {
                console.error('An error occurred:', error);
            }
                });
                
            });
        });
    </script>
     <script>
    // Function to fetch student blog posts via AJAX
    function getStudentBlog() {
        // document.querySelector('.createStudentBlog').style.display = 'none'; // No need to hide here
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'studentBlog.php', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    document.getElementById('componentContainer').innerHTML = xhr.responseText;
                    initializeSwiper();
                    addDeleteButtonListeners();
                    addEditButtonListeners();
                    // document.querySelector('.createStudentBlog').style.display = 'block'; // No need to show here
                    createCreateNewBlogButton(); // Create the button after fetching the blog posts
                } else {
                    console.error('Error fetching student blog posts:', xhr.status);
                }
            }
        };
        xhr.send();
    }

    // Function to create the "Create New Blog" button
    function createCreateNewBlogButton() {
        var buttonContainer = document.createElement('div');
        buttonContainer.className = 'new-post-actions';

        var buttonInnerContainer = document.createElement('div');
        buttonInnerContainer.className = 'button-container';

        var createNewBlogButton = document.createElement('a');
        createNewBlogButton.className = 'createStudentBlog';
        createNewBlogButton.style.cursor = 'pointer';
        createNewBlogButton.textContent = 'Click to Create New Blog';
        createNewBlogButton.onclick = createStudentBlog;

        buttonInnerContainer.appendChild(createNewBlogButton);
        buttonContainer.appendChild(buttonInnerContainer);

        // Append the button container to the componentContainer
        var componentContainer = document.getElementById('componentContainer');
        componentContainer.appendChild(buttonContainer);
    }

    // Function to fetch the createStudentBlog form via AJAX
    function createStudentBlog() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'createStudentBlog.php', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Replace the content of a specific element with the form HTML
                    document.getElementById('componentContainer').innerHTML = xhr.responseText; // Change to componentContainer
                } else {
                    console.error('Error fetching createStudentBlog form:', xhr.status);
                }
            }
        };
        xhr.send();
    }

    // Function to initialize Swiper
    function initializeSwiper() {
        var totalBlogs = $('#componentContainer .swiper-slide').length;
        var slidesPerView = totalBlogs > 1 ? 3 : 1; // Set slidesPerView to 3 if there are more than 1 blog, otherwise set to 1
        var swiper = new Swiper("#componentContainer .swiper-container", {
            slidesPerView: slidesPerView,
            spaceBetween: 2,
            loop: true
        });
    }

    // Function to add event listeners to delete buttons
    function addDeleteButtonListeners() {
        var deleteForms = document.querySelectorAll('.delete-blog-form');
        console.log('Found', deleteForms.length, 'delete forms');
        deleteForms.forEach(form => {
            console.log('Adding listener to form', form);
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                let postID = this.querySelector('input[name="postID"]').value;
                sendDeleteRequest(postID);
            });
        });
    }

    // Function to send delete blog post request via AJAX
    function sendDeleteRequest(postID) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'studentBlog.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                console.log(xhr.responseText);
                getStudentBlog(); // Reload the blog posts after deletion
            } else {
                console.error('Request failed. Status:', xhr.status);
            }
        };
        xhr.send('action=deleteBlogPost&postID=' + encodeURIComponent(postID));
    }

    // Function to add event listeners to edit buttons
    function addEditButtonListeners() {
        document.querySelectorAll('.edit-blog-btn').forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                let postID = this.dataset.postid;
                editBlog(postID);
            });
        });
    }

    // Function to call editBlog function via AJAX
    function editBlog(postID) {
        document.querySelector('.createStudentBlog').style.display = 'none'; // Hide the button
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'editBlog.php?id=' + encodeURIComponent(postID), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById('componentContainer').innerHTML = xhr.responseText; // Change to componentContainer
                addEditFormListener(postID); // Add listener for edit form submission
            } else {
                console.error('Request failed. Status:', xhr.status);
            }
        };
        xhr.send();
    }

    // Function to add event listener for edit form submission
    function addEditFormListener(postID) {
        document.getElementById('editForm').addEventListener('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            formData.append('PostID', postID);
            sendEditRequest(formData);
        });
    }

    // Function to send edit blog post request via AJAX
    function sendEditRequest(formData) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'editBlog.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    window.location.href = response.redirect_url; // Redirect to viewBlog page after successful edit
                } else {
                    console.error('Edit failed:', response.message);
                }
            } else {
                console.error('Request failed. Status:', xhr.status);
            }
        };
        xhr.send(formData);
    }

    // Call the getStudentBlog function when the page loads
    window.onload = function() {
        $('.blogs-link').click(function(event) {
            //event.preventDefault();
            getStudentBlog(); // Call the function to fetch student blog posts via AJAX
        });
    };
</script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var swiper = new Swiper("#componentContainer .swiper-container", { // Adjusted to target swiper-container within componentContainer
                slidesPerView: 2,
                spaceBetween: 5,
                loop: true,
            });
        });
    </script>
  </body>
</html>