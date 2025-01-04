<?php
include 'access.php';

// Ξεκινήστε τη συνεδρία
session_start();

// Ελέγξτε αν ο χρήστης είναι συνδεδεμένος και έχει δικαιώματα καθηγητή
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professors') {
    // Ανακατευθύνετε στη σελίδα σύνδεσης ή εμφανίστε μήνυμα σφάλματος
    header("Location: login.php?block=1");
    exit();
}

// Include the global menu
include 'menus/menu.php';

?>

<!DOCTYPE html>
<html lang="el">
<head>
    <!-- Ορισμός της κωδικοποίησης χαρακτήρων της σελίδας -->
    <meta charset="UTF-8">

    <!-- Σύνδεση με το φύλλο στυλ -->
    <!--<link rel="stylesheet" href="lobby.css">-->
    <link rel="stylesheet" href="AllCss.css">
    <title>Καλώς ήρθατε Καθηγητή</title>
</head>
<body>
    <div class="container">
        <button class="go-back" onclick="window.location.href = 'logout.php';">Αποσύνδεση</button>
        <h1>Τι θέλεις να δεις;</h1>
        <button onclick="window.location.href = 'all_thesis.php';">Διπλωματικές εργασίες</button>
        <button onclick="window.location.href = 'delegation.php';">Ανάθεση διπλωματικής</button>
        <button onclick="window.location.href = 'announcements.php';">Δημιουργία Ανακοίνωσης</button>
        <button onclick="window.location.href = 'invites.php';">Προσκλήσεις</button>
        <button onclick="window.location.href = 'charts.php';">Στατιστικά</button>
    </div>
</body>
</html>
