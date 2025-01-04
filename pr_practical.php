<?php
include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has professor privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professors') {
    header("Location: login.php?block=1");
    exit();
}

// Ανάκτηση διπλωματικών εργασιών για τον καθηγητή
$sql = "
    SELECT e.examinationID, t.title, s.Name AS StudentName, s.Surname AS StudentSurname
    FROM examination e
    LEFT JOIN thesis t ON e.thesisID = t.thesisID
    LEFT JOIN students s ON e.StudentID = s.Student_ID
    WHERE e.supervisorID = ? OR e.member1ID = ? OR e.member2ID = ?
";

$stmt = $con->prepare($sql);
$stmt->bind_param("iii", $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

// Ανάκτηση επιλεγμένων δεδομένων εξέτασης αν υπάρχει επιλογή
$examinationID = isset($_POST['examinationID']) ? intval($_POST['examinationID']) : null;

$examinationData = null;
$averageGrade = null;
if ($examinationID) {
    $sql = "
        SELECT e.*, t.*,
               s.Name AS StudentName, s.Surname AS StudentSurname,
               p1.Name AS SupervisorName, p1.Surname AS SupervisorSurname,
               p2.Name AS Member1Name, p2.Surname AS Member1Surname,
               p3.Name AS Member2Name, p3.Surname AS Member2Surname
        FROM examination e
        LEFT JOIN students s ON e.StudentID = s.Student_ID
        LEFT JOIN professors p1 ON e.supervisorID = p1.Professor_ID
        LEFT JOIN professors p2 ON e.member1ID = p2.Professor_ID
        LEFT JOIN professors p3 ON e.member2ID = p3.Professor_ID
        LEFT JOIN thesis t ON e.thesisID = t.thesisID
        WHERE e.examinationID = ?
    ";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $examinationID);
    $stmt->execute();
    $result = $stmt->get_result();
    $examinationData = $result->fetch_assoc();

    if ($examinationData) {
        // Υπολογισμός μέσου όρου βαθμολογίας
        if (!is_null($examinationData['finalGrade']) && !is_null($examinationData['member1Grade']) && !is_null($examinationData['member2Grade'])) {
            $averageGrade = round(($examinationData['finalGrade'] + $examinationData['member1Grade'] + $examinationData['member2Grade']) / 3, 2);
        }
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
    <link rel="stylesheet" href="AllCss.css">
    <title>Επιλογή Διπλωματικής</title>
</head>
<body>
<div class="container">
    <h1>Επιλογή Διπλωματικής Εργασίας</h1>

    <form method="POST">
        <label for="examinationID">Επιλέξτε διπλωματική:</label>
        <select name="examinationID" id="examinationID" onchange="this.form.submit()">
            <option value="">-- Επιλέξτε --</option>
            <?php foreach ($data as $thesis): ?>
                <option value="<?= htmlspecialchars($thesis['examinationID']) ?>" <?= $examinationID == $thesis['examinationID'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($thesis['title'] . ' - ' . $thesis['StudentName'] . ' ' . $thesis['StudentSurname']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($examinationData): ?>
        <?php if (is_null($averageGrade)): ?>
            <p class="message">Η βαθμολογία δεν έχει ολοκληρωθεί ακόμα. Παρακαλώ ελέγξτε αργότερα.</p>
        <?php else: ?>
            <div class="container">
        <h1>ΠΡΑΚΤΙΚΟ ΕΞΕΤΑΣΗΣ</h1>
        <p class="section">ΤΗΣ ΤΡΙΜΕΛΟΥΣ ΕΠΙΤΡΟΠΗΣ ΓΙΑ ΤΗΝ ΠΑΡΟΥΣΙΑΣΗ ΚΑΙ ΚΡΙΣΗ ΤΗΣ ΔΙΠΛΩΜΑΤΙΚΗΣ ΕΡΓΑΣΙΑΣ</p>

        <p class="section">του/της φοιτητή/φοιτήτριας κ. <span class="highlight"><?= htmlspecialchars($examinationData['StudentName'] . ' ' . $examinationData['StudentSurname']) ?></span></p>

        <p class="section">Η συνεδρίαση πραγματοποιήθηκε στην αίθουσα <span class="highlight"><?= htmlspecialchars($examinationData['location']) ?></span>, στις <span class="highlight"><?= htmlspecialchars($examinationData['examinationDate']) ?></span>.</p>

        <p class="section">Στην συνεδρίαση είναι παρόντα τα μέλη της Τριμελούς Επιτροπής, κ.κ.:</p>
        <ol>
            <li><span class="highlight"><?= htmlspecialchars($examinationData['SupervisorName'] . ' ' . $examinationData['SupervisorSurname']) ?></span></li>
            <li><span class="highlight"><?= htmlspecialchars($examinationData['Member1Name'] . ' ' . $examinationData['Member1Surname']) ?></span></li>
            <li><span class="highlight"><?= htmlspecialchars($examinationData['Member2Name'] . ' ' . $examinationData['Member2Surname']) ?></span></li>
        </ol>
        <p class="section">οι οποίοι ορίσθηκαν από την Συνέλευση του ΤΜΗΥΠ, στην συνεδρίαση της με αριθμό
        <span class="highlight"><?= htmlspecialchars(string: $examinationData['general_assembly']) ?></span> </p>

        <p class="section">Ο/Η φοιτητής/φοιτήτρια κ. <span class="highlight"><?= htmlspecialchars($examinationData['StudentName'] . ' ' . $examinationData['StudentSurname']) ?></span> ανέπτυξε το θέμα της Διπλωματικής του/της Εργασίας, με τίτλο:</p>
        <p class="section"><span class="highlight"><?= htmlspecialchars($examinationData['title']) ?></span></p>

        <p class="section">Στην συνέχεια υποβλήθηκαν ερωτήσεις στον υποψήφιο από τα μέλη της Τριμελούς Επιτροπής και τους άλλους παρευρισκόμενους, προκειμένου να διαμορφώσουν σαφή άποψη για το περιεχόμενο της εργασίας, για την επιστημονική συγκρότηση του μεταπτυχιακού φοιτητή.</p>

        <p class="section">Μετά το τέλος της ανάπτυξης της εργασίας του και των ερωτήσεων, ο υποψήφιος αποχωρεί.</p>

        <p class="section">Ο Επιβλέπων καθηγητής κ. <span class="highlight"><?= htmlspecialchars($examinationData['SupervisorName'] . ' ' . $examinationData['SupervisorSurname']) ?></span>, προτείνει στα μέλη της Τριμελούς Επιτροπής, να ψηφίσουν για το αν εγκρίνεται η διπλωματική εργασία του <span class="highlight"><?= htmlspecialchars($examinationData['StudentName'] . ' ' . $examinationData['StudentSurname']) ?></span>.</p>

        <p class="section">Τα μέλη της Τριμελούς Επιτροπής που ψηφίζουν:</p>
        <ol>
            <li><span class="highlight"><?= htmlspecialchars($examinationData['Member1Name'] . ' ' . $examinationData['Member1Surname']) ?></span></li>
            <li><span class="highlight"><?= htmlspecialchars($examinationData['Member2Name'] . ' ' . $examinationData['Member2Surname']) ?></span></li>
            <li><span class="highlight"><?= htmlspecialchars($examinationData['SupervisorName'] . ' ' . $examinationData['SupervisorSurname']) ?></span></li>
        </ol>

        <p class="section">υπέρ της εγκρίσεως της Διπλωματικής Εργασίας του φοιτητή <span class="highlight"><?= htmlspecialchars($examinationData['StudentName'] . ' ' . $examinationData['StudentSurname']) ?></span>, επειδή θεωρούν επιστημονικά επαρκή και το περιεχόμενό της ανταποκρίνεται στο θέμα που του δόθηκε.</p>

        <p class="section">Μετά της έγκριση, ο εισηγητής κ. <span class="highlight"><?= htmlspecialchars($examinationData['SupervisorName'] . ' ' . $examinationData['SupervisorSurname']) ?></span>, προτείνει στα μέλη της Τριμελούς Επιτροπής, να απονεμηθεί στο/στη φοιτητή/τρια κ. <span class="highlight"><?= htmlspecialchars($examinationData['StudentName'] . ' ' . $examinationData['StudentSurname']) ?></span> ο βαθμός <span class="highlight"><?= htmlspecialchars($averageGrade) ?></span>.</p>

        <p class="section">Τα μέλη της Τριμελούς Επιτροπής, απομένουν την παρακάτω βαθμολογία:</p>

        <table>
            <thead>
                <tr>
                    <th>Ονοματεπώνυμο</th>
                    <th>Ιδιότητα</th>
                    <th>Βαθμολογία</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars($examinationData['SupervisorName'] . ' ' . $examinationData['SupervisorSurname']) ?></td>
                    <td>Επιβλέπων</td>
                    <td><?= htmlspecialchars($examinationData['finalGrade']) ?></td>
                </tr>
                <tr>
                    <td><?= htmlspecialchars($examinationData['Member1Name'] . ' ' . $examinationData['Member1Surname']) ?></td>
                    <td>Μέλος 1</td>
                    <td><?= htmlspecialchars($examinationData['member1Grade']) ?></td>
                </tr>
                <tr>
                    <td><?= htmlspecialchars($examinationData['Member2Name'] . ' ' . $examinationData['Member2Surname']) ?></td>
                    <td>Μέλος 2</td>
                    <td><?= htmlspecialchars($examinationData['member2Grade']) ?></td>
                </tr>
            </tbody>
        </table>

        <p class="section">Μετά την έγκριση και την απονομή του βαθμού <span class="highlight"><?= htmlspecialchars($averageGrade) ?></span>, η Τριμελής Επιτροπή, προτείνει να προχωρήσει στην διαδικασία για να ανακηρύξει τον κ. <span class="highlight"><?= htmlspecialchars($examinationData['StudentName'] . ' ' . $examinationData['StudentSurname']) ?></span>, σε διπλωματούχο.</p>
    </div>
        <?php endif; ?>
    <?php elseif ($examinationID): ?>
        <p class="message">Δεν βρέθηκαν δεδομένα για την επιλεγμένη διπλωματική εργασία.</p>
    <?php endif; ?>
    
</div>
</body>
</html>
