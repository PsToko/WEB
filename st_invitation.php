<?php
// st_invitation.php
include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has student privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'students') {
    header("Location: login.php?block=1");
    exit();
}

// Get dynamic student ID from session
$studentID = $_SESSION['user_id'];

// Fetch thesis information for the logged-in student
$thesisQuery = "SELECT thesisID, title, status FROM Thesis WHERE studentID = ?";
$thesisStmt = $con->prepare($thesisQuery);
$thesisStmt->bind_param('i', $studentID);
$thesisStmt->execute();
$thesisResult = $thesisStmt->get_result();
$thesis = $thesisResult->fetch_assoc();

// Fetch professors who are not already invited or assigned to the thesis
$professors = [];
if ($thesis && $thesis['status'] === 'under assignment') {
    $professorsQuery = "
        SELECT Professor_ID, CONCAT(Name, ' ', Surname) AS fullname 
        FROM Professors p
        WHERE NOT EXISTS (
            SELECT 1 
            FROM Invitations i
            WHERE i.professorID = p.Professor_ID 
              AND i.thesisID = ?
        )
        AND NOT EXISTS (
            SELECT 1 
            FROM Thesis t
            WHERE t.studentID = ?
              AND (t.supervisorID = p.Professor_ID OR t.member1ID = p.Professor_ID OR t.member2ID = p.Professor_ID)
        )";
    $professorsStmt = $con->prepare($professorsQuery);
    $professorsStmt->bind_param('ii', $thesis['thesisID'], $studentID);
    $professorsStmt->execute();
    $professors = $professorsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Handle form submission to send an invitation
$successMessage = $errorMessage = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['professor_id'], $thesis['thesisID'])) {
    $professorID = $_POST['professor_id'];
    $thesisID = $thesis['thesisID'];

    // Insert the invitation
    $insertQuery = "INSERT INTO Invitations (thesisID, studentID, professorID, status, sentDate) 
                    VALUES (?, ?, ?, 'pending', NOW())";
    $insertStmt = $con->prepare($insertQuery);
    $insertStmt->bind_param('iii', $thesisID, $studentID, $professorID);

    if ($insertStmt->execute()) {
        $successMessage = "Invitation sent successfully!";
        // Refresh the professors list
        header("Location: st_invitation.php");
        exit();
    } else {
        $errorMessage = "Error sending invitation: " . $con->error;
    }
}

// Fetch invitation history for the logged-in student
$invitationsQuery = "
    SELECT i.invitationID, t.title AS thesisTitle, CONCAT(p.Name, ' ', p.Surname) AS professorName, i.status, i.sentDate, i.responseDate
    FROM Invitations i
    INNER JOIN Thesis t ON i.thesisID = t.thesisID
    INNER JOIN Professors p ON i.professorID = p.Professor_ID
    WHERE i.studentID = ?
    ORDER BY i.sentDate DESC";
$invitationsStmt = $con->prepare($invitationsQuery);
$invitationsStmt->bind_param('i', $studentID);
$invitationsStmt->execute();
$invitations = $invitationsStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invite Professors</title>
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

        .static-info {
            font-size: 1.2em;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Go back button -->
        <button class="go-back" onclick="window.location.href = 'student.php';">Go Back</button>

        <!-- Page heading -->
        <h1>Invite Professors to Your Thesis Committee</h1>

        <!-- Success/Error Messages -->
        <?php if (!empty($successMessage)): ?>
            <p class="success"><?= htmlspecialchars($successMessage) ?></p>
        <?php elseif (!empty($errorMessage)): ?>
            <p class="error"><?= htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>

        <!-- Thesis Information -->
        <?php if ($thesis): ?>
            <div class="static-info">
                <strong>Your Thesis:</strong> <?= htmlspecialchars($thesis['title']) ?> (Status: <?= htmlspecialchars($thesis['status']) ?>)
            </div>
        <?php else: ?>
            <div class="static-info">
                <strong>You do not have an assigned thesis.</strong>
            </div>
        <?php endif; ?>

        <!-- Invitation Form -->
        <?php if ($thesis && $thesis['status'] === 'under assignment'): ?>
            <form id="invitationForm" method="POST" action="">
                <label for="professor_id">Select a Professor:</label>
                <select id="professor_id" name="professor_id" required>
                    <option value="">-- Select Professor --</option>
                    <?php foreach ($professors as $professor): ?>
                        <option value="<?= $professor['Professor_ID'] ?>"><?= htmlspecialchars($professor['fullname']) ?></option>
                    <?php endforeach; ?>
                </select><br><br>

                <button type="submit">Send Invitation</button>
            </form>
        <?php endif; ?>

        <!-- Invitations History Table -->
        <h2>Invitation History</h2>
        <table>
            <thead>
                <tr>
                    <th>Thesis Title</th>
                    <th>Professor Name</th>
                    <th>Status</th>
                    <th>Sent Date</th>
                    <th>Response Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($invitations->num_rows > 0): ?>
                    <?php while ($row = $invitations->fetch_assoc()): ?>
                        <?php if ($thesis && $thesis['status'] !== 'under assignment' && $row['status'] !== 'accepted') continue; ?>
                        <tr>
                            <td><?= htmlspecialchars($row['thesisTitle']) ?></td>
                            <td><?= htmlspecialchars($row['professorName']) ?></td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                            <td><?= htmlspecialchars($row['sentDate']) ?></td>
                            <td><?= htmlspecialchars($row['responseDate'] ?? 'N/A') ?></td>
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
