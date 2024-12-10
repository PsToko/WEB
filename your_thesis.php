<?php
include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has the required role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'students') {
    header("Location: login.php?block=1");
    exit();
}

$studentID = $_SESSION['user_id'];

// Handle form submission for file upload or adding a link
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['examination_id'])) {
        $examinationID = $_POST['examination_id'];

        // Handle PDF upload
        if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] == UPLOAD_ERR_OK) {
            $pdfFileName = basename($_FILES['pdf']['name']);
            $uploadDir = 'uploads/';
            $uploadFilePath = $uploadDir . $pdfFileName;

            // Ensure the upload directory exists
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Move the uploaded file
            if (move_uploaded_file($_FILES['pdf']['tmp_name'], $uploadFilePath)) {
                $query = "UPDATE examination SET st_thesis = ? WHERE StudentID = ? AND ExaminationID = ?";
                if ($stmt = $con->prepare($query)) {
                    $stmt->bind_param('sii', $pdfFileName, $studentID, $examinationID);
                    if ($stmt->execute()) {
                        echo "File uploaded successfully.";
                    } else {
                        echo "Error: " . $stmt->error;
                    }
                    $stmt->close();
                }
            } else {
                echo "Error uploading file.";
            }
        }
      
        // Handle multiple link additions
        if (!empty($_POST['link']) && is_array($_POST['link'])) {
            foreach ($_POST['link'] as $link) {
                if (!empty($link)) {
                    $query = "INSERT INTO links (StudentID, ExaminationID, link) VALUES (?, ?, ?)";
                    if ($stmt = $con->prepare($query)) {
                        $stmt->bind_param('iis', $studentID, $examinationID, $link);
                        if (!$stmt->execute()) {
                            echo "Error: " . $stmt->error;
                        }
                        $stmt->close();
                    }
                }
            }
        }

    }
}

// Fetch examinations associated with the logged-in student
$query = "
    SELECT 
        e.ExaminationID, 
        e.examinationDate, 
        t.Title, 
        e.st_thesis
    FROM 
        examination e
    JOIN 
        thesis t 
    ON 
        e.thesisID = t.thesisID
    WHERE 
        e.StudentID = ?";
$examinations = [];
if ($stmt = $con->prepare($query)) {
    $stmt->bind_param('i', $studentID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $examinations[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="st_thesis.css">
    <title>Upload File and Add Link</title>
    <script>
        // Function to dynamically add more link input fields
        function addLinkField() {
            const linkContainer = document.getElementById('link-container');
            const newField = document.createElement('div');
            newField.className = 'link-field';
            newField.innerHTML = `
                <label for="link">Link:</label>
                <input type="url" name="link[]" placeholder="https://example.com" pattern="https?://.+" required>
                <button type="button" onclick="removeLinkField(this)">Remove</button>
                <br><br>
            `;
            linkContainer.appendChild(newField);
        }

        // Function to remove a link input field
        function removeLinkField(button) {
            const linkField = button.parentNode;
            linkField.remove();
        }
    </script>
</head>
<body>
    <h1>Upload File for Examination</h1>
    <?php if (empty($examinations)): ?>
        <p>No examinations found for your ID.</p>
    <?php else: ?>
        <form action="" method="post" enctype="multipart/form-data">
            <label for="examination_id">Select Examination:</label>
            <select name="examination_id" id="examination_id" required>
                <?php foreach ($examinations as $exam): ?>
                    <option value="<?= $exam['ExaminationID'] ?>">
                        <?= htmlspecialchars($exam['Title']) ?> (<?= htmlspecialchars($exam['examinationDate']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>

            <?php 
            if (!empty($examinations)): 
                foreach ($examinations as $exam):
                    if (!empty($exam['st_thesis'])): ?>
                        <p>
                            For examination "<strong><?= htmlspecialchars($exam['Title']) ?></strong>" 
                            there is already a file uploaded: <strong><?= htmlspecialchars($exam['st_thesis']) ?></strong>.
                            You can upload a new file to replace it.
                        </p>
                    <?php 
                    endif;
                endforeach;
            endif;
            ?>

            <label for="pdf">Upload PDF:</label>
            <input type="file" name="pdf" id="pdf" accept="application/pdf">
            <br><br>

            <!-- Empty link container -->
            <div id="link-container"></div>
            <button type="button" onclick="addLinkField()">Add More Links</button>
            <br><br>

            <button type="submit">Submit</button>

            <button type="button" onclick="window.location.href = 'st_dipl.php';">Return</button>
            </form>
    <?php endif; ?>
</body>
</html>

