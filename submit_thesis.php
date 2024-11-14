<?php
include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has the required role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professors') {
    header("Location: login.php?block=1");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $summary = $_POST['summary'];
    $status = 'under assignment';
    $pdfFileName = '';

    // Handle PDF upload
    if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] == UPLOAD_ERR_OK) {
        $pdfFileName = basename($_FILES['pdf']['name']);
        $uploadDir = 'uploads/';
        $uploadFilePath = $uploadDir . $pdfFileName;

        // Ensure the upload directory exists
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Move the uploaded file
        if (move_uploaded_file($_FILES['pdf']['tmp_name'], $uploadFilePath)) {
            // File uploaded successfully
        } else {
            echo "Error uploading file.";
            exit();
        }
    }

    // Insert thesis information into the database
    $query = "INSERT INTO thesis (title, description, status, supervisorID, postedDate, pdf) 
              VALUES (?, ?, ?, ?, NOW(), ?)";
    if ($stmt = $con->prepare($query)) {
        $stmt->bind_param('sssis', $title, $summary, $status, $_SESSION['user_id'], $pdfFileName);
        if ($stmt->execute()) {
            header("Location: professor.php");
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $con->error;
    }

    $con->close();
}
?>
