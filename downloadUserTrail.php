<?php
require_once 'db.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Function to generate Excel file from trail records
function generateExcelFile($conn, $userID, $userRole) {
    // Sanitize input
    $userID = mysqli_real_escape_string($conn, $userID);
    $userRole = mysqli_real_escape_string($conn, $userRole);

    $userName = '';
    switch ($userRole) {
        case 'student':
            $sql = "SELECT FName FROM Student WHERE SID = '$userID'";
            break;
        case 'tutor':
            $sql = "SELECT FName FROM Tutor WHERE TID = '$userID'";
            break;
        case 'admin':
            $sql = "SELECT FName FROM Admin WHERE AID = '$userID'";
            break;
        default:
            $sql = '';
    }

    if ($sql !== '') {
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $userName = $row['FName'];
        }
    }

    // Build the SQL query to fetch all trail records for the user
    $sql = "SELECT * FROM trail WHERE userID = '$userID' AND userRole = '$userRole'";
    $result = $conn->query($sql);

    // Create a new PhpSpreadsheet object
    $spreadsheet = new Spreadsheet();

    // Get the active sheet
    $sheet = $spreadsheet->getActiveSheet();

    // Set title
    $sheet->setCellValue('A1', '@' . $userName . ' activities');
    $sheet->mergeCells('A1:C1');

    // Set column headers
    $sheet->setCellValue('A2', 'Action Time')
          ->setCellValue('B2', 'Action Performed')
          ->setCellValue('C2', 'IP Address');

    // Add trail records to Excel file
    $row = 3;
    while ($row_data = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $row, $row_data['actionTime'])
              ->setCellValue('B' . $row, $row_data['actionPerformed'])
              ->setCellValue('C' . $row, $row_data['ip_address']);
        $row++;
    }

    // Save Excel file
    $filename = 'user_trail_' . $userName . '.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);

    return $filename;
}

// Check if userID and userRole are set in the GET request
if (isset($_GET['userID']) && isset($_GET['userRole'])) {
    // Call the function to generate Excel file
    $excelFilePath = generateExcelFile($conn, $_GET['userID'], $_GET['userRole']);

    // Provide download headers to force download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . basename($excelFilePath) . '"');
    header('Content-Length: ' . filesize($excelFilePath));

    // Output the file content directly
    readfile($excelFilePath);

    // Delete the file after download (optional)
    unlink($excelFilePath);
    
    // Exit script after download
    exit;
} else {
    echo 'User ID and user role not specified.';
}
?>
