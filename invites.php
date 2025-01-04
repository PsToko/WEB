<?php
// invites.php
include 'access.php';

// Έναρξη συνεδρίας
session_start();

// Έλεγχος αν ο χρήστης είναι συνδεδεμένος και έχει ρόλο καθηγητή
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professors') {
    header("Location: login.php?block=1");
    exit();
}

// Λήψη του ID του καθηγητή από τη συνεδρία
$professorID = $_SESSION['user_id'];

// Αρχικοποίηση μηνυμάτων επιτυχίας και σφάλματος
$successMessage = $errorMessage = "";

// Διαχείριση ενεργειών αποδοχής/απόρριψης
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invitation_id'], $_POST['thesis_id'], $_POST['action'])) {
    $invitationID = $_POST['invitation_id'];
    $thesisID = $_POST['thesis_id'];
    $action = $_POST['action']; // 'accept' ή 'reject'

    $newStatus = $action === 'accept' ? 'accepted' : 'rejected';

    // Ενημέρωση της κατάστασης της πρόσκλησης
    $updateInvitationQuery = "UPDATE invitations SET status = ?, responseDate = NOW() WHERE invitationID = ?";
    $stmt = $con->prepare($updateInvitationQuery);
    $stmt->bind_param('si', $newStatus, $invitationID);

    if ($stmt->execute()) {
        $successMessage = "Η πρόσκληση έχει $newStatus επιτυχώς!";

        if ($action === 'accept') {
            // Ανάκτηση των τρεχουσών μελών
            $checkMembersQuery = "SELECT member1ID, member2ID FROM Thesis WHERE thesisID = ?";
            $checkStmt = $con->prepare($checkMembersQuery);
            $checkStmt->bind_param('i', $thesisID);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result()->fetch_assoc();

            $member1ID = $checkResult['member1ID'];
            $member2ID = $checkResult['member2ID'];

            // Ενημέρωση του κατάλληλου μέλους
            if (is_null($member1ID)) {
                $updateMemberQuery = "UPDATE Thesis SET member1ID = ? WHERE thesisID = ?";
                $updateStmt = $con->prepare($updateMemberQuery);
                $updateStmt->bind_param('ii', $professorID, $thesisID);
                $updateStmt->execute();
            } elseif (is_null($member2ID)) {
                $updateMemberQuery = "UPDATE Thesis SET member2ID = ? WHERE thesisID = ?";
                $updateStmt = $con->prepare($updateMemberQuery);
                $updateStmt->bind_param('ii', $professorID, $thesisID);
                $updateStmt->execute();
            }

            // Επανέλεγχος αν και τα δύο μέλη είναι συμπληρωμένα
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result()->fetch_assoc();

            if (!is_null($checkResult['member1ID']) && !is_null($checkResult['member2ID'])) {
                // Ενημέρωση κατάστασης διατριβής σε ενεργή και ορισμός ημερομηνίας ανάθεσης
                $activateThesisQuery = "UPDATE Thesis SET status = 'active', assignmentDate = CURDATE() WHERE thesisID = ?";
                $activateStmt = $con->prepare($activateThesisQuery);
                $activateStmt->bind_param('i', $thesisID);
                $activateStmt->execute();

                // Διαγραφή όλων των εκκρεμών και απορριφθέντων προσκλήσεων για αυτήν τη διατριβή
                $deleteInvitationsQuery = "DELETE FROM Invitations WHERE thesisID = ? AND (status = 'pending' OR status = 'rejected')";
                $deleteStmt = $con->prepare($deleteInvitationsQuery);
                $deleteStmt->bind_param('i', $thesisID);
                $deleteStmt->execute();
            }
        }
    } else {
        $errorMessage = "Σφάλμα κατά την ενημέρωση της πρόσκλησης: " . $stmt->error;
    }
}

// Ανάκτηση προσκλήσεων για τον συνδεδεμένο καθηγητή
$invitationsQuery = "
    SELECT 
        i.invitationID, 
        t.thesisID, 
        t.title AS thesis_title, 
        CONCAT(s.Name, ' ', s.Surname) AS student_name, 
        i.status, 
        i.sentDate 
    FROM Invitations i
    JOIN Thesis t ON i.thesisID = t.thesisID
    JOIN Students s ON i.studentID = s.Student_ID
    WHERE i.professorID = ?
    ORDER BY i.sentDate DESC";
$invitationsStmt = $con->prepare($invitationsQuery);
$invitationsStmt->bind_param('i', $professorID);
$invitationsStmt->execute();
$invitations = $invitationsStmt->get_result();

// Include the global menu
include 'menus/menu.php';

?>



<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Προσκλήσεις</title>
    <!-- <link rel="stylesheet" href="lobby.css">-->
    <link rel="stylesheet" href="AllCss.css">
</head>
<body>
    <div class="container">

        <!-- Τίτλος Σελίδας -->
        <h1>Οι Προσκλήσεις σας</h1>

        <!-- Μηνύματα Επιτυχίας/Σφάλματος -->
        <?php if (!empty($successMessage)): ?>
            <p class="success"><?= htmlspecialchars($successMessage) ?></p>
        <?php elseif (!empty($errorMessage)): ?>
            <p class="error"><?= htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>

        <!-- Πίνακας Προσκλήσεων -->
        <table>
            <thead>
                <tr>
                    <th>Τίτλος Διατριβής</th>
                    <th>Όνομα Φοιτητή</th>
                    <th>Κατάσταση</th>
                    <th>Ημερομηνία Αποστολής</th>
                    <th>Ενέργειες</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($invitations->num_rows > 0): ?>
                    <?php while ($row = $invitations->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['thesis_title']) ?></td>
                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                            <td><?= htmlspecialchars(ucfirst($row['status'])) ?></td>
                            <td><?= htmlspecialchars($row['sentDate']) ?></td>
                            <td class="action-buttons">
                                <?php if ($row['status'] === 'pending'): ?>
                                    <form method="POST" action="">
                                        <input type="hidden" name="invitation_id" value="<?= $row['invitationID'] ?>">
                                        <input type="hidden" name="thesis_id" value="<?= $row['thesisID'] ?>">
                                        <input type="hidden" name="action" value="accept">
                                        <button type="submit">Αποδοχή</button>
                                    </form>
                                    <form method="POST" action="">
                                        <input type="hidden" name="invitation_id" value="<?= $row['invitationID'] ?>">
                                        <input type="hidden" name="thesis_id" value="<?= $row['thesisID'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit">Απόρριψη</button>
                                    </form>
                                <?php else: ?>
                                    <span><?= ucfirst($row['status']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Δεν βρέθηκαν προσκλήσεις.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>