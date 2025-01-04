<?php
// admin.php
include 'access.php';

// Ξεκινήστε τη συνεδρία
session_start();

// Ελέγξτε αν ο χρήστης είναι συνδεδεμένος και έχει δικαιώματα γραμματείας
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'secretaries') {
    // Ανακατεύθυνση στη σελίδα σύνδεσης ή εμφάνιση μηνύματος σφάλματος
    header("Location: login.php?block=1");
    exit();
}

// Include the global menu
include 'menus/menu.php';

?>

<!DOCTYPE html>
<html lang="el">
<head>
    <!-- Ορισμός κωδικοποίησης χαρακτήρων της σελίδας -->
    <meta charset="UTF-8">

    <!-- Σύνδεση με το αρχείο στυλ -->
    <!--<link rel="stylesheet" href="lobby.css">-->
    <link rel="stylesheet" href="AllCss.css">
    <title>Καλώς ήρθατε Γραμματέα</title>
</head>
<body>
    <div class="container">
    <button class="go-back" onclick="window.location.href = 'logout.php';">Αποσύνδεση</button>
    <h1>Τι θέλεις να δεις;</h1>
        <button onclick="window.location.href = 'view_thesis.php';">Προβολή Διπλωματικών Εργασιών</button>
        <button onclick="window.location.href = 'import_data.php';">Καταχώρηση Δεδομένων</button>
    </div>
</body>
</html>
