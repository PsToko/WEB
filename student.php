<?php
// student.php
include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has student privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'students') {
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
    <title>Welcome Student</title>
</head>
<body>
    <div class="container">
        <button class="go-back" onclick="window.location.href = 'logout.php';">Log Out</button>
        <h1>What do you want to see?</h1>
        <button onclick="window.location.href = 'st_dipl.php';">Εμφάνιση διπλωματικής</button>
        <button onclick="window.location.href = 'profile.php';">Προφίλ</button>
        <button onclick="window.location.href = 'st_invitation.php';">Invite Professors to Committee</button>
    </div>
</body>
</html>
