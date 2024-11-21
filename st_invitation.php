<?php
include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has professor privileges
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
        thesis.status, 
        thesis.postedDate, 
        thesis.assignmentDate, 
        thesis.completionDate, 
        thesis.examinationDate, 
        thesis.finalGrade,
        students.name AS student_name, 
        students.surname AS student_surname
    FROM 
        thesis
    LEFT JOIN students ON thesis.studentID = students.Student_ID
    WHERE 
        $role_condition 
        $status_condition
";

$stmt = $con->prepare($query);

// Bind parameters dynamically
if ($filter_status !== '') {
    $stmt->bind_param('is', $_SESSION['user_id'], $filter_status);
} else {
    $stmt->bind_param('i', $_SESSION['user_id']);
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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f9;
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

            <button type="submit">Φιλτράρισμα</button>
        </form>

        <!-- Display thesis topics -->
        <div class="topic-list">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="topic" onclick="showModal(' . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . ')">';
                    echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
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
                <div id="invitationHistory">
                    <h3>Invitation History</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Professor Name</th>
                                <th>Status</th>
                                <th>Sent Date</th>
                                <th>Response Date</th>
                            </tr>
                        </thead>
                        <tbody id="invitationHistoryBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showModal(data) {
            document.getElementById('modalTitle').innerText = data.title;
            document.getElementById('modalDescription').innerText = data.description;

            // Fetch invitation history
            fetch(`fetch_invitations.php?thesisID=${data.thesisID}`)
                .then(response => response.json())
                .then(invitations => {
                    const tbody = document.getElementById('invitationHistoryBody');
                    tbody.innerHTML = invitations.map(invitation => `
                        <tr>
                            <td>${invitation.professorName}</td>
                            <td>${invitation.status}</td>
                            <td>${invitation.sentDate}</td>
                            <td>${invitation.responseDate || 'N/A'}</td>
                        </tr>
                    `).join('');
                });

            document.getElementById('detailsModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }
    </script>
</body>
</html>
