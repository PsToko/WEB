<?php
include 'access.php';
session_start();

// Βεβαιωθείτε ότι ο χρήστης είναι συνδεδεμένος
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Δεν υπάρχει συνδεδεμένος χρήστης.']);
    exit();
}

$professorID = $_SESSION['user_id']; // Το ID του συνδεδεμένου καθηγητή
$thesisID = isset($_GET['thesisID']) ? intval($_GET['thesisID']) : null;

if (!$thesisID) {
    echo json_encode(['success' => false, 'error' => 'Λείπει το thesisID.']);
    exit();
}

// Ενημερωμένη SQL ερώτηση με φίλτρο για professorID
$query = "
    SELECT tc.comment, p.name AS professor_name, p.surname AS professor_surname
    FROM thesiscomments tc
    JOIN professors p ON tc.professorID = p.Professor_ID
    WHERE tc.thesisID = ? AND tc.professorID = ?
";

$stmt = $con->prepare($query);
$stmt->bind_param('ii', $thesisID, $professorID);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = [
        'comment' => $row['comment'],
    ];
}

echo json_encode(['success' => true, 'comments' => $comments]);
?>
