<?php
// st_invitation.php
include 'access.php';

// Ξεκινήστε τη συνεδρία
session_start();

// Ελέγξτε αν ο χρήστης είναι συνδεδεμένος και έχει δικαιώματα φοιτητή
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'students') {
    header("Location: login.php?block=1");
    exit();
}

// Λήψη του δυναμικού ID φοιτητή από τη συνεδρία
$studentID = $_SESSION['user_id'];

// Λήψη πληροφοριών διπλωματικής για τον συνδεδεμένο φοιτητή
$thesisQuery = "SELECT thesisID, title, status FROM Thesis WHERE studentID = ? AND status = 'under assignment'";
$thesisStmt = $con->prepare($thesisQuery);
$thesisStmt->bind_param('i', $studentID);
$thesisStmt->execute();
$thesisResult = $thesisStmt->get_result();
$thesis = $thesisResult->fetch_assoc();

// Λήψη καθηγητών που δεν έχουν προσκληθεί ή ανατεθεί ήδη στη διπλωματική
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
            WHERE t.studentID = ? AND status = 'under assignment'
              AND (t.supervisorID = p.Professor_ID OR t.member1ID = p.Professor_ID OR t.member2ID = p.Professor_ID)
        )";
    $professorsStmt = $con->prepare($professorsQuery);
    $professorsStmt->bind_param('ii', $thesis['thesisID'], $studentID);
    $professorsStmt->execute();
    $professors = $professorsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Διαχείριση υποβολής φόρμας για αποστολή πρόσκλησης
$successMessage = $errorMessage = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['professor_id'], $thesis['thesisID'])) {
    $professorID = $_POST['professor_id'];
    $thesisID = $thesis['thesisID'];

    // Εισαγωγή της πρόσκλησης
    $insertQuery = "INSERT INTO Invitations (thesisID, studentID, professorID, status, sentDate) 
                    VALUES (?, ?, ?, 'pending', NOW())";
    $insertStmt = $con->prepare($insertQuery);
    $insertStmt->bind_param('iii', $thesisID, $studentID, $professorID);

    if ($insertStmt->execute()) {
        $successMessage = "Η πρόσκληση στάλθηκε με επιτυχία!";
        // Ανανεώστε τη λίστα καθηγητών
        header("Location: st_invitation.php");
        exit();
    } else {
        $errorMessage = "Σφάλμα στην αποστολή της πρόσκλησης: " . $con->error;
    }
}

// Λήψη ιστορικού προσκλήσεων για τον συνδεδεμένο φοιτητή
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

// Include the global menu
include 'menus/menu.php';

?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Πρόσκληση Καθηγητών</title>
    <!--<link rel="stylesheet" href="lobby.css">-->
    <link rel="stylesheet" href="AllCss.css">

</head>
<body>
    <div class="container">

        <!-- Τίτλος σελίδας -->
        <h1>Προσκλήσεις καθηγητών για την επιτροπή της διπλωματικής σας </h1>

        <!-- Μηνύματα Επιτυχίας/Σφάλματος -->
        <?php if (!empty($successMessage)): ?>
            <p class="success"><?= htmlspecialchars($successMessage) ?></p>
        <?php elseif (!empty($errorMessage)): ?>
            <p class="error"><?= htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>

        <!-- Πληροφορίες Διπλωματικής -->
        <?php if ($thesis): ?>
            <div class="static-info">
                <strong>Η Διπλωματική Σας:</strong> <?= htmlspecialchars($thesis['title']) ?> (Κατάσταση: <?= htmlspecialchars($thesis['status']) ?>)
            </div>
        <?php else: ?>
            <div class="static-info">
                <strong>Δεν σας έχει ανατεθεί διπλωματική εργασία.</strong>
            </div>
        <?php endif; ?>

        <!-- Φόρμα Πρόσκλησης -->
        <?php if ($thesis && $thesis['status'] === 'under assignment'): ?>
            <form id="invitationForm" method="POST" action="">
                <label for="professor_id">Επιλέξτε Καθηγητή:</label>
                <select id="professor_id" name="professor_id" required>
                    <option value="">-- Επιλέξτε Καθηγητή --</option>
                    <?php foreach ($professors as $professor): ?>
                        <option value="<?= $professor['Professor_ID'] ?>"><?= htmlspecialchars($professor['fullname']) ?></option>
                    <?php endforeach; ?>
                </select><br><br>
                <button type="submit">Αποστολή Πρόσκλησης</button>
            </form>
        <?php endif; ?>

        <!-- Ιστορικό Προσκλήσεων -->
        <h2>Ιστορικό Προσκλήσεων</h2>
        <table>
            <thead>
                <tr>
                    <th>Τίτλος Διπλωματικής</th>
                    <th>Όνομα Καθηγητή</th>
                    <th>Κατάσταση</th>
                    <th>Ημερομηνία Αποστολής</th>
                    <th>Ημερομηνία Απάντησης</th>
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
                            <td><?= htmlspecialchars($row['responseDate'] ?? 'Μ/Δ') ?></td>
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
