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

// Initialize success and error messages
$successMessage = $errorMessage = "";

// Handle accept/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invitation_id'], $_POST['thesis_id'], $_POST['action'])) {
    $invitationID = $_POST['invitation_id'];
    $thesisID = $_POST['thesis_id'];
    $action = $_POST['action']; // 'accept' or 'reject'

    $newStatus = $action === 'accept' ? 'accepted' : 'rejected';

    // Update the invitation status
    $updateInvitationQuery = "UPDATE invitations SET status = ?, responseDate = NOW() WHERE invitationID = ?";
    $stmt = $con->prepare($updateInvitationQuery);
    $stmt->bind_param('si', $newStatus, $invitationID);

    if ($stmt->execute()) {
        $successMessage = "Invitation has been $newStatus successfully!";

        if ($action === 'accept') {
            // Fetch the current member IDs
            $checkMembersQuery = "SELECT member1ID, member2ID FROM Thesis WHERE thesisID = ?";
            $checkStmt = $con->prepare($checkMembersQuery);
            $checkStmt->bind_param('i', $thesisID);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result()->fetch_assoc();

            $member1ID = $checkResult['member1ID'];
            $member2ID = $checkResult['member2ID'];

            // Update only the appropriate member ID
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

            // Check again if both member IDs are filled
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result()->fetch_assoc();

            if (!is_null($checkResult['member1ID']) && !is_null($checkResult['member2ID'])) {
                // Update thesis status to active and set assignmentDate
                $activateThesisQuery = "UPDATE Thesis SET status = 'active', assignmentDate = CURDATE() WHERE thesisID = ?";
                $activateStmt = $con->prepare($activateThesisQuery);
                $activateStmt->bind_param('i', $thesisID);
                $activateStmt->execute();

                // Delete all pending and rejected invitations for this thesis
                $deleteInvitationsQuery = "DELETE FROM Invitations WHERE thesisID = ? AND (status = 'pending' OR status = 'rejected')";
                $deleteStmt = $con->prepare($deleteInvitationsQuery);
                $deleteStmt->bind_param('i', $thesisID);
                $deleteStmt->execute();
            }
        }
    } else {
        $errorMessage = "Error updating invitation: " . $stmt->error;
    }
}

// Fetch invitations for the logged-in professor
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitations</title>
    <link rel="stylesheet" href="lobby.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .success {
            color: green;
            font-weight: bold;
        }

        .error {
            color: red;
            font-weight: bold;
        }

        .action-buttons form {
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Go back button -->
        <button class="go-back" onclick="window.location.href = 'professor.php';">Go Back</button>

        <!-- Page heading -->
        <h1>Your Invitations</h1>

        <!-- Success/Error Messages -->
        <?php if (!empty($successMessage)): ?>
            <p class="success"><?= htmlspecialchars($successMessage) ?></p>
        <?php elseif (!empty($errorMessage)): ?>
            <p class="error"><?= htmlspecialchars($errorMessage); ?></p>
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
                                        <button type="submit">Accept</button>
                                    </form>
                                    <form method="POST" action="">
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
                <?php else: ?>
                    <tr>
                        <td colspan="5">No invitations found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>