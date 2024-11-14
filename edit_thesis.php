<?php
include 'access.php';
session_start();

// Check if the user is logged in and has the required role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professors') {
    header("Location: login.php?block=1");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $summary = $_POST['summary'];
    $pdfFileName = '';

    // Handle new PDF upload if provided
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

    // Build the SQL query to update the thesis
    if ($pdfFileName) {
        // If a new PDF was uploaded, update all fields
        $query = "UPDATE thesis SET title = ?, description = ?, pdf = ? WHERE thesisID = ? AND supervisorID = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param('sssii', $title, $summary, $pdfFileName, $id, $_SESSION['user_id']);
    } else {
        // If no new PDF, update only title and description
        $query = "UPDATE thesis SET title = ?, description = ? WHERE thesisID = ? AND supervisorID = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param('ssii', $title, $summary, $id, $_SESSION['user_id']);
    }

    // Execute the query
    if ($stmt->execute()) {
        header("Location: show_dipl.php?add=1");
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    $con->close();
}
?>
