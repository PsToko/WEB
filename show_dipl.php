<?php
include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professors') {
    header("Location: login.php?block=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Διαχείριση Θεμάτων Διπλωματικών</title>
    <link rel="stylesheet" href="dipl.css">
</head>
<body>
    <div class="container">
        <h1>Προβολή και Δημιουργία Θεμάτων Διπλωματικών</h1>

        <!-- Display thesis topics -->
        <div class="topic-list">
            <h2>Λίστα Θεμάτων</h2>
            <?php
            // Query for thesis topics specific to the logged-in professor
            $query = "SELECT title, description, pdf FROM thesis WHERE supervisorID = ? AND status = 'under assignment'";
            $stmt = $con->prepare($query);
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="topic">';
                    echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
                    echo '<p>Σύνοψη: ' . htmlspecialchars($row['description']) . '</p>';

                    // Check if there is an attached PDF
                    if (!empty($row['pdf'])) {
                        echo '<p>Συνημμένο PDF:</p>';
                        echo '<embed src="uploads/' . htmlspecialchars($row['pdf']) . '" type="application/pdf" width="100%" height="500px" />';
                    } else {
                        echo '<p>Δεν υπάρχει συνημμένο αρχείο</p>';
                    }
                    echo '</div>';
                }
            } else {
                echo '<p>Δεν υπάρχουν θέματα προς το παρόν.</p>';
            }
            ?>
        </div>

        <!-- Button to add a new topic -->
        <button class="add-topic-button" onclick="openModal()">Προσθήκη Νέου Θέματος</button>
        <button class="add-topic-button" onclick="window.location.href = 'professor.php';">Επιστροφή</button>

        <!-- Modal to create a new topic -->
        <div class="modal" id="createTopicModal">
            <div class="modal-content">
                <h2>Δημιουργία Νέου Θέματος</h2>
                <form id="createTopicForm" action="submit_thesis.php" method="post" enctype="multipart/form-data">
                    <label for="title">Τίτλος:</label><br>
                    <input type="text" id="title" name="title" required><br><br>
                    
                    <label for="summary">Σύνοψη:</label><br>
                    <textarea id="summary" name="summary" required></textarea><br><br>
                    
                    <label for="pdf">Επισύναψη Αρχείου (PDF):</label><br>
                    <input type="file" id="pdf" name="pdf" accept=".pdf"><br><br>
                    
                    <button type="submit">Αποθήκευση</button>
                    <button type="button" onclick="closeModal()">Άκυρο</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('createTopicModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('createTopicModal').style.display = 'none';
        }
    </script>
</body>
</html>
