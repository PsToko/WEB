<?php

include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has student privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'students') {
    header("Location: login.php?block=1");
    exit();
}

// Ανάκτηση δεδομένων εξέτασης
$examinationID = isset($_GET['examinationID']) ? intval($_GET['examinationID']) : 1;

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
    WHERE e.StudentID = ?
";

$stmt = $con->prepare($sql);
$stmt->bind_param("i",  $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die("No examination found for ID: $examinationID");
}

// Υπολογισμός του μέσου όρου των τριών βαθμολογιών
$averageGrade = round(($data['finalGrade'] + $data['member1Grade'] + $data['member2Grade']) / 3, 2);

// Check if grades are available
if (!$data || is_null($data['finalGrade']) || is_null($data['member1Grade']) || is_null($data['member2Grade'])) {
    echo "<!DOCTYPE html>
    <html lang='el'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Πρακτικό Εξέτασης</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                text-align: center;
            }
            .message {
                font-size: 1.2em;
                color: #333;
                background-color: #f9f9f9;
                border: 1px solid #ddd;
                padding: 20px;
                border-radius: 10px;
                display: inline-block;
            }
        </style>
    </head>
    <body>
        <div class='message'>
            Η βαθμολογία δεν έχει ολοκληρωθεί ακόμα. Παρακαλώ ελέγξτε αργότερα.
        </div>
    </body>
    </html>";
    exit();
}

?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dipl.css">
    <title>Πρακτικό Συνεδρίασης</title>
    <style>
                body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .section {
            margin-bottom: 15px;
        }
        .highlight {
            font-weight: bold;
            text-decoration: underline;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>ΠΡΑΚΤΙΚΟ ΕΞΕΤΑΣΗΣ</h1>
        <p class="section">ΤΗΣ ΤΡΙΜΕΛΟΥΣ ΕΠΙΤΡΟΠΗΣ ΓΙΑ ΤΗΝ ΠΑΡΟΥΣΙΑΣΗ ΚΑΙ ΚΡΙΣΗ ΤΗΣ ΔΙΠΛΩΜΑΤΙΚΗΣ ΕΡΓΑΣΙΑΣ</p>

        <p class="section">του/της φοιτητή/φοιτήτριας κ. <span class="highlight"><?= htmlspecialchars($data['StudentName'] . ' ' . $data['StudentSurname']) ?></span></p>

        <p class="section">Η συνεδρίαση πραγματοποιήθηκε στην αίθουσα <span class="highlight"><?= htmlspecialchars($data['location']) ?></span>, στις <span class="highlight"><?= htmlspecialchars($data['examinationDate']) ?></span> και ώρα <span class="highlight"><?= htmlspecialchars($data['examinationTime']) ?></span>.</p>

        <p class="section">Στην συνεδρίαση είναι παρόντα τα μέλη της Τριμελούς Επιτροπής, κ.κ.:</p>
        <ol>
            <li><span class="highlight"><?= htmlspecialchars($data['SupervisorName'] . ' ' . $data['SupervisorSurname']) ?></span></li>
            <li><span class="highlight"><?= htmlspecialchars($data['Member1Name'] . ' ' . $data['Member1Surname']) ?></span></li>
            <li><span class="highlight"><?= htmlspecialchars($data['Member2Name'] . ' ' . $data['Member2Surname']) ?></span></li>
        </ol>
        <p class="section">οι οποίοι ορίσθηκαν από την Συνέλευση του ΤΜΗΥΠ, στην συνεδρίαση της με αριθμό
        <span class="highlight"><?= htmlspecialchars($data['general_assembly']) ?></span> </p>

        <p class="section">Ο/Η φοιτητής/φοιτήτρια κ. <span class="highlight"><?= htmlspecialchars($data['StudentName'] . ' ' . $data['StudentSurname']) ?></span> ανέπτυξε το θέμα της Διπλωματικής του/της Εργασίας, με τίτλο:</p>
        <p class="section"><span class="highlight"><?= htmlspecialchars($data['title']) ?></span></p>

        <p class="section">Στην συνέχεια υποβλήθηκαν ερωτήσεις στον υποψήφιο από τα μέλη της Τριμελούς Επιτροπής και τους άλλους παρευρισκόμενους, προκειμένου να διαμορφώσουν σαφή άποψη για το περιεχόμενο της εργασίας, για την επιστημονική συγκρότηση του μεταπτυχιακού φοιτητή.</p>

        <p class="section">Μετά το τέλος της ανάπτυξης της εργασίας του και των ερωτήσεων, ο υποψήφιος αποχωρεί.</p>

        <p class="section">Ο Επιβλέπων καθηγητής κ. <span class="highlight"><?= htmlspecialchars($data['SupervisorName'] . ' ' . $data['SupervisorSurname']) ?></span>, προτείνει στα μέλη της Τριμελούς Επιτροπής, να ψηφίσουν για το αν εγκρίνεται η διπλωματική εργασία του <span class="highlight"><?= htmlspecialchars($data['StudentName'] . ' ' . $data['StudentSurname']) ?></span>.</p>

        <p class="section">Τα μέλη της Τριμελούς Επιτροπής που ψηφίζουν:</p>
        <ol>
            <li><span class="highlight"><?= htmlspecialchars($data['Member1Name'] . ' ' . $data['Member1Surname']) ?></span></li>
            <li><span class="highlight"><?= htmlspecialchars($data['Member2Name'] . ' ' . $data['Member2Surname']) ?></span></li>
            <li><span class="highlight"><?= htmlspecialchars($data['SupervisorName'] . ' ' . $data['SupervisorSurname']) ?></span></li>
        </ol>

        <p class="section">υπέρ της εγκρίσεως της Διπλωματικής Εργασίας του φοιτητή <span class="highlight"><?= htmlspecialchars($data['StudentName'] . ' ' . $data['StudentSurname']) ?></span>, επειδή θεωρούν επιστημονικά επαρκή και το περιεχόμενό της ανταποκρίνεται στο θέμα που του δόθηκε.</p>

        <p class="section">Μετά της έγκριση, ο εισηγητής κ. <span class="highlight"><?= htmlspecialchars($data['SupervisorName'] . ' ' . $data['SupervisorSurname']) ?></span>, προτείνει στα μέλη της Τριμελούς Επιτροπής, να απονεμηθεί στο/στη φοιτητή/τρια κ. <span class="highlight"><?= htmlspecialchars($data['StudentName'] . ' ' . $data['StudentSurname']) ?></span> ο βαθμός <span class="highlight"><?= htmlspecialchars($averageGrade) ?></span>.</p>

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
                    <td><?= htmlspecialchars($data['SupervisorName'] . ' ' . $data['SupervisorSurname']) ?></td>
                    <td>Επιβλέπων</td>
                    <td><?= htmlspecialchars($data['finalGrade']) ?></td>
                </tr>
                <tr>
                    <td><?= htmlspecialchars($data['Member1Name'] . ' ' . $data['Member1Surname']) ?></td>
                    <td>Μέλος 1</td>
                    <td><?= htmlspecialchars($data['member1Grade']) ?></td>
                </tr>
                <tr>
                    <td><?= htmlspecialchars($data['Member2Name'] . ' ' . $data['Member2Surname']) ?></td>
                    <td>Μέλος 2</td>
                    <td><?= htmlspecialchars($data['member2Grade']) ?></td>
                </tr>
            </tbody>
        </table>

        <p class="section">Μετά την έγκριση και την απονομή του βαθμού <span class="highlight"><?= htmlspecialchars($averageGrade) ?></span>, η Τριμελής Επιτροπή, προτείνει να προχωρήσει στην διαδικασία για να ανακηρύξει τον κ. <span class="highlight"><?= htmlspecialchars($data['StudentName'] . ' ' . $data['StudentSurname']) ?></span>, σε διπλωματούχο.</p>
    </div>
    <button class="add-topic-button" onclick="window.location.href = 'st_dipl.php';">Επιστροφή</button>

</body>
</html>

