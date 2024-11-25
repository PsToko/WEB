<?php
include 'access.php';
session_start();

// Check if the user is logged in and has professor privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professors') {
    header("Location: login.php?block=1");
    exit();
}

// Handle cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['thesisID'], $_POST['studentID'])) {
    $thesisID = $_POST['thesisID'];
    $studentID = $_POST['studentID'];

    // Start a transaction to ensure all changes are applied together
    $con->begin_transaction();

    try {
        // Set studentID and member1ID to NULL in the thesis table
        $updateThesisQuery = "UPDATE thesis SET studentID = NULL, member1ID = NULL WHERE thesisID = ?";
        $updateThesisStmt = $con->prepare($updateThesisQuery);
        $updateThesisStmt->bind_param('i', $thesisID);
        $updateThesisStmt->execute();

        // Set Has_Thesis to 0 for the student
        $updateStudentQuery = "UPDATE students SET Has_Thesis = 0 WHERE student_ID = ?";
        $updateStudentStmt = $con->prepare($updateStudentQuery);
        $updateStudentStmt->bind_param('i', $studentID);
        $updateStudentStmt->execute();

        // Delete all invitations sent by this student regarding this thesis
        $deleteInvitationsQuery = "DELETE FROM invitations WHERE studentID = ? AND thesisID = ?";
        $deleteInvitationsStmt = $con->prepare($deleteInvitationsQuery);
        $deleteInvitationsStmt->bind_param('ii', $studentID, $thesisID);
        $deleteInvitationsStmt->execute();

        // Commit the transaction
        $con->commit();
    } catch (Exception $e) {
        // Roll back the transaction in case of any error
        $con->rollback();
        echo "Error during unassignment: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Αναθέσεις Φοιτητών</title>
    <link rel="stylesheet" href="dipl.css">
</head>
<body>
    <div class="container">
        <h1>Αναθέσεις Φοιτητών</h1>

        <div class="assigned-topics">
            <h2>Ανατεθειμένα Θέματα</h2>
            <?php
            // Query to fetch assigned thesis topics
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

                    // Cancel button form
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

        <button class="add-topic-button" onclick="window.location.href = 'delegation.php';">Επιστροφή</button>
        </div>
</body>
</html>
