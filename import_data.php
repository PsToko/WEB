<?php
include 'access.php';
session_start();

// Έλεγχος αν ο χρήστης είναι συνδεδεμένος και έχει το ρόλο "γραμματείας"
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'secretaries') {
    header("Location: login.php?block=1");
    exit();
}

// Συνάρτηση για τη δημιουργία τυχαίου κωδικού πρόσβασης που ξεκινά με "password" ακολουθούμενο από 4 τυχαία ψηφία
function generateRandomPassword() {
    return 'password' . rand(1000, 9999); // Χρήση πιο συνεπούς εύρους για τυχαία ψηφία
}

// Διαχείριση ανέβασμα αρχείου και ανάλυση JSON
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['json_file']) && $_FILES['json_file']['error'] === UPLOAD_ERR_OK) {
        // Επικύρωση τύπου αρχείου
        $fileType = mime_content_type($_FILES['json_file']['tmp_name']);
        if ($fileType !== 'application/json') {
            $message = 'Το αρχείο πρέπει να είναι τύπου JSON.';
        } else {
            // Ανάγνωση και αποκωδικοποίηση του αρχείου JSON
            $jsonContent = file_get_contents($_FILES['json_file']['tmp_name']);
            $data = json_decode($jsonContent, true);

            if ($data === null) {
                $message = 'Το αρχείο JSON είναι άκυρο.';
            } else {
                // Εισαγωγή φοιτητών και καθηγητών στη βάση δεδομένων
                $studentsInserted = 0;
                $professorsInserted = 0;

                // Διαχείριση φοιτητών
                if (isset($data['students'])) {
                    foreach ($data['students'] as $student) {
                        if (!isset($student['AM'], $student['Name'], $student['Surname'], $student['email'], $student['mobile'], $student['Username'])) {
                            continue; // Παράκαμψη μη έγκυρων εγγραφών
                        }

                        $password = generateRandomPassword(); // Δημιουργία τυχαίου κωδικού πρόσβασης

                        // Εισαγωγή του χρήστη στον πίνακα `user` πρώτα
                        $stmt = $con->prepare("
                            INSERT INTO user (Name, Surname, email, mobile, Username, Password, role) 
                            VALUES (?, ?, ?, ?, ?, ?, 'student')
                        ");
                        $stmt->bind_param(
                            'ssssss',
                            $student['Name'],
                            $student['Surname'],
                            $student['email'],
                            $student['mobile'],
                            $student['Username'],
                            $password
                        );

                        if ($stmt->execute()) {
                            $studentsInserted++;
                            $studentID = $con->insert_id; // Λήψη του αυτόματα παραγόμενου ID

                            // Εισαγωγή του φοιτητή στον πίνακα `students`
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

                // Διαχείριση καθηγητών
                if (isset($data['professors'])) {
                    foreach ($data['professors'] as $professor) {
                        if (!isset($professor['Name'], $professor['Surname'], $professor['email'], $professor['mobile'], $professor['Username'], $professor['Subject'])) {
                            continue; // Παράκαμψη μη έγκυρων εγγραφών
                        }

                        $password = generateRandomPassword(); // Δημιουργία τυχαίου κωδικού πρόσβασης

                        // Εισαγωγή του χρήστη στον πίνακα `user` πρώτα
                        $stmt = $con->prepare("
                            INSERT INTO user (Name, Surname, email, mobile, Username, Password, role) 
                            VALUES (?, ?, ?, ?, ?, ?, 'professor')
                        ");
                        $stmt->bind_param(
                            'ssssss',
                            $professor['Name'],
                            $professor['Surname'],
                            $professor['email'],
                            $professor['mobile'],
                            $professor['Username'],
                            $password
                        );

                        if ($stmt->execute()) {
                            $professorsInserted++;
                            $professorID = $con->insert_id; // Λήψη του αυτόματα παραγόμενου ID

                            // Εισαγωγή του καθηγητή στον πίνακα `professors`
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

                $message = "Η εισαγωγή ολοκληρώθηκε: $studentsInserted φοιτητές, $professorsInserted καθηγητές.";
            }
        }
    } else {
        $message = 'Πρέπει να επιλέξετε ένα αρχείο JSON.';
    }
}

// Include the global menu
include 'menus/menu.php';

?>


<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Καταχώρηση Δεδομένων</title>
    <link rel="stylesheet" href="AllCss.css">
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
            background-color:#6e0000;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color:#6e0000;
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
        <h1>Καταχώρηση Δεδομένων</h1>
        <form method="POST" enctype="multipart/form-data">
            <label for="json_file">Επιλέξτε ένα αρχείο JSON:</label><br>
            <input type="file" name="json_file" id="json_file" accept="application/json" required><br>
            <button type="submit">Ανέβασμα</button>
        </form>

        <?php if ($message): ?>
            <div class="message <?= strpos($message, 'completed') !== false ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>