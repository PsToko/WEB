<?php
include 'access.php';

// Ξεκινάμε τη συνεδρία
session_start();

// Ελέγχουμε αν ο χρήστης είναι συνδεδεμένος και έχει τον απαιτούμενο ρόλο
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'students') {
    header("Location: login.php?block=1");
    exit();
}

$studentID = $_SESSION['user_id'];

// Διαχείριση αποστολής της φόρμας για ανέβασμα αρχείου ή προσθήκη συνδέσμου
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['examination_id'])) {
        $examinationID = $_POST['examination_id'];

        // Διαχείριση ανέβασματος PDF
        if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] == UPLOAD_ERR_OK) {
            $pdfFileName = basename($_FILES['pdf']['name']);
            $uploadDir = 'uploads/';
            $uploadFilePath = $uploadDir . $pdfFileName;

            // Εξασφαλίζουμε ότι ο φάκελος ανέβασματων υπάρχει
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Μετακίνηση του ανεβασμένου αρχείου
            if (move_uploaded_file($_FILES['pdf']['tmp_name'], $uploadFilePath)) {
                $query = "UPDATE examination SET st_thesis = ? WHERE StudentID = ? AND ExaminationID = ?";
                if ($stmt = $con->prepare($query)) {
                    $stmt->bind_param('sii', $pdfFileName, $studentID, $examinationID);
                    if ($stmt->execute()) {
                        echo "Το αρχείο ανέβηκε με επιτυχία.";
                    } else {
                        echo "Σφάλμα: " . $stmt->error;
                    }
                    $stmt->close();
                }
            } else {
                echo "Σφάλμα κατά το ανέβασμα του αρχείου.";
            }
        }
      
        // Διαχείριση προσθήκης πολλαπλών συνδέσμων
        if (!empty($_POST['link']) && is_array($_POST['link'])) {
            foreach ($_POST['link'] as $link) {
                if (!empty($link)) {
                    $query = "INSERT INTO links (StudentID, ExaminationID, link) VALUES (?, ?, ?)";
                    if ($stmt = $con->prepare($query)) {
                        $stmt->bind_param('iis', $studentID, $examinationID, $link);
                        if (!$stmt->execute()) {
                            echo "Σφάλμα: " . $stmt->error;
                        }
                        $stmt->close();
                    }
                }
            }
        }

    }
}

// Ανάκτηση των εξετάσεων που σχετίζονται με τον συνδεδεμένο φοιτητή
$query = "
    SELECT 
        e.ExaminationID, 
        e.examinationDate, 
        t.Title, 
        e.st_thesis
    FROM 
        examination e
    JOIN 
        thesis t 
    ON 
        e.thesisID = t.thesisID
    WHERE 
        e.StudentID = ? AND t.status = 'under review'";
$examinations = [];
if ($stmt = $con->prepare($query)) {
    $stmt->bind_param('i', $studentID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $examinations[] = $row;
    }
    $stmt->close();
}

// Include the global menu
include 'menus/menu.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="st_thesis.css">
    <title>Ανέβασμα Αρχείου και Προσθήκη Συνδέσμου</title>
    <script>
        // Συνάρτηση για δυναμική προσθήκη περισσότερων πεδίων συνδέσμου
        function addLinkField() {
            const linkContainer = document.getElementById('link-container');
            const newField = document.createElement('div');
            newField.className = 'link-field';
            newField.innerHTML = `
                <label for="link">Σύνδεσμος:</label>
                <input type="url" name="link[]" placeholder="https://example.com" pattern="https?://.+" required>
                <button type="button" onclick="removeLinkField(this)">Αφαίρεση</button>
                <br><br>
            `;
            linkContainer.appendChild(newField);
        }

        // Συνάρτηση για αφαίρεση ενός πεδίου συνδέσμου
        function removeLinkField(button) {
            const linkField = button.parentNode;
            linkField.remove();
        }
    </script>
</head>
<body>
    <h1>Ανέβασμα Αρχείου για Εξέταση</h1>
    <?php if (empty($examinations)): ?>
        <p>Δεν έχεις την δυνατότητα να ανεβάσεις αρχείο.</p>
    <?php else: ?>
        <form action="" method="post" enctype="multipart/form-data">
            <label for="examination_id">Επιλέξτε Εξέταση:</label>
            <select name="examination_id" id="examination_id" required>
                <?php foreach ($examinations as $exam): ?>
                    <option value="<?= $exam['ExaminationID'] ?>">
                        <?= htmlspecialchars($exam['Title']) ?> (<?= htmlspecialchars($exam['examinationDate']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>

            <?php 
            if (!empty($examinations)): 
                foreach ($examinations as $exam):
                    if (!empty($exam['st_thesis'])): ?>
                        <p>
                            Για την εξέταση "<strong><?= htmlspecialchars($exam['Title']) ?></strong>" 
                            υπάρχει ήδη ανεβασμένο αρχείο: <strong><?= htmlspecialchars($exam['st_thesis']) ?></strong>.
                            Μπορείτε να ανεβάσετε νέο αρχείο για να το αντικαταστήσετε.
                        </p>
                    <?php 
                    endif;
                endforeach;
            endif;
            ?>

            <label for="pdf">Ανέβασμα PDF:</label>
            <input type="file" name="pdf" id="pdf" accept="application/pdf">
            <br><br>

            <!-- Κενός container για συνδέσμους -->
            <div id="link-container"></div>
            <button type="button" onclick="addLinkField()">Προσθήκη Περισσότερων Συνδέσμων</button>
            <br><br>

            <button type="submit">Υποβολή</button>

            <button type="button" onclick="window.location.href = 'st_dipl.php';">Επιστροφή</button>
        </form>
    <?php endif; ?>
</body>
</html>