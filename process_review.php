<?php
include 'access.php';
session_start();

// Ελέγξτε αν ο χρήστης είναι συνδεδεμένος
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professors') {
    header("Location: login.php?block=1");
    exit();
}

// Ελέγξτε αν όλα τα δεδομένα έχουν υποβληθεί
if (!isset($_POST['examination_id'], $_POST['criteria1'], $_POST['criteria2'], $_POST['criteria3'], $_POST['criteria4'])) {
    die("Λείπουν απαραίτητα δεδομένα.");
}

$examination_id = $_POST['examination_id'];
$criteria1 = (float) $_POST['criteria1'];
$criteria2 = (float) $_POST['criteria2'];
$criteria3 = (float) $_POST['criteria3'];
$criteria4 = (float) $_POST['criteria4'];

// Υπολογισμός του τελικού βαθμού
$final_grade = ($criteria1 * 0.6) + ($criteria2 * 0.15) + ($criteria3 * 0.15) + ($criteria4 * 0.1);

// Λάβετε το user_id του συνδεδεμένου χρήστη
$user_id = $_SESSION['user_id'];

// Λήψη των απαραίτητων πληροφοριών για την εξέταση και τη διπλωματική
$sql = "SELECT t.supervisorID, t.member1ID, t.member2ID, t.thesisID 
        FROM examination e
        JOIN thesis t ON e.thesisID = t.thesisID
        WHERE e.examinationID = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $examination_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Δεν βρέθηκαν δεδομένα για την εξέταση.");
}

$data = $result->fetch_assoc();
$thesis_id = $data['thesisID'];

// Καθορισμός του πεδίου που θα ενημερωθεί
$field_to_update = '';
if ($data['supervisorID'] == $user_id) {
    $field_to_update = 'finalGrade';
} elseif ($data['member1ID'] == $user_id) {
    $field_to_update = 'member1Grade';
} elseif ($data['member2ID'] == $user_id) {
    $field_to_update = 'member2Grade';
} else {
    die("Δεν έχετε δικαίωμα να υποβάλετε βαθμολογία για αυτήν την εξέταση.");
}

// Ενημέρωση του αντίστοιχου πεδίου στη βάση δεδομένων
$update_sql = "UPDATE thesis SET $field_to_update = ? WHERE thesisID = ?";
$update_stmt = $con->prepare($update_sql);
$update_stmt->bind_param("di", $final_grade, $thesis_id);

if ($update_stmt->execute()) {
    echo "<p>Η βαθμολογία υποβλήθηκε με επιτυχία. Τελικός Βαθμός: " . number_format($final_grade, 2) . "</p>";
    header("Location: all_thesis.php");
} else {
    echo "<p>Σφάλμα κατά την υποβολή της βαθμολογίας: " . $update_stmt->error . "</p>";
}
?>
