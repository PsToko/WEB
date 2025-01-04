<?php
include 'access.php';
session_start();

// Έλεγχος αν ο χρήστης είναι συνδεδεμένος και έχει δικαιώματα καθηγητή
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professors') {
    header("Location: login.php?block=1");
    exit();
}

$successMessage = '';
$errorMessage = '';

// Διαχείριση υποβολής φόρμας για δημιουργία ανακοίνωσης
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['thesisID'])) {
    $thesisID = $_POST['thesisID'];

    // Ανάκτηση πληροφοριών διπλωματικής και εξέτασης
    $query = "
        SELECT 
            t.title AS thesisTitle, 
            e.examinationDate, 
            e.examinationMethod, 
            e.location
        FROM Thesis t
        INNER JOIN Examination e ON t.thesisID = e.thesisID
        WHERE t.thesisID = ? AND t.supervisorID = ?
    ";
    $stmt = $con->prepare($query);
    $stmt->bind_param('ii', $thesisID, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        $announcementText = sprintf(
            "Η εξέταση για τη διπλωματική με τίτλο \"%s\" θα πραγματοποιηθεί στις %s. Η μέθοδος εξέτασης είναι %s και θα γίνει στο %s.",
            $result['thesisTitle'],
            $result['examinationDate'],
            $result['examinationMethod'],
            $result['location']
        );

        // Εισαγωγή ανακοίνωσης στη βάση δεδομένων
        $insertQuery = "
            INSERT INTO Announcements (thesisID, createdBy, announcementText, examinationDate, examinationMethod, location)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        $insertStmt = $con->prepare($insertQuery);
        $insertStmt->bind_param(
            'iissss',
            $thesisID,
            $_SESSION['user_id'],
            $announcementText,
            $result['examinationDate'],
            $result['examinationMethod'],
            $result['location']
        );

        if ($insertStmt->execute()) {
            $successMessage = "Η ανακοίνωση δημιουργήθηκε με επιτυχία!";
            // Ανακατεύθυνση για αποφυγή διπλής υποβολής
            header("Location: announcements.php?success=1");
            exit();
        } else {
            $errorMessage = "Σφάλμα κατά τη δημιουργία της ανακοίνωσης: " . $insertStmt->error;
        }
    } else {
        $errorMessage = "Οι πληροφορίες διπλωματικής ή εξέτασης είναι ελλιπείς.";
    }
}

// Έλεγχος για μήνυμα επιτυχίας μετά από ανακατεύθυνση
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $successMessage = "Η ανακοίνωση δημιουργήθηκε με επιτυχία!";
}

// Ανάκτηση διπλωματικών με πλήρεις πληροφορίες εξέτασης χωρίς υπάρχουσες ανακοινώσεις
$query = "
    SELECT 
        t.thesisID, 
        t.title, 
        e.examinationDate, 
        e.examinationMethod, 
        e.location 
    FROM Thesis t
    INNER JOIN Examination e ON t.thesisID = e.thesisID
    WHERE t.supervisorID = ? 
    AND e.examinationDate IS NOT NULL 
    AND e.examinationMethod IS NOT NULL 
    AND e.location IS NOT NULL
    AND t.thesisID NOT IN (SELECT thesisID FROM Announcements)
";
$stmt = $con->prepare($query);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$theses = $stmt->get_result();

// Include the global menu
include 'menus/menu.php';

?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Δημιουργία Ανακοίνωσης</title>
    <!--<link rel="stylesheet" href="lobby.css">-->
    <link rel="stylesheet" href="AllCss.css">

    <script>
        // Ενημέρωση του κειμένου προεπισκόπησης δυναμικά
        function updatePreview() {
            const thesisSelect = document.getElementById("thesisID");
            const preview = document.getElementById("announcementPreview");
            const thesisDetails = thesisSelect.selectedOptions[0].dataset;

            if (thesisDetails.title) {
                preview.innerText = `Η εξέταση για τη διπλωματική με τίτλο "${thesisDetails.title}" θα πραγματοποιηθεί στις ${thesisDetails.examinationDate}. Η μέθοδος εξέτασης είναι ${thesisDetails.examinationMethod} και θα γίνει στο ${thesisDetails.location}.`;
            } else {
                preview.innerText = "Επιλέξτε μια διπλωματική για να δείτε την προεπισκόπηση της ανακοίνωσης.";
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Δημιουργία Ανακοίνωσης Εξέτασης</h1>

        <!-- Μηνύματα Επιτυχίας/Σφάλματος -->
        <?php if (!empty($successMessage)): ?>
            <p class="success"><?= htmlspecialchars($successMessage) ?></p>
        <?php elseif (!empty($errorMessage)): ?>
            <p class="error"><?= htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>

        <!-- Φόρμα Ανακοίνωσης -->
        <?php if ($theses->num_rows > 0): ?>
            <form method="POST" class="announcement-form">
                <label for="thesisID">Επιλέξτε Διπλωματική:</label>
                <select name="thesisID" id="thesisID" onchange="updatePreview()" required>
                    <option value="" disabled selected>Επιλέξτε διπλωματική</option>
                    <?php while ($row = $theses->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['thesisID']) ?>" 
                                data-title="<?= htmlspecialchars($row['title']) ?>"
                                data-examination-date="<?= htmlspecialchars($row['examinationDate']) ?>"
                                data-examination-method="<?= htmlspecialchars($row['examinationMethod']) ?>"
                                data-location="<?= htmlspecialchars($row['location']) ?>">
                            <?= htmlspecialchars($row['title']) ?> - <?= htmlspecialchars($row['examinationDate']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <p><strong>Προεπισκόπηση Ανακοίνωσης:</strong></p>
                <p id="announcementPreview">Επιλέξτε μια διπλωματική για να δείτε την προεπισκόπηση της ανακοίνωσης.</p>

                <button type="submit">Δημιουργία Ανακοίνωσης</button>
            </form>
        <?php else: ?>
            <p>Δεν υπάρχουν διπλωματικές με πλήρεις πληροφορίες εξέτασης διαθέσιμες για ανακοινώσεις.</p>
        <?php endif; ?>

    </div>
</body>
</html>
