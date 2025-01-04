<?php
// st_dipl.php
include 'access.php';

// Ξεκινήστε τη συνεδρία
session_start();

// Ελέγξτε αν ο χρήστης είναι συνδεδεμένος και έχει δικαιώματα φοιτητή
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'students') {
    header("Location: login.php?block=1");
    exit();
}

// Λάβετε το δυναμικό ID φοιτητή από τη συνεδρία
$studentID = $_SESSION['user_id'];

// Ανάκτηση λεπτομερειών θέματος διπλωματικής για τον συνδεδεμένο φοιτητή
$query = "
    SELECT 
        t.completionDate,
        t.examinationDate,
        t.thesisID, 
        t.title, 
        t.description, 
        t.pdf, 
        t.status, 
        t.assignmentDate, 
        p1.Name AS supervisorName, 
        p1.Surname AS supervisorSurname, 
        p2.Name AS member1Name, 
        p2.Surname AS member1Surname, 
        p3.Name AS member2Name, 
        p3.Surname AS member2Surname 
    FROM Thesis t
    LEFT JOIN Professors p1 ON t.supervisorID = p1.Professor_ID
    LEFT JOIN Professors p2 ON t.member1ID = p2.Professor_ID
    LEFT JOIN Professors p3 ON t.member2ID = p3.Professor_ID
    WHERE t.studentID = ? AND t.status != 'withdrawn'
";

$stmt = $con->prepare($query);
$stmt->bind_param('i', $studentID);
$stmt->execute();
$result = $stmt->get_result();

// Ελέγξτε αν ο φοιτητής έχει ανατεθεί ένα θέμα
$thesis = $result->fetch_assoc();

// Ανάκτηση λεπτομερειών εξέτασης για το θέμα διπλωματικής του φοιτητή
$examination = null;
if ($thesis) {
    $examinationQuery = "
        SELECT examinationDate, examinationMethod, location 
        FROM Examination 
        WHERE thesisID = ?";
    $examStmt = $con->prepare($examinationQuery);
    $examStmt->bind_param('i', $thesis['thesisID']);
    $examStmt->execute();
    $examination = $examStmt->get_result()->fetch_assoc();
}

// Ενημέρωση εξέτασης
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $thesis && $thesis['status'] === 'under review') {
    $examinationDate = $_POST['examinationDate'];
    $examinationMethod = $_POST['examinationMethod'];
    $location = $_POST['location'];

    // Ενημέρωση στον πίνακα Examination
    $updateExamQuery = "
        UPDATE Examination 
        SET examinationDate = ?, examinationMethod = ?, location = ? 
        WHERE thesisID = ?";
    $updateExamStmt = $con->prepare($updateExamQuery);
    $updateExamStmt->bind_param('sssi', $examinationDate, $examinationMethod, $location, $thesis['thesisID']);
    $updateExamStmt->execute();

    // Συγχρονισμός της ημερομηνίας εξέτασης στον πίνακα Thesis
    $updateThesisQuery = "UPDATE Thesis SET examinationDate = ? WHERE thesisID = ?";
    $updateThesisStmt = $con->prepare($updateThesisQuery);
    $updateThesisStmt->bind_param('si', $examinationDate, $thesis['thesisID']);
    $updateThesisStmt->execute();

    // Ανανέωση της σελίδας για να εμφανιστούν οι ενημερωμένες πληροφορίες
    header("Location: st_dipl.php");
    exit();
}

// Include the global menu
include 'menus/menu.php';

?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Πληροφορίες Θέματος Διπλωματικής</title>
    <!--<link rel="stylesheet" href="lobby.css">-->
    <link rel="stylesheet" href="AllCss.css">
</head>
<body>
    <div class="container">
        
        <h1>Πληροφορίες Θέματος Διπλωματικής</h1>

        <?php if ($thesis): ?>
            <table class="thesis-table">
                <tr>
                    <th>Θέμα</th>
                    <td><?= htmlspecialchars($thesis['title']) ?></td>
                </tr>
                <tr>
                    <th>Σύνοψη</th>
                    <td><?= nl2br(htmlspecialchars($thesis['description'])) ?></td>
                </tr>
                <tr>
                    <th>PDF</th>
                    <td><?= $thesis['pdf'] ? "<a href='uploads/{$thesis['pdf']}' target='_blank'>Λήψη</a>" : "Δεν έχει ανέβει PDF" ?></td>
                </tr>
                <tr>
                <tr>
    <th>Κατάσταση</th>
    <td><?= htmlspecialchars($thesis['status']) ?></td>
</tr>
<tr>
    <th>Επιβλέπων</th>
    <td><?= htmlspecialchars($thesis['supervisorName'] . " " . $thesis['supervisorSurname']) ?></td>
</tr>
<tr>
    <th>Μέλος Επιτροπής 1</th>
    <td><?= $thesis['member1Name'] ? htmlspecialchars($thesis['member1Name'] . " " . $thesis['member1Surname']) : "Κενό" ?></td>
</tr>
<tr>
    <th>Μέλος Επιτροπής 2</th>
    <td><?= $thesis['member2Name'] ? htmlspecialchars($thesis['member2Name'] . " " . $thesis['member2Surname']) : "Κενό" ?></td>
</tr>
<tr>
<?php if ($thesis['status'] != 'finalized'): ?>
    <th>Χρόνος από την Ανάθεση</th>
    <td>
        <?php
        if ($thesis['assignmentDate']) {
            $now = new DateTime();
            $assignmentDate = new DateTime($thesis['assignmentDate']);
            $interval = $now->diff($assignmentDate);
            echo $interval->format('%y χρόνια, %m μήνες, και %d ημέρες');
        } else {
            echo "Δεν έχει ανατεθεί ακόμη";
        }
        ?>
    </td>
<?php endif; ?>
</tr>
<?php if ($thesis['status'] === 'finalized'): ?>
    <tr>
        <th>Ημερομηνία Ανάθεσης</th>
        <td><?= $thesis['assignmentDate'] ? htmlspecialchars($thesis['assignmentDate']) : "Μη διαθέσιμη" ?></td>
    </tr>
    <tr>
        <th>Ημερομηνία Ολοκλήρωσης</th>
        <td><?= $thesis['completionDate'] ? htmlspecialchars($thesis['completionDate']) : "Μη διαθέσιμη" ?></td>
    </tr>
    <tr>
        <th>Ημερομηνία Εξέτασης</th>
        <td><?= $thesis['examinationDate'] ? htmlspecialchars($thesis['examinationDate']) : "Μη διαθέσιμη" ?></td>
    </tr>
<?php endif; ?>
</table>
<?php if ($thesis['status'] === 'finalized'): ?>
    <button class="add-topic-button" onclick="window.location.href = 'practical.php';">Πρακτικό εξέτασης</button>
<?php endif; ?>
<!-- Πληροφορίες Εξέτασης -->
<?php if ($thesis['status'] === 'under review'): ?>
    <h1>Πληροφορίες Εξέτασης</h1>
    <form method="POST" action="">
        <table class="examination-table">
            <tr>
                <th>Ημερομηνία Εξέτασης</th>
                <td>
                    <input type="datetime-local" name="examinationDate" 
                        value="<?= $examination ? htmlspecialchars($examination['examinationDate']) : '' ?>" 
                        required>
                </td>
            </tr>
            <tr>
                <th>Μέθοδος Εξέτασης</th>
                <td>
                    <select name="examinationMethod" required>
                        <option value="online" <?= $examination && $examination['examinationMethod'] === 'online' ? 'selected' : '' ?>>Online</option>
                        <option value="in person" <?= $examination && $examination['examinationMethod'] === 'in person' ? 'selected' : '' ?>>Διά Ζώσης</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Τοποθεσία</th>
                <td>
                    <input type="text" name="location" 
                        value="<?= $examination ? htmlspecialchars($examination['location']) : '' ?>" 
                        required>
                </td>
            </tr>
        </table>

        <button type="submit">Ενημέρωση Πληροφοριών Εξέτασης</button>
    </form>
<?php endif; ?>
<?php else: ?>
    <p class="no-thesis">Δεν έχετε ανατεθεί διπλωματική αυτή τη στιγμή.</p>
<?php endif; ?>
</div>

    <script src="menus/menu.js" defer></script>

</body>
</html>
