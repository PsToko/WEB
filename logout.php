<?php
// Ξεκινάμε τη συνεδρία
session_start();

// Καθαρίζουμε όλα τα δεδομένα της συνεδρίας
session_unset();

// Τερματίζουμε τη συνεδρία
session_destroy();

// Ανακατεύθυνση στη σελίδα σύνδεσης με επιπλέον παράμετρο για το logout
header("Location: login.php?logged_out=1");
exit();
?>
