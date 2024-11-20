<?php
include 'access.php';
session_start();

// Ελέγξτε αν ο χρήστης είναι συνδεδεμένος
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professors') {
    echo json_encode(['success' => false, 'error' => 'Μη εξουσιοδοτημένη πρόσβαση.']);
    exit();
}

// Ελέγξτε αν το αίτημα είναι POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Μη έγκυρη μέθοδος αιτήματος.']);
    exit();
}

// Διαβάστε τα δεδομένα από το request
$data = json_decode(file_get_contents('php://input'), true);

// Ελέγξτε αν τα δεδομένα είναι σωστά
if (!$data || !isset($data['thesisID']) || !isset($data['newStatus'])) {
    echo json_encode(['success' => false, 'error' => 'Λείπουν δεδομένα ή μη έγκυρη μορφή.']);
    exit();
}

$thesisID = $data['thesisID'];
$newStatus = $data['newStatus'];

// Ελέγξτε αν η διπλωματική ανήκει στον τρέχοντα χρήστη ως επιβλέποντα
$query = "
    SELECT supervisorID, assignmentDate, status, studentID
    FROM thesis
    WHERE thesisID = ?
";
$stmt = $con->prepare($query);
$stmt->bind_param('i', $thesisID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row || $row['supervisorID'] != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => 'Δεν έχετε δικαίωμα πρόσβασης.']);
    exit();
}

// Ελέγξτε την ημερομηνία ανάθεσης
$currentDate = new DateTime();
$assignmentDate = new DateTime($row['assignmentDate']);
$twoYearsAgo = (new DateTime())->modify('-2 years');

if ($newStatus === 'withdrawn' && $row['status'] === 'active' && $assignmentDate <= $twoYearsAgo) {
    // Ξεκινήστε συναλλαγή
    $con->begin_transaction();
    try {
        // Ενημέρωση thesis: αλλαγή κατάστασης, προσθήκη withdrawalDate και withdrawn_comment
        $updateThesisQuery = "
            UPDATE thesis 
            SET status = ?, withdrawalDate = ?, withdrawn_comment = ? 
            WHERE thesisID = ?
        ";
        $withdrawalDate = $currentDate->format('Y-m-d H:i:s');
        $withdrawnComment = 'from professor';
        $updateThesisStmt = $con->prepare($updateThesisQuery);
        $updateThesisStmt->bind_param('sssi', $newStatus, $withdrawalDate, $withdrawnComment, $thesisID);
        $updateThesisStmt->execute();

        // Ενημέρωση student: αλλαγή has_Thesis σε 0
        if (!empty($row['studentID'])) {
            $updateStudentQuery = "
                UPDATE students 
                SET has_Thesis = 0 
                WHERE Student_ID = ?
            ";
            $updateStudentStmt = $con->prepare($updateStudentQuery);
            $updateStudentStmt->bind_param('i', $row['studentID']);
            $updateStudentStmt->execute();
        }

        // Επικύρωση συναλλαγής
        $con->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Ακύρωση συναλλαγής σε περίπτωση σφάλματος
        $con->rollback();
        echo json_encode(['success' => false, 'error' => 'Σφάλμα κατά την ενημέρωση: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Δεν πληρούνται οι προϋποθέσεις.']);
}
?>
