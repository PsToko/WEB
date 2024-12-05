<?php
include 'access.php';
session_start();

// Check if the user is logged in and has the role of "secretariat"
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'secretaries') {
    header("Location: login.php?block=1");
    exit();
}

// Function to generate random password starting with "password" followed by 4 random digits
function generateRandomPassword() {
    return 'password' . rand(0000, 9999);
}

// Function to get the next available ID for students or professors
function getNextID($table, $column) {
    global $con;
    $stmt = $con->prepare("SELECT MAX($column) + 1 AS next_id FROM $table");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['next_id'] ?: 1; // Default to 1 if the table is empty
}

// Handle file upload and JSON parsing
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['json_file']) && $_FILES['json_file']['error'] === UPLOAD_ERR_OK) {
        // Validate file type
        $fileType = mime_content_type($_FILES['json_file']['tmp_name']);
        if ($fileType !== 'application/json') {
            $message = 'The file must be a JSON type.';
        } else {
            // Read and decode JSON file
            $jsonContent = file_get_contents($_FILES['json_file']['tmp_name']);
            $data = json_decode($jsonContent, true);

            if ($data === null) {
                $message = 'The JSON file is invalid.';
            } else {
                // Insert students and professors into the database
                $studentsInserted = 0;
                $professorsInserted = 0;

                // Handle students
                if (isset($data['students'])) {
                    foreach ($data['students'] as $student) {
                        if (!isset($student['AM'], $student['Name'], $student['Surname'], $student['email'], $student['mobile'], $student['Username'])) {
                            continue; // Skip invalid entries
                        }

                        $studentID = getNextID('students', 'Student_ID'); // Generate unique Student_ID
                        $password = generateRandomPassword(); // Generate random password

                        // Insert the user into the `user` table first
                        $stmt = $con->prepare("
                            INSERT INTO user (ID, Name, Surname, email, mobile, Username, Password, role) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, 'student')
                        ");
                        $stmt->bind_param(
                            'issssss',
                            $studentID,
                            $student['Name'],
                            $student['Surname'],
                            $student['email'],
                            $student['mobile'],
                            $student['Username'],
                            $password
                        );

                        if ($stmt->execute()) {
                            $studentsInserted++;

                            // Now insert the student into the `students` table
                            $stmtStudent = $con->prepare("
                                INSERT INTO students (Student_ID, AM, Name, Surname, Has_Thesis, Address, email, mobile, landline) 
                                VALUES (?, ?, ?, ?, 0, ?, ?, ?, ?)
                            ");
                            $stmtStudent->bind_param(
                                'iissssss',
                                $studentID,
                                $student['AM'],
                                $student['Name'],
                                $student['Surname'],
                                $student['Address'],
                                $student['email'],
                                $student['mobile'],
                                $student['landline']
                            );
                            $stmtStudent->execute();
                            $stmtStudent->close();
                        }
                        $stmt->close();
                    }
                }

                // Handle professors
                if (isset($data['professors'])) {
                    foreach ($data['professors'] as $professor) {
                        if (!isset($professor['Name'], $professor['Surname'], $professor['email'], $professor['mobile'], $professor['Username'], $professor['Subject'])) {
                            continue; // Skip invalid entries
                        }

                        $professorID = getNextID('professors', 'Professor_ID'); // Generate unique Professor_ID
                        $password = generateRandomPassword(); // Generate random password

                        // Insert the user into the `user` table first
                        $stmt = $con->prepare("
                            INSERT INTO user (ID, Name, Surname, email, mobile, Username, Password, role) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, 'professor')
                        ");
                        $stmt->bind_param(
                            'issssss',
                            $professorID,
                            $professor['Name'],
                            $professor['Surname'],
                            $professor['email'],
                            $professor['mobile'],
                            $professor['Username'],
                            $password
                        );

                        if ($stmt->execute()) {
                            $professorsInserted++;

                            // Now insert the professor into the `professors` table
                            $stmtProfessor = $con->prepare("
                                INSERT INTO professors (Professor_ID, Name, Surname, Subject, email, mobile) 
                                VALUES (?, ?, ?, ?, ?, ?)
                            ");
                            $stmtProfessor->bind_param(
                                'isssss',
                                $professorID,
                                $professor['Name'],
                                $professor['Surname'],
                                $professor['Subject'],
                                $professor['email'],
                                $professor['mobile']
                            );
                            $stmtProfessor->execute();
                            $stmtProfessor->close();
                        }
                        $stmt->close();
                    }
                }

                $message = "Insertion completed: $studentsInserted students, $professorsInserted professors.";
            }
        }
    } else {
        $message = 'You must select a JSON file.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Entry</title>
    <link rel="stylesheet" href="dipl.css">
    <style>
        .container {
            max-width: 600px;
            margin: 50px auto;
            text-align: center;
        }

        form {
            margin: 20px 0;
        }

        input[type="file"] {
            margin-bottom: 20px;
        }

        button {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            margin: 20px 0;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f4f4f9;
            border-radius: 5px;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Data Entry</h1>
        <form method="POST" enctype="multipart/form-data">
            <label for="json_file">Select a JSON file:</label><br>
            <input type="file" name="json_file" id="json_file" accept="application/json" required><br>
            <button type="submit">Upload</button>
        </form>

        <?php if ($message): ?>
            <div class="message <?= strpos($message, 'completed') !== false ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <button onclick="window.location.href = 'secretary.php';">Return</button>
    </div>
</body>
</html>