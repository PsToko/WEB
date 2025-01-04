<?php
include 'access.php';

// Σύνδεση στη βάση δεδομένων
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $user, $password);
} catch (PDOException $e) {
    die("Αποτυχία σύνδεσης στη βάση δεδομένων: " . $e->getMessage());
}

// Ανάκτηση των φίλτρων από τα παραμέτρους GET
$start = $_GET['start'] ?? null;
$end = $_GET['end'] ?? null;

// Δημιουργία του βασικού SQL query
$query = "
    SELECT 
        a.announcementID, 
        t.title AS thesisTitle, 
        a.announcementText, 
        a.examinationDate, 
        a.examinationMethod, 
        a.location
    FROM Announcements a
    INNER JOIN Thesis t ON a.thesisID = t.thesisID
    WHERE a.examinationDate IS NOT NULL
";

// Προσθήκη φίλτρων ημερομηνιών αν υπάρχουν
$params = [];
if ($start) {
    $query .= " AND a.examinationDate >= :start";
    $params[':start'] = $start;
}
if ($end) {
    $query .= " AND a.examinationDate <= :end";
    $params[':end'] = $end;
}

// Ταξινόμηση κατά εξέταση ημερομηνίας, από την πιο κοντινή στο τρέχον
$query .= " ORDER BY a.examinationDate ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ανακοινώσεις Διπλωματικών</title>
    <link rel="stylesheet" href= "AllCss.css">
</head>
<body>
    <a href="login.php" class="golden_button">Σύνδεση</a>

    <div class="container">
        <div class="header">
            <h1>Ανακοινώσεις Διπλωματικών</h1>
        </div>

        <!-- Ενότητα φίλτρων -->
        <div class="filter-section">
            <form method="GET" action="">
                <label for="start">Από:</label>
                <input type="date" id="start" name="start" value="<?= htmlspecialchars($start) ?>">
                <label for="end">Έως:</label>
                <input type="date" id="end" name="end" value="<?= htmlspecialchars($end) ?>">
                <button type="submit" class="button button-primary">Φίλτρο</button>
                <a href="demo.php" class="button button-secondary">Επαναφορά</a>
                <a href="endpoint.php?start=<?= htmlspecialchars($start) ?>&end=<?= htmlspecialchars($end) ?>&format=xml" class="button button-secondary">Εξαγωγή σε XML</a>
                <a href="endpoint.php?start=<?= htmlspecialchars($start) ?>&end=<?= htmlspecialchars($end) ?>&format=json" class="button button-secondary">Εξαγωγή σε JSON</a>
            </form>
        </div>

        <!-- Πίνακας Ανακοινώσεων -->
        <?php if ($announcements): ?>
            <table>
                <thead>
                    <tr>
                        <th>Τίτλος Διπλωματικής</th>
                        <th>Ανακοίνωση</th>
                        <th>Ημερομηνία Εξέτασης</th>
                        <th>Μέθοδος Εξέτασης</th>
                        <th>Τοποθεσία</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($announcements as $announcement): ?>
                        <tr>
                            <td><?= htmlspecialchars($announcement['thesisTitle']) ?></td>
                            <td><?= nl2br(htmlspecialchars($announcement['announcementText'])) ?></td>
                            <td><?= htmlspecialchars($announcement['examinationDate']) ?></td>
                            <td><?= htmlspecialchars($announcement['examinationMethod']) ?></td>
                            <td><?= htmlspecialchars($announcement['location']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-results">Δεν βρέθηκαν ανακοινώσεις για την επιλεγμένη περίοδο.</p>
        <?php endif; ?>
    </div>
</body>
</html>