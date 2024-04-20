<?php

if(isset($_POST['user_role'])) {
    $user_role = $_POST['user_role'];
    $userID = $_POST['userID'];
    require_once 'db.php'; // Include your database connection file

    function displayDashboard($user_role, $userID, $conn) {
        switch ($user_role) {
            case "student":
                displayStudentDashboard($userID, $conn);
                break;
            case "tutor":
                displayTutorDashboard($userID, $conn);
                break;
            case "admin":
                displayAdminDashboard($userID, $conn);
                break;
            default:
                echo "Invalid user role";
        }
    }

    // Function to display student dashboard content
    function displayStudentDashboard($userID, $conn) {
        // Prepare the SQL query
        $sql = "SELECT COUNT(*) AS num_messages
        FROM Messages
        WHERE SID = ? 
        AND receiver_type = 'Student'
        AND sent_at >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 7 DAY)";

        // Prepare and bind the parameter
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userID);

        // Execute the query
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        // Fetch the number of messages
        $row = $result->fetch_assoc();
        $num_messages = $row['num_messages'];

        echo "<h2>Dashboard</h2><br/>";
        echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/css/all.min.css" integrity="sha256-mmgLkCYLUQbXn0B1SRqzHar6dCnv9oZFPEC1g1cwlkk=" crossorigin="anonymous" />';
        echo '
                <style>
                    .card {
                        background-color: #fff;
                        border-radius: 10px;
                        border: none;
                        position: relative;
                        margin-bottom: 30px;
                        box-shadow: 0 0.46875rem 2.1875rem rgba(90,97,105,0.1), 0 0.9375rem 1.40625rem rgba(90,97,105,0.1), 0 0.25rem 0.53125rem rgba(90,97,105,0.12), 0 0.125rem 0.1875rem rgba(90,97,105,0.1);
                    }
                    .l-bg-cherry {
                        background: linear-gradient(to right, #493240, #f09) !important;
                        color: #fff;
                    }
                    .l-bg-blue-dark {
                        background: linear-gradient(to right, #373b44, #4286f4) !important;
                        color: #fff;
                    }
                    .l-bg-green-dark {
                        background: linear-gradient(to right, #0a504a, #38ef7d) !important;
                        color: #fff;
                    }
                    .l-bg-orange-dark {
                        background: linear-gradient(to right, #a86008, #ffba56) !important;
                        color: #fff;
                    }
                    .card .card-statistic-3 .card-icon-large .bi, .card .card-statistic-3 .card-icon-large .far, .card .card-statistic-3 .card-icon-large .fab, .card .card-statistic-3 .card-icon-large .fal {
                        font-size: 90px;
                    }
                    .card .card-statistic-3 .card-icon {
                        text-align: center;
                        line-height: 50px;
                        margin-left: 15px;
                        color: #000;
                        position: absolute;
                        right: 25px;
                        top: 20px;
                        opacity: 0.1;
                    }
                    .custom-card {
                        max-width: 400px; /* Adjust the max-width value as per your requirement */
                    }

                    .l-bg-cyan {
                        background: linear-gradient(135deg, #289cf5, #84c0ec) !important;
                        color: #fff;
                    }
                    .l-bg-green {
                        background: linear-gradient(135deg, #23bdb8 0%, #43e794 100%) !important;
                        color: #fff;
                    }
                    .l-bg-orange {
                        background: linear-gradient(to right, #f9900e, #ffba56) !important;
                        color: #fff;
                    }
                    .l-bg-cyan {
                        background: linear-gradient(135deg, #289cf5, #84c0ec) !important;
                        color: #fff;
                    }
                </style>
            ';
        echo '
                <div class="col-xl-6 col-lg-6">
                    <div class="card l-bg-blue-dark custom-card">
                        <div class="card-statistic-3 p-4">
                            <div class="card-icon card-icon-large"><i class="bi bi-chat-left-dots"></i></div>
                            <div class="mb-4">
                                <h5 class="card-title mb-0">Number of messages last 7 days:</h5>
                            </div>
                            <div class="row align-items-center mb-2 d-flex">
                                <div class="col-8">
                                    <h2 class="d-flex align-items-center mb-0">' . $num_messages . '</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>';
    }


    function displayTutorDashboard($userID, $conn) {
        // Display content specific to tutors
        $sql = "SELECT COUNT(*) AS num_messages
        FROM Messages
        WHERE TID = ? 
        AND receiver_type = 'Tutor'
        AND sent_at >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 7 DAY)";

        // Prepare and bind the parameter
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userID);

        // Execute the query
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        // Fetch the number of messages
        $row = $result->fetch_assoc();
        $num_messages = $row['num_messages'];

        echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/css/all.min.css" integrity="sha256-mmgLkCYLUQbXn0B1SRqzHar6dCnv9oZFPEC1g1cwlkk=" crossorigin="anonymous" />';
        echo "<h2>Dashboard</h2><br/>";
        echo '
                <style>
                    .card {
                        background-color: #fff;
                        border-radius: 10px;
                        border: none;
                        position: relative;
                        margin-bottom: 30px;
                        box-shadow: 0 0.46875rem 2.1875rem rgba(90,97,105,0.1), 0 0.9375rem 1.40625rem rgba(90,97,105,0.1), 0 0.25rem 0.53125rem rgba(90,97,105,0.12), 0 0.125rem 0.1875rem rgba(90,97,105,0.1);
                    }
                    .l-bg-cherry {
                        background: linear-gradient(to right, #493240, #f09) !important;
                        color: #fff;
                    }
                    .l-bg-blue-dark {
                        background: linear-gradient(to right, #373b44, #4286f4) !important;
                        color: #fff;
                    }
                    .l-bg-green-dark {
                        background: linear-gradient(to right, #0a504a, #38ef7d) !important;
                        color: #fff;
                    }
                    .l-bg-orange-dark {
                        background: linear-gradient(to right, #a86008, #ffba56) !important;
                        color: #fff;
                    }
                    .card .card-statistic-3 .card-icon-large .bi, .card .card-statistic-3 .card-icon-large .far, .card .card-statistic-3 .card-icon-large .fab, .card .card-statistic-3 .card-icon-large .fal {
                        font-size: 90px;
                    }
                    .card .card-statistic-3 .card-icon {
                        text-align: center;
                        line-height: 50px;
                        margin-left: 15px;
                        color: #000;
                        position: absolute;
                        right: 25px;
                        top: 20px;
                        opacity: 0.1;
                    }
                    .custom-card {
                        max-width: 400px; /* Adjust the max-width value as per your requirement */
                    }

                    .l-bg-cyan {
                        background: linear-gradient(135deg, #289cf5, #84c0ec) !important;
                        color: #fff;
                    }
                    .l-bg-green {
                        background: linear-gradient(135deg, #23bdb8 0%, #43e794 100%) !important;
                        color: #fff;
                    }
                    .l-bg-orange {
                        background: linear-gradient(to right, #f9900e, #ffba56) !important;
                        color: #fff;
                    }
                    .l-bg-cyan {
                        background: linear-gradient(135deg, #289cf5, #84c0ec) !important;
                        color: #fff;
                    }
                </style>
            ';

        $sql_average_messages = "SELECT ROUND(AVG(num_messages), 1) AS average_messages_by_student
        FROM (
            SELECT COUNT(*) AS num_messages
            FROM Messages
            WHERE TID = ? 
            AND receiver_type = 'Tutor'
            AND sent_at >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 7 DAY)
            GROUP BY SID
        ) AS student_messages;";

        // Prepare and bind the parameter
        $stmt_average_messages = $conn->prepare($sql_average_messages);
        $stmt_average_messages->bind_param("i", $userID);

        // Execute the query
        $stmt_average_messages->execute();

        // Get the result
        $result_average_messages = $stmt_average_messages->get_result();

        // Fetch the average number of messages by student
        $row_average_messages = $result_average_messages->fetch_assoc();
        $average_messages = $row_average_messages['average_messages_by_student'];
    
    // Card for average number of messages by student
        echo '<div class="row">'; // Start row container

// Card for number of messages last 7 days
        echo '
            <div class="col-xl-6 col-lg-6">
                <div class="card l-bg-blue-dark custom-card">
                    <div class="card-statistic-3 p-4">
                        <div class="card-icon card-icon-large"><i class="bi bi-chat-left-dots"></i></div>
                        <div class="mb-4">
                            <h5 class="card-title mb-0">Number of messages last 7 days:</h5>
                        </div>
                        <div class="row align-items-center mb-2 d-flex">
                            <div class="col-8">
                                <h2 class="d-flex align-items-center mb-0">' . $num_messages . '</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';

        // Card for average number of messages by student
        echo '
            <div class="col-xl-6 col-lg-6">
                <div class="card l-bg-green-dark custom-card">
                    <div class="card-statistic-3 p-4">
                        <div class="card-icon card-icon-large"><i class="bi bi-person-lines-fill"></i></div>
                        <div class="mb-4">
                            <h5 class="card-title mb-0">Average messages by student:</h5>
                        </div>
                        <div class="row align-items-center mb-2 d-flex">
                            <div class="col-8">
                                <h2 class="d-flex align-items-center mb-0">' . $average_messages . '</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';

        echo '</div>';
    
        $currentDate = date("Y-m-d");

        // Calculate the date 7 days ago
        $sevenDaysAgo = date("Y-m-d", strtotime("-7 days", strtotime($currentDate)));

        // Calculate the date 28 days ago
        $twentyEightDaysAgo = date("Y-m-d", strtotime("-28 days", strtotime($currentDate)));

        // Query to get inactive students for 7 days
        $sqlInactiveSevenDays = "SELECT *
                                FROM Student
                                WHERE last_login < ?";

        // Prepare and bind the parameter for 7 days
        $stmtInactiveSevenDays = $conn->prepare($sqlInactiveSevenDays);
        $stmtInactiveSevenDays->bind_param("s", $sevenDaysAgo);

        // Execute the query for 7 days
        $stmtInactiveSevenDays->execute();

        // Get the result for 7 days
        $resultInactiveSevenDays = $stmtInactiveSevenDays->get_result();

        // Query to get inactive students for 28 days
        $sqlInactiveTwentyEightDays = "SELECT *
                                    FROM Student
                                    WHERE last_login < ?";

        // Prepare and bind the parameter for 28 days
        $stmtInactiveTwentyEightDays = $conn->prepare($sqlInactiveTwentyEightDays);
        $stmtInactiveTwentyEightDays->bind_param("s", $twentyEightDaysAgo);

        // Execute the query for 28 days
        $stmtInactiveTwentyEightDays->execute();

        // Get the result for 28 days
        $resultInactiveTwentyEightDays = $stmtInactiveTwentyEightDays->get_result();

        // Display tables for inactive students
        echo "<h3>Inactive students for 7 days and above</h3>";
        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered">';
        echo "<thead><tr><th>Name</th><th>Last Login Date</th></tr></thead><tbody>";
        while ($row = $resultInactiveSevenDays->fetch_assoc()) {
            echo "<tr><td>" . $row['FName'] . "</td><td>" . $row['last_login'] . "</td></tr>";
        }
        echo "</tbody></table></div>";

        echo "<h3>Inactive students for 28 days and above</h3>";
        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered">';
        echo "<thead><tr><th>Name</th><th>Last Login Date</th></tr></thead><tbody>";
        while ($row = $resultInactiveTwentyEightDays->fetch_assoc()) {
            echo "<tr><td>" . $row['FName'] . "</td><td>" . $row['last_login'] . "</td></tr>";
        }
        echo "</tbody></table></div>";
    }
    // Function to display admin dashboard content
    function displayAdminDashboard($userID, $conn) {
        // Display content specific to admins
        echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/css/all.min.css" integrity="sha256-mmgLkCYLUQbXn0B1SRqzHar6dCnv9oZFPEC1g1cwlkk=" crossorigin="anonymous" />';
        echo "<h2>Dashboard</h2><br/>";
    
        $currentDate = date("Y-m-d");
    
        // Calculate the date 7 days ago
        $sevenDaysAgo = date("Y-m-d", strtotime("-7 days", strtotime($currentDate)));
    
        // Calculate the date 28 days ago
        $twentyEightDaysAgo = date("Y-m-d", strtotime("-28 days", strtotime($currentDate)));
    
        // Query to get inactive students for 7 days
        $sqlInactiveSevenDays = "SELECT *
                                FROM Student
                                WHERE last_login < ?";
    
        // Prepare and bind the parameter for 7 days
        $stmtInactiveSevenDays = $conn->prepare($sqlInactiveSevenDays);
        $stmtInactiveSevenDays->bind_param("s", $sevenDaysAgo);
    
        // Execute the query for 7 days
        $stmtInactiveSevenDays->execute();
    
        // Get the result for 7 days
        $resultInactiveSevenDays = $stmtInactiveSevenDays->get_result();
    
        // Query to get inactive students for 28 days
        $sqlInactiveTwentyEightDays = "SELECT *
                                        FROM Student
                                        WHERE last_login < ?";
    
        // Prepare and bind the parameter for 28 days
        $stmtInactiveTwentyEightDays = $conn->prepare($sqlInactiveTwentyEightDays);
        $stmtInactiveTwentyEightDays->bind_param("s", $twentyEightDaysAgo);
    
        // Execute the query for 28 days
        $stmtInactiveTwentyEightDays->execute();
    
        // Get the result for 28 days
        $resultInactiveTwentyEightDays = $stmtInactiveTwentyEightDays->get_result();
    
        // Query to get students without tutor assignments
        $sqlStudentsWithoutTutor = "SELECT *
                                    FROM Student
                                    WHERE SID NOT IN (SELECT SID FROM StudentAssignment)";
    
        // Execute the query for students without tutor assignments
        $resultStudentsWithoutTutor = $conn->query($sqlStudentsWithoutTutor);
    
        // Display tables for inactive students and students without tutor assignments
        echo "<h4>Inactive students for 7 days and above</h4>";
        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered">';
        echo "<thead><tr><th>Name</th><th>Last Login Date</th></tr></thead><tbody>";
        while ($row = $resultInactiveSevenDays->fetch_assoc()) {
            echo "<tr><td>" . $row['FName'] . "</td><td>" . $row['last_login'] . "</td></tr>";
        }
        echo "</tbody></table></div>";
    
        echo "<h4>Inactive students for 28 days and above</h4>";
        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered">';
        echo "<thead><tr><th>Name</th><th>Last Login Date</th></tr></thead><tbody>";
        while ($row = $resultInactiveTwentyEightDays->fetch_assoc()) {
            echo "<tr><td>" . $row['FName'] . "</td><td>" . $row['last_login'] . "</td></tr>";
        }
        echo "</tbody></table></div>";
    
        echo "<h4>Students without tutor assignments</h4>";
        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered">';
        echo "<thead><tr><th>Name</th></tr></thead><tbody>";
        while ($row = $resultStudentsWithoutTutor->fetch_assoc()) {
            echo "<tr><td>" . $row['FName'] . "</td></tr>";
        }
        echo "</tbody></table></div>";
    }
    

    // Display the dashboard based on the user role
    displayDashboard($user_role, $userID, $conn);

} else {
    echo "User role parameter is missing";
}
?>
