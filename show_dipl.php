<?php
include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professors') {
    header("Location: login.php?block=1");
    exit();
}

if(isset($_GET['add'])==true){
    echo '<font colour="#961823"><p align="center">You have added a thesis</p></font>';
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
            $query = "SELECT thesisID, title, description, pdf FROM thesis WHERE supervisorID = ? AND status = 'under assignment'";
            $stmt = $con->prepare($query);
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            // Existing code in your topic list display section
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
                    
                    // Add an Edit button for each thesis
                    echo '<button class="edit-topic-button" onclick="openEditModal(' . $row['thesisID'] . ', \'' . htmlspecialchars($row['title']) . '\', \'' . htmlspecialchars($row['description']) . '\')">Επεξεργασία</button>';
                    echo '</div>';
                }
            }
            ?>
        </div>

        <!-- Button to add a new topic -->
        <button class="add-topic-button" onclick="openModal()">Προσθήκη Νέου Θέματος</button>
        <button class="add-topic-button" onclick="window.location.href = 'all_thesis.php';">Επιστροφή</button>


        <div class="modal" id="editTopicModal">
            <div class="modal-content">
                <h2>Επεξεργασία Θέματος</h2>
                <form id="editTopicForm" action="edit_thesis.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" id="editId" name="id">

                    <label for="editTitle">Τίτλος:</label><br>
                    <input type="text" id="editTitle" name="title" required><br><br>
                    
                    <label for="editSummary">Σύνοψη:</label><br>
                    <textarea id="editSummary" name="summary" required></textarea><br><br>
                    
                    <label for="pdf">Νέο Αρχείο (PDF) (προαιρετικά):</label><br>
                    <input type="file" id="editPdf" name="pdf" accept=".pdf"><br><br>
                    
                    <button type="submit">Αποθήκευση Αλλαγών</button>
                    <button type="button" onclick="closeEditModal()">Άκυρο</button>
                </form>
            </div>
        </div>
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

        function openEditModal(id, title, summary) {
            document.getElementById('editId').value = id;
            document.getElementById('editTitle').value = title;
            document.getElementById('editSummary').value = summary;
            document.getElementById('editTopicModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editTopicModal').style.display = 'none';
        }
    </script>
</body>
</html>
