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
$sql = "SELECT u.ID, a.PROFESSOR_ID, r.STUDENT_ID, c.SECRETARY_ID
        FROM user u
        LEFT JOIN professors a ON u.ID = a.PROFESSOR_ID
        LEFT JOIN students r ON u.ID = r.STUDENT_ID
        LEFT JOIN secretaries c ON u.ID = c.SECRETARY_ID
        WHERE u.Username = '$username' AND u.Password = '$password'";

$result = mysqli_query($con, $sql);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
$count = mysqli_num_rows($result);

// Check if the user is an admin, rescuer, or citizen based on their ID
if ($count == 1) {
    // User found in any of the tables
    session_start();
    $_SESSION['user_id'] = $row['ID'];
    if (!empty($row['PROFESSOR_ID'])) {
        $_SESSION['user_role'] = 'professors';
        header("Location: professor.php");
    } elseif (!empty($row['STUDENT_ID'])) {
        $_SESSION['user_role'] = 'students';
        header("Location: student.php");
    } elseif (!empty($row['SECRETARY_ID'])) {
        $_SESSION['user_role'] = 'secretaries';
        header("Location: secretary.php");
    }
} else {
    header("Location: login.php?error=1");
}
?>
