<?php
require_once 'tokenVerify.php';
include "db.php";
use \Firebase\JWT\JWT;

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

function insertIntoTrail($userID, $userRole, $actionPerformed) {
    global $conn; // Declare $conn as global

    // Get the user's IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Prepare the INSERT statement
    $stmt = $conn->prepare("INSERT INTO Trail (userID, userRole, ip_address, actionPerformed) VALUES (?, ?, ?, ?)");

    // Bind parameters and execute the statement
    $stmt->bind_param("isss", $userID, $userRole, $ip_address, $actionPerformed);
    if (!$stmt->execute()) {
        die("Error executing SQL query: " . $stmt->error);
    }
    $stmt->close();
}

// Function to create a new blog post
function createStudentBlog() {
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

                    // Insert record into the Trail table
                    $actionPerformed = $title . " blog has been created";
                    insertIntoTrail($userID, $userRole, $actionPerformed);

                    // Redirect to some page after successful submission
                    header("Location: studentDashboard.php");
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
createStudentBlog();
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
    padding: 5%; /* Adjusted padding */
    margin: 2% auto; /* Adjusted margin */
    max-width: 960px;
}

form {
    width: 100%; /* Add this line */
    padding: 5%;
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
        padding: 5%; /* Adjusted padding */
        margin: 2%; /* Adjusted margin */
        border-radius: 0;
    }

}
  </style>
</head>

<body>
  

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