<?php
include 'access.php';
session_start();

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professors') {
    header("Location: login.php?block=1");
    exit();
}

// Handle form submission to assign student to a thesis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['thesisID'], $_POST['studentID'])) {
    $thesisID = $_POST['thesisID'];
    $studentID = $_POST['studentID'];

    $studentID = $studentID ?: NULL; // Use NULL if no student is selected


    // Update the thesis with the selected student
    $updateThesisQuery = "UPDATE thesis SET studentID = ? WHERE thesisID = ?";
    $updateThesisStmt = $con->prepare($updateThesisQuery);
    $updateThesisStmt->bind_param('ii', $studentID, $thesisID);
    $updateThesisStmt->execute();

    // Set the student's Has_Thesis to 1
    $updateStudentQuery = "UPDATE students SET Has_Thesis = 1 WHERE Student_ID = ?";
    $updateStudentStmt = $con->prepare($updateStudentQuery);
    $updateStudentStmt->bind_param('i', $studentID);
    $updateStudentStmt->execute();
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Ανάθεση Διπλωματικών</title>
    <link rel="stylesheet" href="dipl.css">
</head>
<body>
<div class="container">
    <h1>Ανάθεση Διπλωματικών</h1>

    <!-- Display thesis topics -->
    <div class="topic-list">
        <h2>Λίστα Θεμάτων</h2>
        <?php
        $query = "SELECT thesisID, title, description FROM thesis WHERE supervisorID = ? AND status = 'under assignment' AND studentID IS NULL";
        $stmt = $con->prepare($query);
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="topic">';
                echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
                echo '<p>Σύνοψη: ' . htmlspecialchars($row['description']) . '</p>';

                // Fetch eligible students
                $studentsQuery = "SELECT Student_ID, AM, name, surname FROM students WHERE Has_Thesis = 0";
                $studentsResult = $con->query($studentsQuery);

                echo '<form method="POST" action="">';
                echo '<input type="hidden" name="thesisID" value="' . $row['thesisID'] . '">';
                
                // Add dropdown for students
                echo '<label for="studentID">Επιλογή Φοιτητή: </label>';
                echo '<select name="studentID" required>';
                while ($student = $studentsResult->fetch_assoc()) {
                    echo '<option value="' . $student['Student_ID'] . '">' . htmlspecialchars($student['AM'] . ' - ' . $student['name'] . ' ' . $student['surname']) . '</option>';
                }
                echo '</select>';
                
                // Add submit button
                echo '<button type="submit">Ανάθεση</button>';
                echo '</form>';
                
                echo '</div>';
            }
        } else {
            echo '<p>Δεν υπάρχουν διαθέσιμα θέματα.</p>';
        }
        ?>
    </div>

    <button class="add-topic-button" onclick="window.location.href = 'professor.php';">Επιστροφή</button>
</div>
</body>
</html>
