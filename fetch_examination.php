<?php
include 'access.php';

if (isset($_GET['thesis_id'])) {
    $thesis_id = intval($_GET['thesis_id']);

    $query = "
        SELECT 
            examinationMethod, 
            location, 
            st_thesis,
            can_review
        FROM 
            examination 
        WHERE 
            thesisID = ?
    ";

    $stmt = $con->prepare($query);
    $stmt->bind_param('i', $thesis_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();

        // Προσθήκη του πλήρους URL για το αρχείο PDF
        if (!empty($data['st_thesis'])) {
            $data['st_thesis_url'] = "uploads/" . $data['st_thesis'];
        } else {
            $data['st_thesis_url'] = null;
        }

        echo json_encode($data);
    } else {
        echo json_encode([]);
    }
}
?>
