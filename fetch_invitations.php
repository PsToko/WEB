<?php
// fetch_invitations.php
include 'access.php';

if (!isset($_GET['thesis_id']) || empty($_GET['thesis_id'])) {
    echo "<p>Δεν βρέθηκαν προσκλήσεις.</p>";
    exit();
}

$thesisID = (int)$_GET['thesis_id']; // Καθαρισμός εισόδου

$query = "
    SELECT 
        i.invitationID, 
        CONCAT(p.Name, ' ', p.Surname) AS professorName, 
        i.status, 
        i.sentDate, 
        i.responseDate 
    FROM 
        Invitations i
    INNER JOIN 
        Professors p ON i.professorID = p.Professor_ID
    WHERE 
        i.thesisID = ?
    ORDER BY 
        i.sentDate DESC
";

$stmt = $con->prepare($query);
$stmt->bind_param('i', $thesisID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<table border='1'>
            <thead>
                <tr>
                    <th>Όνομα Καθηγητή</th>
                    <th>Κατάσταση</th>
                    <th>Ημερομηνία Αποστολής</th>
                    <th>Ημερομηνία Απόκρισης</th>
                </tr>
            </thead>
            <tbody>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row['professorName']) . "</td>
                <td>" . htmlspecialchars($row['status']) . "</td>
                <td>" . htmlspecialchars($row['sentDate']) . "</td>
                <td>" . htmlspecialchars($row['responseDate'] ?? 'Μη διαθέσιμο') . "</td>
              </tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<p>Δεν βρέθηκαν προσκλήσεις.</p>";
}

$stmt->close();
?>
