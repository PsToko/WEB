<?php
// st_dipl.php
include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has student privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'students') {
    header("Location: login.php?block=1");
    exit();
}

// Get dynamic student ID from session
$studentID = $_SESSION['user_id'];

// Fetch thesis details for the logged-in student
$query = "
    SELECT 
        t.thesisID, 
        t.title, 
        t.description, 
        t.pdf, 
        t.status, 
        t.assignmentDate, 
        p1.Name AS supervisorName, 
        p1.Surname AS supervisorSurname, 
        p2.Name AS member1Name, 
        p2.Surname AS member1Surname, 
        p3.Name AS member2Name, 
        p3.Surname AS member2Surname 
    FROM Thesis t
    LEFT JOIN Professors p1 ON t.supervisorID = p1.Professor_ID
    LEFT JOIN Professors p2 ON t.member1ID = p2.Professor_ID
    LEFT JOIN Professors p3 ON t.member2ID = p3.Professor_ID
    WHERE t.studentID = ? AND t.status != 'withdrawn'
";

$stmt = $con->prepare($query);
$stmt->bind_param('i', $studentID);
$stmt->execute();
$result = $stmt->get_result();

// Check if the student has a thesis assigned
$thesis = $result->fetch_assoc();

// Fetch examination details for the student's thesis
$examination = null;
if ($thesis) {
    $examinationQuery = "
        SELECT examinationDate, examinationMethod, location 
        FROM Examination 
        WHERE thesisID = ?";
    $examStmt = $con->prepare($examinationQuery);
    $examStmt->bind_param('i', $thesis['thesisID']);
    $examStmt->execute();
    $examination = $examStmt->get_result()->fetch_assoc();
}

// Handle examination update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $thesis && $thesis['status'] === 'under review') {
    $examinationDate = $_POST['examinationDate'];
    $examinationMethod = $_POST['examinationMethod'];
    $location = $_POST['location'];

    // Update the Examination table
    $updateExamQuery = "
        UPDATE Examination 
        SET examinationDate = ?, examinationMethod = ?, location = ? 
        WHERE thesisID = ?";
    $updateExamStmt = $con->prepare($updateExamQuery);
    $updateExamStmt->bind_param('sssi', $examinationDate, $examinationMethod, $location, $thesis['thesisID']);
    $updateExamStmt->execute();

    // Synchronize the examination date to the Thesis table
    $updateThesisQuery = "UPDATE Thesis SET examinationDate = ? WHERE thesisID = ?";
    $updateThesisStmt = $con->prepare($updateThesisQuery);
    $updateThesisStmt->bind_param('si', $examinationDate, $thesis['thesisID']);
    $updateThesisStmt->execute();

    // Refresh page to show updated information
    header("Location: st_dipl.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Thesis</title>
    <link rel="stylesheet" href="lobby.css">
    <style>
        .thesis-table, .examination-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 1em;
            text-align: left;
        }

        .thesis-table th, .thesis-table td, .examination-table th, .examination-table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
        }

        .thesis-table th, .examination-table th {
            background-color: #f4f4f9;
            font-weight: bold;
        }

        .thesis-table tr:nth-child(even), .examination-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .thesis-table tr:nth-child(odd), .examination-table tr:nth-child(odd) {
            background-color: #fff;
        }

        .no-thesis, .no-exam {
            font-size: 1.2em;
            color: #333;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <button class="go-back" onclick="window.location.href = 'student.php';">Go Back</button>
        <h1>Your Thesis Information</h1>

        <?php if ($thesis): ?>
            <table class="thesis-table">
                <tr>
                    <th>Subject</th>
                    <td><?= htmlspecialchars($thesis['title']) ?></td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td><?= nl2br(htmlspecialchars($thesis['description'])) ?></td>
                </tr>
                <tr>
                    <th>PDF</th>
                    <td><?= $thesis['pdf'] ? "<a href='uploads/{$thesis['pdf']}' target='_blank'>Download</a>" : "No PDF uploaded" ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td><?= htmlspecialchars($thesis['status']) ?></td>
                </tr>
                <tr>
                    <th>Supervisor</th>
                    <td><?= htmlspecialchars($thesis['supervisorName'] . " " . $thesis['supervisorSurname']) ?></td>
                </tr>
                <tr>
                    <th>Committee Member 1</th>
                    <td><?= $thesis['member1Name'] ? htmlspecialchars($thesis['member1Name'] . " " . $thesis['member1Surname']) : "Vacant" ?></td>
                </tr>
                <tr>
                    <th>Committee Member 2</th>
                    <td><?= $thesis['member2Name'] ? htmlspecialchars($thesis['member2Name'] . " " . $thesis['member2Surname']) : "Vacant" ?></td>
                </tr>
                <tr>
                    <th>Time Passed Since Assignment</th>
                    <td>
                        <?php
                        if ($thesis['assignmentDate']) {
                            $now = new DateTime();
                            $assignmentDate = new DateTime($thesis['assignmentDate']);
                            $interval = $now->diff($assignmentDate);
                            echo $interval->format('%y years, %m months, and %d days');
                        } else {
                            echo "Not yet assigned";
                        }
                        ?>
                    </td>
                </tr>
            </table>

            <!-- Examination Information -->
            <h1>Examination Information</h1>
            <form method="POST" action="">
                <table class="examination-table">
                    <tr>
                        <th>Examination Date</th>
                        <td>
                            <input type="datetime-local" name="examinationDate" 
                                   value="<?= $examination ? htmlspecialchars($examination['examinationDate']) : '' ?>" 
                                   required>
                        </td>
                    </tr>
                    <tr>
                        <th>Examination Method</th>
                        <td>
                            <select name="examinationMethod" required>
                                <option value="online" <?= $examination && $examination['examinationMethod'] === 'online' ? 'selected' : '' ?>>Online</option>
                                <option value="in person" <?= $examination && $examination['examinationMethod'] === 'in person' ? 'selected' : '' ?>>In Person</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>Location</th>
                        <td>
                            <input type="text" name="location" 
                                   value="<?= $examination ? htmlspecialchars($examination['location']) : '' ?>" 
                                   required>
                        </td>
                    </tr>
                </table>
                <?php if ($thesis['status'] === 'under review'): ?>
                    <button type="submit">Update Examination Information</button>
                <?php endif; ?>
                <?php if ($thesis['status'] === 'under review'): ?>
                <div style="text-align: center; margin-top: 20px;">
                <button class="add-topic-button" onclick="window.location.href = 'your_thesis.php';">Υπέβαλε την διπλωματική σου</button>
                <button class="add-topic-button" onclick="window.location.href = 'practical.php';"> Πρακτικό εξέτασης</button>
                <button class="add-topic-button" onclick="window.location.href = 'nemertes.php';"> Στείλε το σύνδεσμο του Νημερτή</button>
                <button class="add-topic-button" onclick="window.location.href = 'student.php';">Επιστροφή</button>
                </div>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <p class="no-thesis">No thesis assigned to you at the moment.</p>
        <?php endif; ?>
    </div>
</body>
</html>
