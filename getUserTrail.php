<?php
require_once 'db.php';
require_once('vendor/autoload.php');
use \Firebase\JWT\JWT;

$token = $_COOKIE['token'];
$secretKey = 'your_secret_key'; // Change to your actual secret key
$decoded = JWT::decode($token, $secretKey, array('HS256'));
$userRole = $decoded->role;
echo "<script>var userRole = '$userRole';</script>";

function getUserTrail($conn, $userID, $userRole, $selectedDate = null, $page = 1, $results_per_page = 10) {
    // Sanitize input
    $userID = mysqli_real_escape_string($conn, $userID);
    $userRole = mysqli_real_escape_string($conn, $userRole);
    $page = mysqli_real_escape_string($conn, $page);

    // Get the user's name based on the userRole and userID
    $userName = '';
    switch ($userRole) {
        case 'student':
            $sql = "SELECT FName, LName FROM Student WHERE SID = '$userID'";
            break;
        case 'tutor':
            $sql = "SELECT FName, LName FROM Tutor WHERE TID = '$userID'";
            break;
        case 'admin':
            $sql = "SELECT FName, LName FROM Admin WHERE AID = '$userID'";
            break;
        default:
            $sql = '';
    }

    if ($sql !== '') {
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $userName = $row['FName'] . ' ' . $row['LName'];
        }
    }

    // Build the SQL query
    $sql = "SELECT * FROM trail WHERE userID = '$userID' AND userRole = '$userRole'";
    
    // Add the date filter if a date is selected
    if ($selectedDate) {
        $sql .= " AND DATE(actionTime) = '$selectedDate'";
    }

    // Calculate the offset
    $offset = ($page - 1) * $results_per_page;

    // Add sorting and pagination to the SQL query
    $sql .= " ORDER BY actionTime DESC LIMIT $offset, $results_per_page";

    $result = $conn->query($sql);

    // Display trail records in a table
    echo '<form id="dateForm" method="POST">';
    echo '<h2>'. $userName .' activities</h2>';
    echo '<br/>';
    echo '<label for="datepicker">Select Date: </label>';
    echo '<input type="date" id="datepicker" name="datepicker" value="' . ($selectedDate ? $selectedDate : '') . '">';
    echo '<button type="submit" id="submitDate">Submit</button>';
    echo '<button type="button" id="clear">Clear filter</button>';
    echo '<button type="button" id="back">Back</button>';
    echo '<button type="button" id="download" onclick="downloadExcel()">Download</button>';
    echo '</form>';
    echo '<br/><br/>';
    echo '<table class="table">
            <thead>
                <tr>
                    <th>Action Time</th>
                    <th>Action Performed</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>';

    if ($result->num_rows > 0) {
        // Display trail records
        while ($row = $result->fetch_assoc()) {
            echo '<tr>
                    <td>' . $row['actionTime'] . '</td>
                    <td>' . $row['actionPerformed'] . '</td>
                    <td>' . $row['ip_address'] . '</td>
                </tr>';
        }
    } else {
        // No trail records found
        echo '<tr><td colspan="3">No trail records found for the selected user.</td></tr>';
    }

    echo '</tbody>
        </table>';

    // Pagination
    echo '<div class="pagination">';
    $sql = "SELECT COUNT(*) AS total FROM trail WHERE userID = '$userID' AND userRole = '$userRole'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $total_pages = ceil($row["total"] / $results_per_page);
    for ($i = 1; $i <= $total_pages; $i++) {
        echo '<a href="#" class="page-link userTrailPageLink" data-page="' . $i . '">' . $i . '</a>';
    }
    echo '</div>';
}

// Assuming $conn is your database connection
// Check if userID and userRole are set in the POST request
if (isset($_POST['userID']) && isset($_POST['userRole'])) {
    // If date filter is set, get it from the POST request
    $selectedDate = isset($_POST['selectedDate']) ? $_POST['selectedDate'] : null;
    $page = isset($_POST['page']) ? $_POST['page'] : 1;
    
    // Call the function to display user trail
    getUserTrail($conn, $_POST['userID'], $_POST['userRole'], $selectedDate, $page);
} else {
    echo 'User ID and user role not specified.';
}
?>
<script>
    $(document).ready(function() {
        $("#dateForm").submit(function(event) {
            event.preventDefault();
            var selectedDate = $("#datepicker").val();
            // Send the selected date to the server
            $.ajax({
                url: "getUserTrail.php",
                type: "POST",
                data: {
                    userID: <?php echo $_POST['userID']; ?>,
                    userRole: "<?php echo $_POST['userRole']; ?>",
                    selectedDate: selectedDate
                },
                success: function(data) {
                    $("#componentContainer").html(data);
                },
                error: function(xhr, status, error) {
                    console.error("An error occurred:", error);
                }
            });
        });
        
        // Add event listener for the "Clear" button click
        $("#clear").click(function() {
            $("#datepicker").val(''); // Clear the datepicker value
            $("#dateForm").submit(); // Submit the form to reload without the date filter
        });

        $("#back").click(function() {

            var url;
            if (userRole === 'student') {
                url = 'selectUserTrailStudent.php';
            } else if (userRole === 'admin') {
                url = 'selectUserTrail.php';
            } else if (userRole === 'tutor') {
                url = 'selectUserTrailTutor.php';
            }

            // Make AJAX request with the determined URL
            $.ajax({
                url: url,
                success: function(data) {
                    $('#componentContainer').html(data);
                },
                error: function(xhr, status, error) {
                    console.error('An error occurred:', error);
                }
            });
        });

        // Pagination
        $(".userTrailPageLink").click(function(event) {
            event.preventDefault();
            var page = $(this).data("page");
            $.ajax({
                url: "getUserTrail.php",
                type: "POST",
                data: {
                    userID: <?php echo $_POST['userID']; ?>,
                    userRole: "<?php echo $_POST['userRole']; ?>",
                    selectedDate: $("#datepicker").val(),
                    page: page
                },
                success: function(data) {
                    $("#componentContainer").html(data);
                },
                error: function(xhr, status, error) {
                    console.error("An error occurred:", error);
                }
            });
        });
    });

    function downloadExcel() {
        // Get userID and userRole from the form
        var userID = <?php echo $_POST['userID']; ?>;
        var userRole = "<?php echo $_POST['userRole']; ?>";

        // Redirect to downloadUserTrail.php with userID and userRole as parameters
        window.location.href = "downloadUserTrail.php?userID=" + userID + "&userRole=" + userRole;
    }
</script>
