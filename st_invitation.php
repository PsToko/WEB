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

// Fetch theses for the logged-in student
$thesesQuery = "SELECT thesisID, title FROM Thesis WHERE studentID = ? AND status = 'under assignment'";
$thesesStmt = $con->prepare($thesesQuery);
$thesesStmt->bind_param('i', $studentID);
$thesesStmt->execute();
$thesesResult = $thesesStmt->get_result();

// Fetch professors who are not already invited or assigned to the thesis
$professorsQuery = "
    SELECT Professor_ID, CONCAT(Name, ' ', Surname) AS fullname 
    FROM Professors p
    WHERE NOT EXISTS (
        SELECT 1 
        FROM Invitations i
        WHERE i.professorID = p.Professor_ID 
          AND i.thesisID IN (SELECT thesisID FROM Thesis WHERE studentID = ?)
    )
    AND NOT EXISTS (
        SELECT 1 
        FROM Thesis t
        WHERE t.studentID = ?
          AND (t.supervisorID = p.Professor_ID OR t.member1ID = p.Professor_ID OR t.member2ID = p.Professor_ID) AND t.status = 'under assignment'
    )";
$professorsStmt = $con->prepare($professorsQuery);
$professorsStmt->bind_param('ii', $studentID, $studentID);
$professorsStmt->execute();
$professorsResult = $professorsStmt->get_result();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $thesisID = $_POST['thesis_id'];
    $professorID = $_POST['professor_id'];

    // Insert the invitation
    $sql = "INSERT INTO Invitations (thesisID, studentID, professorID, status, sentDate) 
            VALUES (?, ?, ?, 'pending', NOW())";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('iii', $thesisID, $studentID, $professorID);

    if ($stmt->execute()) {
        $successMessage = "Invitation sent successfully!";
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
$invitationsResult = $invitationsStmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invite Professors</title>
    <link rel="stylesheet" href="lobby.css">
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

        <!-- Invitation Form -->
        <form method="POST">
            <label for="thesis_id">Select Your Thesis:</label>
            <select id="thesis_id" name="thesis_id" required>
                <option value="">-- Select Thesis --</option>
                <?php while ($row = $thesesResult->fetch_assoc()): ?>
                    <option value="<?= $row['thesisID'] ?>"><?= htmlspecialchars($row['title']) ?></option>
                <?php endwhile; ?>
            </select><br><br>

            <label for="professor_id">Select a Professor:</label>
            <select id="professor_id" name="professor_id" required>
                <option value="">-- Select Professor --</option>
                <?php while ($row = $professorsResult->fetch_assoc()): ?>
                    <option value="<?= $row['Professor_ID'] ?>"><?= htmlspecialchars($row['fullname']) ?></option>
                <?php endwhile; ?>
            </select><br><br>

            <button type="submit">Send Invitation</button>
        </form>

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
                <?php while ($row = $invitationsResult->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['thesisTitle']) ?></td>
                        <td><?= htmlspecialchars($row['professorName']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td><?= htmlspecialchars($row['sentDate']) ?></td>
                        <td><?= htmlspecialchars($row['responseDate'] ?? 'N/A') ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>