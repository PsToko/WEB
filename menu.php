<?php
include 'access.php';
?>

<link rel="stylesheet" href="menus/menu.css">

<!-- Desktop Menu -->
<nav class="desktop-menu">
    <?php
    if ($_SESSION['user_role'] === 'students') {
        include 'menus/st_menu.php';
    } elseif ($_SESSION['user_role'] === 'secretaries') {
        include 'menus/sec_menu.php';
    } elseif ($_SESSION['user_role'] === 'professors') {
        include 'menus/pr_menu.php';
    } else {
        header("Location: login.php?block=1");
        exit();
    }
    ?>
</nav>

<!-- Mobile Menu -->
<header class="mobile-menu-container">
    <a href="st_dipl.php" class="logo-link">
        <img src="logo.png" alt="Logo">
    </a>
    <button class="hamburger-menu" aria-label="Toggle Menu">☰</button>
</header>

<nav class="mobile-dropdown-menu">
    <ul>
        <?php
        // Dynamically include menu items for mobile users
        if ($_SESSION['user_role'] === 'students') {
            include 'menus/st_menu.php';
        } elseif ($_SESSION['user_role'] === 'secretaries') {
            include 'menus/sec_menu.php';
        } elseif ($_SESSION['user_role'] === 'professors') {
            include 'menus/pr_menu.php';
        } else {
            header("Location: login.php?block=1");
            exit();
        }
        ?>
    </ul>
</nav>

<script src="menus/menu.js" defer></script>
