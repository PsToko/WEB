<?php
include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professors') {
    header("Location: login.php?block=1");
    exit();
}

// Retrieve selected filters from GET parameters
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_role = isset($_GET['role']) ? $_GET['role'] : '';

// Define SQL conditions
$status_condition = ($filter_status !== '') ? "AND status = ?" : "";
$role_condition = "";

if ($filter_role === 'supervisor') {
    $role_condition = "supervisorID = ?";
} elseif ($filter_role === 'member') {
    $role_condition = "(member1ID = ? OR member2ID = ?)";
} else {
    $role_condition = "(supervisorID = ? OR member1ID = ? OR member2ID = ?)";
}

// Construct query dynamically
$query = "
    SELECT 
        thesis.thesisID, 
        thesis.title, 
        thesis.description, 
        thesis.pdf, 
        thesis.status, 
        thesis.studentID, 
        thesis.supervisorID, 
        thesis.member1ID, 
        thesis.member2ID, 
        thesis.postedDate, 
        thesis.assignmentDate, 
        thesis.completionDate, 
        thesis.examinationDate, 
        thesis.finalGrade,
        students.name AS student_name, 
        students.surname AS student_surname, 
        supervisor.name AS supervisor_name, 
        supervisor.surname AS supervisor_surname,
        member1.name AS member1_name, 
        member1.surname AS member1_surname,
        member2.name AS member2_name, 
        member2.surname AS member2_surname
    FROM 
        thesis
    LEFT JOIN students ON thesis.studentID = students.Student_ID
    LEFT JOIN professors AS supervisor ON thesis.supervisorID = supervisor.Professor_ID
    LEFT JOIN professors AS member1 ON thesis.member1ID = member1.Professor_ID
    LEFT JOIN professors AS member2 ON thesis.member2ID = member2.Professor_ID
    WHERE 
        $role_condition 
        $status_condition
";

$stmt = $con->prepare($query);

// Bind parameters dynamically
if ($filter_status !== '') {
    if ($role_condition === "(supervisorID = ? OR member1ID = ? OR member2ID = ?)") {
        $stmt->bind_param('iiis', $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $filter_status);
    } elseif ($role_condition === "(member1ID = ? OR member2ID = ?)") {
        $stmt->bind_param('iis', $_SESSION['user_id'], $_SESSION['user_id'], $filter_status);
    } else {
        $stmt->bind_param('is', $_SESSION['user_id'], $filter_status);
    }
} else {
    if ($role_condition === "(supervisorID = ? OR member1ID = ? OR member2ID = ?)") {
        $stmt->bind_param('iii', $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']);
    } elseif ($role_condition === "(member1ID = ? OR member2ID = ?)") {
        $stmt->bind_param('ii', $_SESSION['user_id'], $_SESSION['user_id']);
    } else {
        $stmt->bind_param('i', $_SESSION['user_id']);
    }
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Διαχείριση Θεμάτων Διπλωματικών</title>
    <link rel="stylesheet" href="dipl.css">
    <style>
        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .modal-close {
            float: right;
            font-size: 20px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
        }

        .modal-close:hover {
            color: #000;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Ιστορικό Διπλωματικών</h1>

        <!-- Filters -->
        <form method="GET" action="" style="margin-bottom: 20px;">
            <label for="status">Κατάσταση:</label>
            <select name="status" id="status">
                <option value="">Όλες</option>
                <option value="under assignment" <?= $filter_status === 'under assignment' ? 'selected' : '' ?>>Under Assignment</option>
                <option value="active" <?= $filter_status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="under review" <?= $filter_status === 'under review' ? 'selected' : '' ?>>Under Review</option>
                <option value="finalized" <?= $filter_status === 'finalized' ? 'selected' : '' ?>>Finalized</option>
                <option value="withdrawn" <?= $filter_status === 'withdrawn' ? 'selected' : '' ?>>Withdrawn</option>
            </select>

            <label for="role">Ρόλος:</label>
            <select name="role" id="role">
                <option value="">Όλα</option>
                <option value="supervisor" <?= $filter_role === 'supervisor' ? 'selected' : '' ?>>Supervisor</option>
                <option value="member" <?= $filter_role === 'member' ? 'selected' : '' ?>>Member</option>
            </select>

            <button type="submit">Φιλτράρισμα</button>
        </form>

        <!-- Display thesis topics -->
        <div class="topic-list">
            <h2>Λίστα Θεμάτων</h2>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="topic" onclick="showModal(' . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . ')">';
                    echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
                    echo '<p>Σύνοψη: ' . htmlspecialchars($row['description']) . '</p>';
                    echo '<p>Κατάσταση: ' . htmlspecialchars($row['status']) . '</p>';
                    echo '</div>';
                }
            } else {
                echo '<p>Δεν βρέθηκαν αποτελέσματα για τα φίλτρα σας.</p>';
            }
            ?>
        </div>

        <!-- Modal structure -->
        <div id="detailsModal" class="modal">
            <div class="modal-content">
                <span class="modal-close" onclick="closeModal()">&times;</span>
                <h2 id="modalTitle">Τίτλος</h2>
                <p id="modalDescription">Περιγραφή</p>
                <p><strong>Κατάσταση:</strong> <span id="modalStatus"></span></p>
                <p><strong>Φοιτητής:</strong> <span id="modalStudent"></span></p>
                <p><strong>Επιβλέπων:</strong> <span id="modalSupervisor"></span></p>
                <p><strong>Μέλος επιτροπής:</strong> <span id="modalMember1"></span></p>
                <p><strong>Μέλος επιτροπής:</strong> <span id="modalMember2"></span></p>
                <p><strong>Ημερομηνίες:</strong></p>
                <ul>
                    <li><strong>Ημερομηνία Ανάθεσης:</strong> <span id="modalAssignDate"></span></li>
                    <li><strong>Ημερομηνία Έναρξης:</strong> <span id="modalStartDate"></span></li>
                    <li><strong>Ημερομηνία Υποβολής:</strong> <span id="modalSubmitDate"></span></li>
                    <li><strong>Ημερομηνία Αξιολόγησης:</strong> <span id="modalReviewDate"></span></li>
                    <li><strong>Ημερομηνία Ολοκλήρωσης:</strong> <span id="modalFinalizedDate"></span></li>
                </ul>
                <p><strong>Τελικός Βαθμός:</strong> <span id="modalFinalGrade"></span></p>
            </div>
        </div>
    </div>

    <script>
        function showModal(data) {
            document.getElementById('modalTitle').innerText = data.title;
            document.getElementById('modalDescription').innerText = data.description;
            document.getElementById('modalStatus').innerText = data.status;

            document.getElementById('modalStudent').innerText = data.student_name 
                ? `${data.student_name} ${data.student_surname}` 
                : 'Δεν έχει ανατεθεί';

            document.getElementById('modalSupervisor').innerText = data.supervisor_name 
                ? `${data.supervisor_name} ${data.supervisor_surname}` 
                : 'Δεν έχει οριστεί';

            document.getElementById('modalMember1').innerText = data.member1_name 
                ? `${data.member1_name} ${data.member1_surname}` 
                : 'Δεν έχει οριστεί';

            document.getElementById('modalMember2').innerText = data.member2_name 
                ? `${data.member2_name} ${data.member2_surname}` 
                : 'Δεν έχει οριστεί';

            document.getElementById('modalAssignDate').innerText = data.assignmentDate || 'Δεν υπάρχει';
            document.getElementById('modalStartDate').innerText = data.postedDate || 'Δεν υπάρχει';
            document.getElementById('modalSubmitDate').innerText = data.examinationDate || 'Δεν υπάρχει';
            document.getElementById('modalReviewDate').innerText = data.completionDate || 'Δεν υπάρχει';
            document.getElementById('modalFinalizedDate').innerText = data.finalizedDate || 'Δεν υπάρχει';

            document.getElementById('modalFinalGrade').innerText = data.finalGrade || 'Δεν έχει βαθμολογηθεί';

            document.getElementById('detailsModal').style.display = 'block';
        }


        function closeModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }
    </script>
</body>
</html>
