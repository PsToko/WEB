<?php
include 'access.php';
session_start();

// Ελέγξτε αν ο χρήστης είναι συνδεδεμένος και έχει δικαιώματα καθηγητή
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professors') {
    header("Location: login.php?block=1");
    exit();
}

// Διαχείριση ακύρωσης
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['thesisID'], $_POST['studentID'])) {
    $thesisID = $_POST['thesisID'];
    $studentID = $_POST['studentID'];

    // Ξεκινήστε μια συναλλαγή για να εξασφαλίσετε ότι όλες οι αλλαγές θα εφαρμοστούν μαζί
    $con->begin_transaction();

    try {
        // Ρυθμίστε τα πεδία studentID και member1ID σε NULL στον πίνακα thesis
        $updateThesisQuery = "UPDATE thesis SET studentID = NULL, member1ID = NULL WHERE thesisID = ?";
        $updateThesisStmt = $con->prepare($updateThesisQuery);
        $updateThesisStmt->bind_param('i', $thesisID);
        $updateThesisStmt->execute();

        // Ρυθμίστε το πεδίο Has_Thesis σε 0 για τον φοιτητή
        $updateStudentQuery = "UPDATE students SET Has_Thesis = 0 WHERE student_ID = ?";
        $updateStudentStmt = $con->prepare($updateStudentQuery);
        $updateStudentStmt->bind_param('i', $studentID);
        $updateStudentStmt->execute();

        // Διαγράψτε όλες τις προσκλήσεις που έχει στείλει αυτός ο φοιτητής σχετικά με αυτό το θέμα
        $deleteInvitationsQuery = "DELETE FROM invitations WHERE studentID = ? AND thesisID = ?";
        $deleteInvitationsStmt = $con->prepare($deleteInvitationsQuery);
        $deleteInvitationsStmt->bind_param('ii', $studentID, $thesisID);
        $deleteInvitationsStmt->execute();

        // Επιβεβαίωση της συναλλαγής
        $con->commit();
    } catch (Exception $e) {
        // Επιστροφή στην προηγούμενη κατάσταση σε περίπτωση σφάλματος
        $con->rollback();
        echo "Σφάλμα κατά την ακύρωση της ανάθεσης: " . $e->getMessage();
    }
}

// Include the global menu
include 'menus/menu.php';

?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Αναθέσεις Φοιτητών</title>
    <link rel="stylesheet" href="AllCss.css">
</head>
<body>
    <div class="container">
        <h1>Αναθέσεις Φοιτητών</h1>

        <div class="assigned-topics">
            <h2>Ανατεθειμένα Θέματα</h2>
            <?php
            // Ερώτημα για την ανάκτηση των ανατεθειμένων θεμάτων
            $query = "
                SELECT t.thesisID, t.title, t.description, s.student_ID, s.name, s.surname, s.AM
                FROM thesis t
                JOIN students s ON t.studentID = s.student_ID
                WHERE t.supervisorID = ? AND t.status = 'under assignment' AND t.studentID IS NOT NULL
            ";
            $stmt = $con->prepare($query);
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="assigned-topic">';
                    echo '<h3>Θέμα: ' . htmlspecialchars($row['title']) . '</h3>';
                    echo '<p>Σύνοψη: ' . htmlspecialchars($row['description']) . '</p>';
                    echo '<p>Φοιτητής: ' . htmlspecialchars($row['AM']) . ' - ' . htmlspecialchars($row['name']) . ' ' . htmlspecialchars($row['surname']) . '</p>';

                    // Φόρμα για ακύρωση ανάθεσης
                    echo '<form method="POST" action="">';
                    echo '<input type="hidden" name="thesisID" value="' . $row['thesisID'] . '">';
                    echo '<input type="hidden" name="studentID" value="' . $row['student_ID'] . '">';
                    echo '<button type="submit" class="cancel-button">Ακύρωση Ανάθεσης</button>';
                    echo '</form>';

                    echo '</div>';
                }
            } else {
                echo '<p>Δεν υπάρχουν ανατεθειμένα θέματα.</p>';
            }
            ?>
        </div>

    </div>
</body>
</html>