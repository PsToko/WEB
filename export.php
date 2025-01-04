<?php
include 'access.php';
session_start();

// Έλεγχος αν ο χρήστης είναι συνδεδεμένος
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professors') {
    header("Location: login.php?block=1");
    exit();
}

// Λήψη του τύπου εξαγωγής
$format = isset($_GET['format']) ? $_GET['format'] : '';

// Λήψη δεδομένων για τον συνδεδεμένο χρήστη
$user_id = $_SESSION['user_id'];
$query = "
    SELECT 
        thesis.title, 
        thesis.description, 
        thesis.status, 
        students.name AS student_name, 
        students.surname AS student_surname
    FROM 
        thesis
    LEFT JOIN students ON thesis.studentID = students.Student_ID
    WHERE 
        supervisorID = ? OR member1ID = ? OR member2ID = ?
";
$stmt = $con->prepare($query);
$stmt->bind_param('iii', $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Εξαγωγή δεδομένων σε JSON
if ($format === 'json') {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="thesis_data.json"');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

// Εξαγωγή δεδομένων σε CSV
if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="thesis_data.csv"');

    $output = fopen('php://output', 'w');
    // Γράφει τις κεφαλίδες
    fputcsv($output, ['Τίτλος', 'Περιγραφή', 'Κατάσταση', 'Όνομα Φοιτητή', 'Επώνυμο Φοιτητή']);

    // Γράφει τις γραμμές
    foreach ($data as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
}

// Αν η μορφή δεν είναι έγκυρη
echo "Μη έγκυρη μορφή εξαγωγής.";
exit();
?>