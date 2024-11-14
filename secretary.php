<?php
// admin.php
include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'secretaries') {
    // Redirect to login page or display an error message
    header("Location: login.php?block=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Setting the pages character encoding -->
    <meta charset="UTF-8">

    <!-- Link to my stylesheet -->
    <link rel="stylesheet" href="lobby.css">
    <title>Welcome Professor</title>
</head>
<body>
    <div class="container">
    <button class="go-back" onclick="window.location.href = 'logout.php';">Log Out</button>
    <h1>What do you want to see?</h1>
        <button onclick="window.location.href = 'all_dipl.php';">Εμφάνιση διπλωματικών</button>
        <button onclick="window.location.href = 'data.php';">Εισαγωγή δεδομένων</button>
    </div>
</body>
</html>