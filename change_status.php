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

    $userID = $_SESSION['user_id'];

    // Validate if the user is the supervisor of the thesis
    $supervisorCheckQuery = "SELECT supervisorID, member1ID, member2ID, studentID FROM thesis WHERE thesisID = ?";
    $stmt = $con->prepare($supervisorCheckQuery);
    $stmt->bind_param('i', $thesisID);
    $stmt->execute();
    $stmt->bind_result($supervisorID, $member1ID, $member2ID, $studentID);
    $stmt->fetch();
    $stmt->close();

    if ($supervisorID != $userID) {
        echo json_encode(['success' => false, 'error' => 'Δεν έχετε δικαίωμα να επεξεργαστείτε αυτή τη διπλωματική.']);
        exit();
    }

    $con->begin_transaction();

    try {
        // Update thesis status
        $updateThesisQuery = "UPDATE thesis SET status = ? WHERE thesisID = ?";
        $updateStmt = $con->prepare($updateThesisQuery);
        $updateStmt->bind_param('si', $newStatus, $thesisID);

        if (!$updateStmt->execute()) {
            throw new Exception('Σφάλμα κατά την ενημέρωση της κατάστασης.');
        }

        // Insert a new record into Examination if not exists
        $checkExaminationQuery = "SELECT 1 FROM Examination WHERE thesisID = ?";
        $checkStmt = $con->prepare($checkExaminationQuery);
        $checkStmt->bind_param('i', $thesisID);
        $checkStmt->execute();
        $exists = $checkStmt->get_result()->num_rows > 0;

        if (!$exists) {
            $insertExaminationQuery = "
                INSERT INTO Examination (thesisID, supervisorID, member1ID, member2ID, StudentID)
                VALUES (?, ?, ?, ?, ?)";
            $insertStmt = $con->prepare($insertExaminationQuery);
            $insertStmt->bind_param('iiiii', $thesisID, $supervisorID, $member1ID, $member2ID, $studentID);

            if (!$insertStmt->execute()) {
                throw new Exception('Σφάλμα κατά την προσθήκη στην Examination.');
            }
        }

        $con->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $con->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>
