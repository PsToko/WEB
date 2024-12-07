<?php
include 'access.php';
session_start();

// Check if the user is logged in and has professor privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professors') {
    header("Location: login.php?block=1");
    exit();
}

$successMessage = '';
$errorMessage = '';

// Handle form submission to create an announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['thesisID'])) {
    $thesisID = $_POST['thesisID'];

    // Fetch thesis and examination details
    $query = "
        SELECT 
            t.title AS thesisTitle, 
            e.examinationDate, 
            e.examinationMethod, 
            e.location
        FROM Thesis t
        INNER JOIN Examination e ON t.thesisID = e.thesisID
        WHERE t.thesisID = ? AND t.supervisorID = ?
    ";
    $stmt = $con->prepare($query);
    $stmt->bind_param('ii', $thesisID, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        $announcementText = sprintf(
            "The examination for the thesis titled \"%s\" will be held on %s. The examination method is %s, and it will take place at %s.",
            $result['thesisTitle'],
            $result['examinationDate'],
            $result['examinationMethod'],
            $result['location']
        );

        // Insert announcement into the database
        $insertQuery = "
            INSERT INTO Announcements (thesisID, createdBy, announcementText, examinationDate, examinationMethod, location)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        $insertStmt = $con->prepare($insertQuery);
        $insertStmt->bind_param(
            'iissss',
            $thesisID,
            $_SESSION['user_id'],
            $announcementText,
            $result['examinationDate'],
            $result['examinationMethod'],
            $result['location']
        );

        if ($insertStmt->execute()) {
            $successMessage = "Announcement created successfully!";
            // Redirect to the same page to prevent duplicate form submissions
            header("Location: announcements.php?success=1");
            exit();
        } else {
            $errorMessage = "Error creating announcement: " . $insertStmt->error;
        }
    } else {
        $errorMessage = "Thesis or examination information is incomplete.";
    }
}

// Check for success message after a redirect
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $successMessage = "Announcement created successfully!";
}

// Fetch theses with complete examination information and no existing announcements
$query = "
    SELECT 
        t.thesisID, 
        t.title, 
        e.examinationDate, 
        e.examinationMethod, 
        e.location 
    FROM Thesis t
    INNER JOIN Examination e ON t.thesisID = e.thesisID
    WHERE t.supervisorID = ? 
    AND e.examinationDate IS NOT NULL 
    AND e.examinationMethod IS NOT NULL 
    AND e.location IS NOT NULL
    AND t.thesisID NOT IN (SELECT thesisID FROM Announcements)
";
$stmt = $con->prepare($query);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$theses = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Announcement</title>
    <link rel="stylesheet" href="lobby.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
        }

        .announcement-form {
            margin-top: 20px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .success {
            color: green;
            font-weight: bold;
        }

        .error {
            color: red;
            font-weight: bold;
        }

        textarea {
            width: 100%;
            height: 100px;
            margin: 10px 0;
        }

        button {
            padding: 10px 20px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        // Update the preview text dynamically
        function updatePreview() {
            const thesisSelect = document.getElementById("thesisID");
            const preview = document.getElementById("announcementPreview");
            const thesisDetails = thesisSelect.selectedOptions[0].dataset;

            if (thesisDetails.title) {
                preview.innerText = `The examination for the thesis titled "${thesisDetails.title}" will be held on ${thesisDetails.examinationDate}. The examination method is ${thesisDetails.examinationMethod}, and it will take place at ${thesisDetails.location}.`;
            } else {
                preview.innerText = "Select a thesis to see the generated announcement preview.";
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Create Examination Announcement</h1>

        <!-- Success/Error Messages -->
        <?php if (!empty($successMessage)): ?>
            <p class="success"><?= htmlspecialchars($successMessage) ?></p>
        <?php elseif (!empty($errorMessage)): ?>
            <p class="error"><?= htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>

        <!-- Announcement Form -->
        <?php if ($theses->num_rows > 0): ?>
            <form method="POST" class="announcement-form">
                <label for="thesisID">Select Thesis:</label>
                <select name="thesisID" id="thesisID" onchange="updatePreview()" required>
                    <option value="" disabled selected>Select a thesis</option>
                    <?php while ($row = $theses->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['thesisID']) ?>" 
                                data-title="<?= htmlspecialchars($row['title']) ?>"
                                data-examination-date="<?= htmlspecialchars($row['examinationDate']) ?>"
                                data-examination-method="<?= htmlspecialchars($row['examinationMethod']) ?>"
                                data-location="<?= htmlspecialchars($row['location']) ?>">
                            <?= htmlspecialchars($row['title']) ?> - <?= htmlspecialchars($row['examinationDate']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <p><strong>Generated Announcement Preview:</strong></p>
                <p id="announcementPreview">Select a thesis to see the generated announcement preview.</p>

                <button type="submit">Create Announcement</button>
            </form>
        <?php else: ?>
            <p>No theses with complete examination information available for announcements.</p>
        <?php endif; ?>

        <button onclick="window.location.href='professor.php';">Go Back</button>
    </div>
</body>
</html>
