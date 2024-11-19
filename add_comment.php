<?php
include 'access.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $thesisID = intval($input['thesisID']);
    $comment = trim($input['comment']);
    $professorID = $_SESSION['user_id'];

    if (strlen($comment) > 300) {
        echo json_encode(['success' => false, 'error' => 'Το σχόλιο ξεπερνά τις 300 λέξεις.']);
        exit();
    }

    $query = "INSERT INTO thesiscomments (thesisID, professorID, comment) VALUES (?, ?, ?)";
    $stmt = $con->prepare($query);
    $stmt->bind_param('iis', $thesisID, $professorID, $comment);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Αποτυχία εισαγωγής σχολίου.']);
    }
}
