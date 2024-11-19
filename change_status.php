<?php
include 'access.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $thesisID = $data['thesisID'];
    $newStatus = $data['newStatus'];

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Δεν έχετε συνδεθεί.']);
        exit();
    }

    // Έλεγχος αν ο χρήστης είναι supervisor της διπλωματικής
    $query = "SELECT supervisorID FROM thesis WHERE thesisID = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param('i', $thesisID);
    $stmt->execute();
    $stmt->bind_result($supervisorID);
    $stmt->fetch();
    $stmt->close();

    if ($supervisorID != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'error' => 'Δεν έχετε δικαίωμα να επεξεργαστείτε αυτή τη διπλωματική.']);
        exit();
    }

    // Ενημέρωση κατάστασης
    $updateQuery = "UPDATE thesis SET status = ? WHERE thesisID = ?";
    $updateStmt = $con->prepare($updateQuery);
    $updateStmt->bind_param('si', $newStatus, $thesisID);

    if ($updateStmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Σφάλμα κατά την ενημέρωση.']);
    }

    $updateStmt->close();
    $con->close();
}
?>
