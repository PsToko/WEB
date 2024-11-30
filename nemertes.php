<?php
include 'access.php';
session_start();

// Ελέγξτε αν ο χρήστης είναι συνδεδεμένος
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'students') {
    header("Location: login.php?block=1");
    exit();
}

// Εύρεση της διπλωματικής με τις κατάλληλες συνθήκες
$sql = "SELECT thesisID, title FROM thesis 
        WHERE status = 'under review' 
        AND finalGrade IS NOT NULL 
        AND member1Grade IS NOT NULL 
        AND member2Grade IS NOT NULL 
        LIMIT 1";
$result = $con->query($sql);

$thesis = null;
if ($result->num_rows > 0) {
    $thesis = $result->fetch_assoc();
}

// Διαχείριση αποθήκευσης συνδέσμου στο nemertes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nemertes_link'])) {
    if ($thesis) {
        $thesis_id = $thesis['thesisID'];
        $nemertes_link = trim($_POST['nemertes_link']);

        if (!empty($nemertes_link)) {
            $update_sql = "UPDATE thesis SET nemertes = ? WHERE thesisID = ?";
            $stmt = $con->prepare($update_sql);
            $stmt->bind_param("si", $nemertes_link, $thesis_id);

            if ($stmt->execute()) {
                echo "<p>Ο σύνδεσμος αποθηκεύτηκε με επιτυχία στο πεδίο 'nemertes'.</p>";
            } else {
                echo "<p>Σφάλμα κατά την αποθήκευση του συνδέσμου: " . $stmt->error . "</p>";
            }
        } else {
            echo "<p>Παρακαλώ εισάγετε έναν έγκυρο σύνδεσμο.</p>";
        }
    } else {
        echo "<p>Δεν βρέθηκε κατάλληλη διπλωματική εργασία.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="dipl.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Αποθήκευση Συνδέσμου Nemertes</title>
</head>
<body>
    <h3>Αποθήκευση Συνδέσμου Nemertes</h3>

    <?php if ($thesis): ?>
        <p>Διπλωματική Εργασία: <?php echo htmlspecialchars($thesis['title']); ?></p>
        <form method="POST" action="">
            <label for="nemertes_link">Σύνδεσμος Nemertes:</label>
            <input type="url" id="nemertes_link" name="nemertes_link" required>
            <br>
            <button type="submit">Αποθήκευση</button>
            <button class="add-topic-button" onclick="window.location.href = 'st_dipl.php';"> Επιστροφή</button>
        </form>
    <?php else: ?>
        <p>Δεν βρέθηκε κατάλληλη διπλωματική εργασία προς ενημέρωση.</p>
    <?php endif; ?>
</body>
</html>
