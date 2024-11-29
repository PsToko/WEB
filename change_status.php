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
    $query = "SELECT supervisorID, StudentID, member1ID, member2ID FROM thesis WHERE thesisID = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param('i', $thesisID);
    $stmt->execute();
    $stmt->bind_result($supervisorID, $studentID, $member1ID, $member2ID);
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
        // Εισαγωγή στον πίνακα examination με όλα τα απαραίτητα δεδομένα
        $insertQuery = "
            INSERT INTO examination (thesisID, StudentID, supervisorID, member1ID, member2ID)
            SELECT ?, ?, ?, ?, ? 
            WHERE EXISTS (SELECT 1 FROM thesis WHERE thesisID = ?)
        ";
        $insertStmt = $con->prepare($insertQuery);
        $insertStmt->bind_param('iiiiii', $thesisID, $studentID, $supervisorID, $member1ID, $member2ID, $thesisID);

        if ($insertStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Η κατάσταση ενημερώθηκε και προστέθηκε εξέταση.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Σφάλμα κατά την προσθήκη στην εξέταση.']);
        }

        $insertStmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Σφάλμα κατά την ενημέρωση.']);
    }

    $updateStmt->close();
    $con->close();
}
?>
