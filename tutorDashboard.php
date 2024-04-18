<?php
session_start();
require_once 'tokenVerify.php';
require_once 'db.php'; // Include your database connection file
/* $_SESSION['TID'] = $row['TID']; */ // Assuming $row['TID'] contains the TID value

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

use \Firebase\JWT\JWT;

// Retrieve the JWT token from the cookie
$token = $_COOKIE['token'];

// Decode the JWT token to extract the email
$decoded = JWT::decode($token, 'your_secret_key', array('HS256'));
$user_email = $decoded->email;

$stmt = $conn->prepare("SELECT TID FROM Tutor WHERE Email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $_SESSION['TID'] = $row['TID'];
}

// Check if the user exists in the database
$sql_check_user = "SELECT * FROM Tutor WHERE Email = '$user_email'";
$result_check_user = $conn->query($sql_check_user);

$welcome_message = "";
$last_login_time = ""; // Initialize the variable to avoid errors

if ($result_check_user && $result_check_user->num_rows > 0) {
    // User exists in the database
    $row = $result_check_user->fetch_assoc();
    
    // Check if it's the user's first login (last login time is NULL)
    if ($row['last_login'] === null || empty($row['last_login'])) {
        // Update last login time
        date_default_timezone_set('Asia/Kuala_Lumpur');
        $current_time = date('Y-m-d H:i:s');
        $update_query = "UPDATE Tutor SET last_login = '$current_time' WHERE Email = '$user_email'";
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
        // User has logged in before, fetch the last login time from SystemActivity table
        $sql_last_login = "SELECT Timestamp FROM SystemActivity WHERE UserID = '{$row['TID']}' AND PageName = 'tutorDashboard.php' ORDER BY Timestamp DESC LIMIT 1";
        $result_last_login = $conn->query($sql_last_login);
        if ($result_last_login && $result_last_login->num_rows > 0) {
            $row_last_login = $result_last_login->fetch_assoc();
            $last_login_time = $row_last_login['Timestamp'];
        }
        
        $welcome_message = "Welcome back to your Dashboard, " . $row['FName'] . "!";
    }

     // Assuming $row['TID'] contains the TID value
     $_SESSION['TID'] = $row['TID']; //this line was added 2,4 to make the message stuff work, so ref for student?
    /* echo $_SESSION['TID']; */
    // Insert record into SystemActivity table
 $activity_type = "Access Dashboard";
 $page_name = "tutorDashboard.php";
 $browser_name = $_SERVER['HTTP_USER_AGENT'];
 $user_id = $row['TID'];
 $user_type = "tutor";

 $insert_query = "INSERT INTO SystemActivity (UserID, UserType, ActivityType, PageName, BrowserName) 
                 VALUES ('$user_id', '$user_type', '$activity_type', '$page_name', '$browser_name')";
 if ($conn->query($insert_query) !== TRUE) {
     // Handle error if insert query fails
     echo "Error inserting system activity: " . $conn->error;
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

    <title>Tutor Dashboard</title>

    <style>

  /* for e-Tutor */ .min-vh-100 .fs-5 {
    color: rgb(255, 255, 255); 
    font-family: "garamond"; 
    
}

/* for Sidebar items */  #menu .nav-link .d-none.d-sm-inline {
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
                        <a href="#" class="nav-link align-middle px-10 dashboard-link" data-tid="<?php echo $_SESSION['TID']; ?>">
                            <i class="fs-4 bi-house-fill"></i> <span class="ms-1 d-none d-sm-inline">Home</span>
                        </a>
                    </li>

                    
                    <li class="nav-item">
                                <a href="#" class="nav-link align-middle px-10 courses-link">
                            <i class="fs-4 bi-journal-text"></i> <span class="ms-1 d-none d-sm-inline">Courses</span>
                        </a>
                    </li>

                        <li class="nav-item">
                          <a href="#" class="nav-link align-middle px-10 chat-link">
                              <i class="fs-4 bi-house-fill"></i> <span class="ms-1 d-none d-sm-inline">Chat</span>
                          </a>
                      </li>
                      
                      <li>
                          <a href="#" class="nav-link px-10 align-middle meeting-link">
                              <i class="fs-4 bi-table"></i> <span class="ms-1 d-none d-sm-inline">Meetings</span></a>
                      </li>
                      
                      <li>
                          <a href="#" class="nav-link px-10 align-middle meetings-list-link">
                              <i class="fs-4 bi-table"></i> <span class="ms-1 d-none d-sm-inline">View my meeting</span></a>
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


                    <li class="nav-item">
                                <a href="#" class="nav-link align-middle px-10 student-link">
                            <i class="fs-4 bi bi-people"></i> <span class="ms-1 d-none d-sm-inline">Students</span>
                        </a>
                    </li>

                    

                      <li>
                          <a href="logout.php" class="nav-link px-10 align-middle">
                              <i class="fs-4 bi-box-arrow-left"></i> <span class="ms-1 d-none d-sm-inline">Logout</span> </a>
                      </li>

                    </ul>
                    <p><?php
                if (!empty($last_login_time)) {
                    echo "Last Login: " . $last_login_time;
                } else {
                    echo "Welcome new user!";
                }
                ?></p>
                </div>
            </div>
            <script>
        $(document).ready(function() {
    
    $('.student-link').click(function(event) {
        //event.preventDefault();
        $.ajax({
            url: 'ViewStudentList.php',
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
        $(document).ready(function() {
            $('.dashboard-link').click(function(event) {
                var userRole = "tutor"; // Set the user role here, replace with actual user role
                var tid = $(this).data('tid'); // Assuming $tid contains the TID value

                $.ajax({
                    url: 'dashboard.php',
                    type: 'POST',
                    data: { user_role: userRole, userID: tid }, // Include tid in the data object
                    success: function(response) {
                        $('#componentContainer').html(response);
                    },
                    error: function(xhr, status, error) {
                        console.error('An error occurred:', error);
                    }
                });
            });
        });
    </script>

    <script>
        $(document).ready(function() {
    
    $('.courses-link').click(function(event) {
        //event.preventDefault();
        $.ajax({
            url: 'tutorCourses.php',
            success: function(data) {
                $('#componentContainer').html(data);
            },
            error: function(xhr, status, error) {
                console.error('An error occurred:', error);
            }
        });
        
    });

    //tutor meeting

    $('.meeting-link').click(function(event) {
    // Prevent default link behavior
    event.preventDefault();
    
    // Load the tutorMeeting.php page using AJAX
    $.ajax({
        url: 'tutorMeeting.php',
        success: function(data) {
            $('#componentContainer').html(data); // Load the page content into the specified container
        },
        error: function(xhr, status, error) {
            console.error('An error occurred:', error);
        }
    });

    // Remove any previous form submission event handler
    $('#componentContainer').off('submit', 'form');
    
    // Attach the form submission event handler to the loaded form
    handleMeetingFormSubmission();
});

function handleMeetingFormSubmission() {
    // Hide any previous alert messages
    $('#alertMessageMeeting').hide();

    // Attach event listener to form submission
    $('#componentContainer').on('submit', 'form', function(event) {
        // Prevent default form submission
        event.preventDefault();

        // Collect form data
        var formData = $(this).serialize();

        // Send form data to backend using AJAX
        $.ajax({
            type: 'POST',
            url: 'tutorMeetingBackend.php',
            data: formData,
            success: function(response) {
                // Handle the response
                $('.alert').remove();
                $('#alertMessage').hide();
                if (response === 'success') {
                    alert("Accept successful.");
                    // Reload the component after successful acceptance
                    $.get('tutorMeeting.php', function(data) {
                        $('.container').replaceWith(data); // Replace the container content with the updated one
                    });
                } else {
                    $('<div id="alertMessageMeeting" class="alert alert-danger" role="alert">' + response + '</div>').insertBefore('form').show();
                }
            },
            error: function(xhr, status, error) {
                // Handle errors
                alert("An error occurred: " + error);
            }
        });
    });

    
}






});

    </script>
<script>
                //MESSAGES STUFF!!
$(document).ready(function() {
    $('.chat-link').click(function(event) {
        event.preventDefault();

         // Retrieve TID
         var tid = <?php echo $_SESSION['TID']; ?>;

        $.ajax({
            type: 'POST',
            url: 'chatTutorOverview.php',
            data: {tid: tid}, 
            success: function(data) {
                $('#componentContainer').html(data);

                // Add auto-resizing functionality after loading the chat
                var textarea = $('#chatInput');
                
                textarea.on('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });

                // Adjust initial height to fit one line
                textarea.css('height', 'auto');
                textarea.css('height', textarea[0].scrollHeight + 'px');
            },
            error: function(xhr, status, error) {
                console.error('An error occurred:', error);
            }
        });
    });
});


</script>


            <div class="col py-3 custom-div">
                
                <main class="mt-5 pt-3">
                    <div class="container-fluid">
                      <div class="row">
                      <div class="col-md-12" id="componentContainer">
                        </div>
                      </div>
                      <br>

            </div>
        </div>
    </div>


    <script>
$(document).ready(function() {
    // Click event for the "View my meeting" link
    $('.meetings-list-link').click(function(event) {
        event.preventDefault(); // Prevent default link behavior

        // Load the tutorMeetingList.php page using AJAX
        $.ajax({
            url: 'tutorMeetingList.php',
            success: function(data) {
                $('#componentContainer').html(data); // Load the page content into the specified container

                // After loading the meeting list, fetch the meeting data
                $.ajax({
                    url: 'tutorMeetingListBackend.php', // Update with the correct backend URL
                    method: 'GET', // Assuming you're using GET method to fetch meeting data
                    success: function(response) {
                        // Process the meeting data here (e.g., display in a table)
                        console.log(response); // For testing, you can log the response to the console
                    },
                    error: function(xhr, status, error) {
                        console.error('An error occurred while fetching meeting data:', error);
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error('An error occurred:', error);
            }
        });
    });
});
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <!-- for charts? --> <script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.2/dist/chart.min.js"></script> 
        <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
        <script>
    // Function to fetch tutor blog posts via AJAX
    function getTutorBlog() {
        var createButton = document.querySelector('.createTutorBlog');
        if (createButton) {
            createButton.style.display = 'none'; // Hide the button if it exists
        }
        
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'tutorBlog.php', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    document.getElementById('componentContainer').innerHTML = xhr.responseText;
                    initializeSwiper(); // Initialize Swiper after loading content
                    addDeleteButtonListeners();
                    addEditButtonListeners();
                    createCreateNewBlogButton(); // Create the "Create New Blog" button
                    if (createButton) {
                        createButton.style.display = 'block'; // Show the button if it exists
                    }
                } else {
                    console.error('Error fetching tutor blog posts:', xhr.status);
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
        createNewBlogButton.className = 'createTutorBlog';
        createNewBlogButton.style.cursor = 'pointer';
        createNewBlogButton.textContent = 'Click to Create New Blog';
        createNewBlogButton.onclick = createTutorBlog;

        buttonInnerContainer.appendChild(createNewBlogButton);
        buttonContainer.appendChild(buttonInnerContainer);

        // Append the button container to the componentContainer
        var componentContainer = document.getElementById('componentContainer');
        componentContainer.appendChild(buttonContainer);
    }

    // Function to fetch the createTutorBlog form via AJAX
    function createTutorBlog() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'createTutorBlog.php', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Replace the content of a specific element with the form HTML
                    document.getElementById('componentContainer').innerHTML = xhr.responseText;
                } else {
                    console.error('Error fetching createTutorBlog form:', xhr.status);
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
        document.querySelectorAll('.delete-blog-form').forEach(form => {
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
        xhr.open('POST', 'tutorBlog.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                console.log(xhr.responseText);
                getTutorBlog(); // Reload the blog posts after deletion
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
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'editBlog.php?id=' + encodeURIComponent(postID), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById('componentContainer').innerHTML = xhr.responseText;
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

    // Call the getTutorBlog function when the page loads
    window.onload = function() {
        $('.blogs-link').click(function(event) {
            event.preventDefault();
            getTutorBlog(); // Call the function to fetch tutor blog posts via AJAX
        });
    };

 
</script>


    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var swiper = new Swiper(".swiper-container", {
                slidesPerView: 3,
                spaceBetween: 10,
                loop: true,
            });
        });
    </script>
  </body>
</html>