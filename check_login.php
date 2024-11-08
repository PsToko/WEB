<?php
include 'access.php';

$username = $_POST['uname'];
$password = $_POST['psw'];

// To prevent SQL injection
$username = stripcslashes($username);
$password = stripcslashes($password);
$username = mysqli_real_escape_string($con, $username);
$password = mysqli_real_escape_string($con, $password);

// Check for user in any of the tables
$sql = "SELECT u.id, a.pr_id, r.st_id, c.sec_id
        FROM users u
        LEFT JOIN professor a ON u.id = a.pr_id
        LEFT JOIN student r ON u.id = r.st_id
        LEFT JOIN secretary c ON u.id = c.sec_id
        WHERE u.username = '$username' AND u.password = '$password'";

$result = mysqli_query($con, $sql);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
$count = mysqli_num_rows($result);

// Check if the user is an admin, rescuer, or citizen based on their ID
if ($count == 1) {
    // User found in any of the tables
    session_start();
    $_SESSION['user_id'] = $row['id'];
    if (!empty($row['pr_id'])) {
        $_SESSION['user_role'] = 'professor';
        header("Location: professor.php");
    } elseif (!empty($row['st_id'])) {
        $_SESSION['user_role'] = 'student';
        header("Location: student.php");
    } elseif (!empty($row['sec_id'])) {
        $_SESSION['user_role'] = 'secretary';
        header("Location: secretary.php");
    }
} else {
    header("Location: login.php?error=1");
}
?>
