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

// Connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "thesismanagementsystem";

// Establish connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get dynamic student ID from session
$studentID = $_SESSION['user_id'];

// Fetch thesis details for the logged-in student
$query = "
    SELECT 
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
    WHERE t.studentID = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $studentID);
$stmt->execute();
$result = $stmt->get_result();

// Check if the student has a thesis assigned
$thesis = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Thesis</title>
    <link rel="stylesheet" href="lobby.css">
    <style>
        .thesis-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 1em;
            text-align: left;
        }

        .thesis-table th, .thesis-table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
        }

        .thesis-table th {
            background-color: #f4f4f9;
            font-weight: bold;
        }

        .thesis-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .thesis-table tr:nth-child(odd) {
            background-color: #fff;
        }

        .no-thesis {
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
        <?php else: ?>
            <p class="no-thesis">No thesis assigned to you at the moment.</p>
        <?php endif; ?>
    </div>
</body>
</html>
