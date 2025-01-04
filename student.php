<?php
// student.php
include 'access.php';

// Ξεκινάμε την συνεδρία
session_start();

// Ελέγχουμε αν ο χρήστης είναι συνδεδεμένος και έχει δικαιώματα φοιτητή
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'students') {
    // Ανακατευθύνουμε στη σελίδα σύνδεσης ή εμφανίζουμε μήνυμα σφάλματος
    header("Location: login.php?block=1");
    exit();
}

// Include the global menu
include 'menus/menu.php';

?>

<!DOCTYPE html>
<html lang="el">
<head>
    <!-- Ορισμός του χαρακτήρα της σελίδας -->
    <meta charset="UTF-8">

    <!-- Σύνδεση με το αρχείο CSS -->
    <!--<link rel="stylesheet" href="lobby.css">-->
    <link rel="stylesheet" href="AllCss.css">

    <title>Καλώς ήρθες Φοιτητή</title>
</head>
<body>
    <div class="container">
        <button class="go-back" onclick="window.location.href = 'logout.php';">Αποσύνδεση</button>
        <h1>Τι θέλεις να δεις;</h1>
        <button onclick="window.location.href = 'st_dipl.php';">Εμφάνιση διπλωματικής</button>
        <button onclick="window.location.href = 'profile.php';">Προφίλ</button>
        <button onclick="window.location.href = 'st_invitation.php';">Πρόσκληση σε Καθηγητή</button>
    </div>
</body>
</html>