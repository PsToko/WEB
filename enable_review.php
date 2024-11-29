<?php
include 'access.php';
session_start();

// Ελέγξτε αν ο χρήστης είναι συνδεδεμένος και έχει δικαιώματα
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professors') {
    header("Location: login.php?block=1");
    exit();
}

// Παράμετρος examination_id
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['examination_id'])) {
    $examination_id = $_POST['examination_id'];

    // Ενημέρωση can_review
    $update_sql = "UPDATE examination SET can_review = 1 WHERE examinationID = ?";
    $stmt = $con->prepare($update_sql);
    $stmt->bind_param("i", $examination_id);

    if ($stmt->execute()) {
        echo "<p>Η δυνατότητα αξιολόγησης ενεργοποιήθηκε με επιτυχία.</p>";
        header("Location: review.php");
    } else {
        echo "<p>Σφάλμα κατά την ενεργοποίηση της δυνατότητας αξιολόγησης.</p>";
    }
    $stmt->close();
} else {
    echo "<p>Μη έγκυρη αίτηση.</p>";
}
?>
