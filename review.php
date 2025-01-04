<?php
include 'access.php';
session_start();

// Ελέγξτε αν ο χρήστης είναι συνδεδεμένος και έχει δικαιώματα
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professors') {
    header("Location: login.php?block=1");
    exit();
}

// Παράμετρος χρήστη
$user_id = $_SESSION['user_id'];

// Λήψη examination που είναι σε κατάσταση "under review"
$sql = "SELECT e.*, t.title 
        FROM examination e
        JOIN thesis t ON e.thesisID = t.thesisID
        WHERE (e.supervisorID = $user_id OR e.member1ID = $user_id OR e.member2ID = $user_id)
        AND t.status = 'under review'";

$result = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="AllCss.css">
    <title>Ενεργές Εξετάσεις</title>
</head>
<body>
    <h1>Ενεργές Εξετάσεις</h1>
    <form method="POST" action="review.php">
        <label for="examination">Επιλέξτε Εξέταση:</label>
        <select name="examination_id" id="examination" required>
            <option value="" disabled selected>Επιλέξτε...</option>
            <?php while ($row = $result->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($row['examinationID']); ?>">
                    <?= htmlspecialchars($row['title']); ?> - <?= htmlspecialchars($row['examinationMethod']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <br><br>
        <button type="submit">Υποβολή</button>
        <button class="add-topic-button" onclick="window.location.href = 'all_thesis.php';">Επιστροφή</button>
    </form>

    <?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['examination_id'])) {
    $examination_id = $_POST['examination_id'];

    // Ελέγξτε αν το examination επιτρέπει review
    $details_sql = "SELECT e.*, t.title, t.description, e.can_review 
                    FROM examination e
                    JOIN thesis t ON e.thesisID = t.thesisID
                    WHERE e.examinationID = ?";
    $stmt = $con->prepare($details_sql);
    $stmt->bind_param("i", $examination_id);
    $stmt->execute();
    $details_result = $stmt->get_result();

    if ($details_result->num_rows > 0) {
        $details = $details_result->fetch_assoc();

        // Έλεγχος αν μπορεί να γίνει αξιολόγηση
        if ($details['can_review'] == 1) {
            ?>
            <h2>Λεπτομέρειες Εξέτασης</h2>
            <table border="1">
                <tr>
                    <th>Τίτλος</th>
                    <th>Μέθοδος Εξέτασης</th>
                    <th>Τοποθεσία</th>
                    <th>Περιγραφή</th>
                </tr>
                <tr>
                    <td><?= htmlspecialchars($details['title']); ?></td>
                    <td><?= htmlspecialchars($details['examinationMethod']); ?></td>
                    <td><?= htmlspecialchars($details['location']); ?></td>
                    <td><?= htmlspecialchars($details['description']); ?></td>
                </tr>
            </table>

            <h3>Φόρμα Υποβολής Βαθμολογίας</h3>
            <form action="process_review.php" method="POST">
                <input type="hidden" name="examination_id" value="<?= htmlspecialchars($examination_id); ?>">

                <label for="criteria1">Ποιότητα της Δ.Ε. και βαθμός εκπλήρωσης των στόχων της (60%):</label>
                <input type="number" id="criteria1" name="criteria1" min="0" max="10" step="0.1" required>
                <br><br>

                <label for="criteria2">Χρονικό διάστημα εκπόνησής της (15%):</label>
                <input type="number" id="criteria2" name="criteria2" min="0" max="10" step="0.1" required>
                <br><br>

                <label for="criteria3">Ποιότητα και πληρότητα του κειμένου της εργασίας (15%):</label>
                <input type="number" id="criteria3" name="criteria3" min="0" max="10" step="0.1" required>
                <br><br>

                <label for="criteria4">Συνολική εικόνα της παρουσίασης (10%):</label>
                <input type="number" id="criteria4" name="criteria4" min="0" max="10" step="0.1" required>
                <br><br>

                <button type="submit">Υποβολή Αξιολόγησης</button>
            </form>
            <?php
        } else {
            echo "<p>Δεν μπορείτε να υποβάλετε βαθμολογία για αυτήν την εξέταση. Η αξιολόγηση δεν είναι διαθέσιμη.</p>";

                        // Έλεγχος αν ο συνδεδεμένος χρήστης είναι ο supervisor
                        if ($details['supervisorID'] == $user_id) {
                            ?>
                            <form method="POST" action="enable_review.php">
                                <input type="hidden" name="examination_id" value="<?= htmlspecialchars($examination_id); ?>">
                                <button type="submit">Ενεργοποίηση Δυνατότητας Αξιολόγησης</button>
                            </form>
                            <?php
                        }            
        }

    } else {
        echo "<p>Δεν βρέθηκαν λεπτομέρειες για την επιλεγμένη εξέταση.</p>";
    }
}
?>

</body>
</html>
