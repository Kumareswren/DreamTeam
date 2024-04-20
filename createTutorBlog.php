<?php
require_once 'tokenVerify.php';
include "db.php";
use \Firebase\JWT\JWT;

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Function to create a new blog post
function createTutorBlog() {
  global $conn; // Declare $conn as global
    // Retrieve the token from the cookie
    if (isset($_COOKIE['token'])) {
        $token = $_COOKIE['token'];

        // Your secret key
        $secretKey = 'your_secret_key'; // Update with your secret key

        try {
            // Decode the token to get the email 
            $decoded = JWT::decode($token, $secretKey, array('HS256'));
            $email = $decoded->email;

            // Query to get the user ID based on the email
            $stmt = $conn->prepare("SELECT SID, NULL AS TID FROM Student WHERE Email = ? UNION SELECT NULL AS SID, TID FROM Tutor WHERE Email = ?");
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
                $userID = ($row['SID'] !== null) ? $row['SID'] : $row['TID'];
                $userRole = ($row['SID'] !== null) ? 'student' : 'tutor';

                // Check if form is submitted
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    // Retrieve data from the form
                    $title = $_POST['blog-title'];
                    $content = $_POST['blog-description'];
                    $imagePath = "media/" . $_FILES['upload-image']['name']; // Constructing image path

                    // Insert data into the database
                    if ($userRole === 'student') {
                        $stmt = $conn->prepare("INSERT INTO BlogPost (Title, Content, StudentID, ImagePath, UserRole) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("ssiss", $title, $content, $userID, $imagePath, $userRole);
                    } else {
                        $stmt = $conn->prepare("INSERT INTO BlogPost (Title, Content, TutorID, ImagePath, UserRole) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("ssiss", $title, $content, $userID, $imagePath, $userRole);
                    }

                    if (!$stmt->execute()) {
                        die("Error executing SQL query: " . $stmt->error);
                    }
                    $stmt->close();

                    // Move uploaded image to desired directory
                    move_uploaded_file($_FILES['upload-image']['tmp_name'], $imagePath);

                    // Redirect to some page after successful submission
                    header("Location: tutorDashboard.php");
                    exit();
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
}

// Call the function to create a new blog post
createTutorBlog();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
 


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

     
      .main-container {
  background: #fff;
  border: 1px solid rgba(0, 0, 0, .12);
  border-radius: 5px;
  padding: 25px 30px;
  margin: 0 auto; /* Center the container */
  max-width: 960px; /* Limit the width */
}


      .msh-logo {
        width: auto;
        max-width: 150px;
      }
      @media (max-width: 480px) {
        h1 {
          font-size: 2em;
        }
        h2 {
          font-size: 1.6em;
        }
        h3 {
          font-size: 1.3em;
        }
        button {
          font-size: 0.9em;
        }
        .body-root {
          min-height: 74.5vh;
        }
        .body-section {
          padding: 15px 0px;
        }
        .body-content {
          flex-direction: column;
        }
        .main-container {
          padding: 15px 20px;
          margin: 0px -16px 15px;
          border-radius: 0px;
        }
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
  </style>


</head>

<body>
    
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
});

    </script>

            <div class="col py-3 custom-div">
                
                <main class="mt-5 pt-3">
                    <div class="container-fluid">
                      <div class="row">
                
                      </div>
                      <br>
                      <div class="col py-3 custom-div">
    
            
                          
                        </div>
                      </div>
                          <div class="body-root">
      <div class="body-section">
        <div class="body-content">
          <div class="main"> 
            <div class="main-content">
              <div class="main-container new-blog">
              <h2>Create a new blog post</h2>
    <form class="new-blog-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
        <div>
            <label for="blog-title">Blog Title</label>
            <input id="blog-title" type="text" name="blog-title" required>
        </div>
        <div>
            <label for="blog-description">Blog Description</label>
            <textarea id="blog-description" type="text" rows="3" name="blog-description" required></textarea>
        </div>
        <div>
            <label for="upload-image">Upload Image</label>
            <input id="upload-image" type="file" name="upload-image" accept="image/*" required>
        </div>
        <!-- Hidden fields for user role and email -->
        <input type="hidden" name="user-role" value="<?php echo $userRole; ?>">
        <input type="hidden" name="user-email" value="<?php echo $userEmail; ?>">
        <button class="publish" type="submit">Publish</button>
    </form>
                      <br>
            </div>
     


  </body>
</html>