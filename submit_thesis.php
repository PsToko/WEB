<?php
include 'access.php';

// Ξεκινάμε τη συνεδρία
session_start();

// Ελέγχουμε αν ο χρήστης είναι συνδεδεμένος και έχει τον απαιτούμενο ρόλο
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professors') {
    header("Location: login.php?block=1");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $summary = $_POST['summary'];
    $status = 'under assignment';
    $pdfFileName = '';

    // Διαχείριση ανέβασματος PDF
    if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] == UPLOAD_ERR_OK) {
        $pdfFileName = basename($_FILES['pdf']['name']);
        $uploadDir = 'uploads/';
        $uploadFilePath = $uploadDir . $pdfFileName;

        // Εξασφαλίζουμε ότι ο φάκελος ανέβασματων υπάρχει
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Μετακίνηση του ανεβασμένου αρχείου
        if (move_uploaded_file($_FILES['pdf']['tmp_name'], $uploadFilePath)) {
            // Το αρχείο ανέβηκε με επιτυχία
        } else {
            echo "Σφάλμα κατά το ανέβασμα του αρχείου.";
            exit();
        }
    }

    // Εισαγωγή των στοιχείων της διπλωματικής στο database
    $query = "INSERT INTO thesis (title, description, status, supervisorID, postedDate, pdf) 
              VALUES (?, ?, ?, ?, NOW(), ?)";
    if ($stmt = $con->prepare($query)) {
        $stmt->bind_param('sssis', $title, $summary, $status, $_SESSION['user_id'], $pdfFileName);
        if ($stmt->execute()) {
            header("Location: show_dipl.php?add=1");
        } else {
            echo "Σφάλμα: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Σφάλμα κατά την προετοιμασία της δήλωσης: " . $con->error;
    }

    $con->close();
}
?>