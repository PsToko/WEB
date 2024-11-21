<?php
// invites.php
include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has professor privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professors') {
    header("Location: login.php?block=1");
    exit();
}


// Get dynamic professor ID from session
$professorID = $_SESSION['user_id'];

// Fetch invitations for the logged-in professor
$invitations = $con->query("
    SELECT i.invitationID, t.thesisID, t.title AS thesis_title, CONCAT(s.Name, ' ', s.Surname) AS student_name, i.status, i.sentDate 
    FROM Invitations i
    JOIN Thesis t ON i.thesisID = t.thesisID
    JOIN Students s ON i.studentID = s.Student_ID
    WHERE i.professorID = $professorID
    ORDER BY i.sentDate DESC
");

// Handle accept/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['invitation_id'], $_POST['thesis_id'], $_POST['action'])) {
        $invitationID = $_POST['invitation_id'];
        $thesisID = $_POST['thesis_id'];
        $action = $_POST['action']; // 'accept' or 'reject'

        $newStatus = $action === 'accept' ? 'accepted' : 'rejected';

        // Update the invitation status
        $sql = "UPDATE Invitations SET status = '$newStatus', responseDate = NOW() WHERE invitationID = $invitationID";

        if ($con->query($sql) === TRUE) {
            $successMessage = "Invitation has been $newStatus successfully!";

            // After accepting, check if both members are now filled
            if ($action === 'accept') {
                $checkMembers = $con->query("SELECT member1ID, member2ID FROM Thesis WHERE thesisID = $thesisID")->fetch_assoc();

                if (!is_null($checkMembers['member1ID']) && !is_null($checkMembers['member2ID'])) {
                    // Delete pending and rejected invitations for this thesis
                    $con->query("DELETE FROM Invitations WHERE thesisID = $thesisID AND (status = 'pending' OR status = 'rejected')");
                }
            }
        } else {
            $errorMessage = "Error updating invitation: " . $con->error;
        }

        // Refresh invitations
        $invitations = $con->query("
            SELECT i.invitationID, t.thesisID, t.title AS thesis_title, CONCAT(s.Name, ' ', s.Surname) AS student_name, i.status, i.sentDate 
            FROM Invitations i
            JOIN Thesis t ON i.thesisID = t.thesisID
            JOIN Students s ON i.studentID = s.Student_ID
            WHERE i.professorID = $professorID
            ORDER BY i.sentDate DESC
        ");
    } else {
        $errorMessage = "Invalid form submission.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Setting the pages character encoding -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitations</title>
    <link rel="stylesheet" href="lobby.css">
</head>
<body>
    <div class="container">
        <!-- Go back button -->
        <button class="go-back" onclick="window.location.href = 'professor.php';">Go Back</button>

        <!-- Page heading -->
        <h1>Your Invitations</h1>

        <!-- Success/Error Messages -->
        <?php if (!empty($successMessage)): ?>
            <p class="success"><?php echo htmlspecialchars($successMessage); ?></p>
        <?php elseif (!empty($errorMessage)): ?>
            <p class="error"><?php echo htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>

        <!-- Invitations Table -->
        <table>
            <thead>
                <tr>
                    <th>Thesis Title</th>
                    <th>Student Name</th>
                    <th>Status</th>
                    <th>Sent Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $invitations->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['thesis_title']) ?></td>
                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                        <td><?= htmlspecialchars(ucfirst($row['status'])) ?></td>
                        <td><?= htmlspecialchars($row['sentDate']) ?></td>
                        <td>
                            <?php if ($row['status'] === 'pending'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="invitation_id" value="<?= $row['invitationID'] ?>">
                                    <input type="hidden" name="thesis_id" value="<?= $row['thesisID'] ?>">
                                    <input type="hidden" name="action" value="accept">
                                    <button type="submit">Accept</button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="invitation_id" value="<?= $row['invitationID'] ?>">
                                    <input type="hidden" name="thesis_id" value="<?= $row['thesisID'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit">Reject</button>
                                </form>
                            <?php else: ?>
                                <span><?= ucfirst($row['status']) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>