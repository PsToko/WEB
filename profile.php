<?php
// profile.php
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

// Fetch current student information
$query = "SELECT AM, Name, Surname, Address, email, mobile, landline, Has_Thesis FROM Students WHERE Student_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $studentID);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Initialize success and error messages
$successMessage = $errorMessage = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = $_POST['address'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $landline = $_POST['landline'];

    // Update student information
    $updateQuery = "UPDATE Students SET Address = ?, email = ?, mobile = ?, landline = ? WHERE Student_ID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('sssii', $address, $email, $mobile, $landline, $studentID);

    if ($stmt->execute()) {
        $successMessage = "Profile updated successfully!";
        // Fetch the updated information
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $studentID);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
    } else {
        $errorMessage = "Error updating profile: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link rel="stylesheet" href="lobby.css">
    <style>
        form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            font-weight: bold;
            margin-top: 10px;
        }

        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .form-row input {
            width: 70%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }

        .readonly {
            background-color: #f4f4f9;
            border: none;
            cursor: not-allowed;
        }

        .success {
            color: green;
            font-weight: bold;
        }

        .error {
            color: red;
            font-weight: bold;
        }

        .edit-button {
            background-color: #2c3e50;
            color: #fff;
            font-size: 0.9em;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-left: 10px;
        }

        .edit-button:hover {
            background-color: #1a242f;
        }
    </style>
    <script>
        function toggleEdit(fieldId, button) {
            const inputField = document.getElementById(fieldId);
            if (inputField.readOnly) {
                inputField.readOnly = false;
                inputField.classList.remove('readonly');
                button.textContent = "Save";
            } else {
                document.getElementById('profileForm').submit();
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <button class="go-back" onclick="window.location.href = 'student.php';">Go Back</button>
        <h1>Your Profile</h1>

        <?php if (!empty($successMessage)): ?>
            <p class="success"><?= htmlspecialchars($successMessage) ?></p>
        <?php elseif (!empty($errorMessage)): ?>
            <p class="error"><?= htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>

        <form id="profileForm" method="POST">
            <div class="form-row">
                <label for="am">Academic ID (AM):</label>
                <input type="text" id="am" value="<?= htmlspecialchars($student['AM']) ?>" class="readonly" readonly>
            </div>

            <div class="form-row">
                <label for="name">Name:</label>
                <input type="text" id="name" value="<?= htmlspecialchars($student['Name']) ?>" class="readonly" readonly>
            </div>

            <div class="form-row">
                <label for="surname">Surname:</label>
                <input type="text" id="surname" value="<?= htmlspecialchars($student['Surname']) ?>" class="readonly" readonly>
            </div>

            <div class="form-row">
                <label for="has_thesis">Has Thesis:</label>
                <input type="text" id="has_thesis" value="<?= $student['Has_Thesis'] ? 'Yes' : 'No' ?>" class="readonly" readonly>
            </div>

            <div class="form-row">
                <label for="address">Full Address:</label>
                <input type="text" id="address" name="address" value="<?= htmlspecialchars($student['Address']) ?>" class="readonly" readonly>
                <button type="button" class="edit-button" onclick="toggleEdit('address', this)">Edit</button>
            </div>

            <div class="form-row">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" class="readonly" readonly>
                <button type="button" class="edit-button" onclick="toggleEdit('email', this)">Edit</button>
            </div>

            <div class="form-row">
                <label for="mobile">Mobile:</label>
                <input type="text" id="mobile" name="mobile" value="<?= htmlspecialchars($student['mobile']) ?>" class="readonly" readonly>
                <button type="button" class="edit-button" onclick="toggleEdit('mobile', this)">Edit</button>
            </div>

            <div class="form-row">
                <label for="landline">Landline:</label>
                <input type="text" id="landline" name="landline" value="<?= htmlspecialchars($student['landline']) ?>" class="readonly" readonly>
                <button type="button" class="edit-button" onclick="toggleEdit('landline', this)">Edit</button>
            </div>
        </form>
    </div>
</body>
</html>
