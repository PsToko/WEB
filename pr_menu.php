<head>
    <link rel="stylesheet" href="menus/menu.css">
</head>
<nav>
    <ul>
        <li class="logo">
            <a href="all_thesis.php">
                <img src="logo.png" alt="App Logo">
            </a>
        </li>
        <li class="dropdown">
            <a href="all_thesis.php" class="dropbtn <?= in_array(basename($_SERVER['PHP_SELF']), ['all_thesis.php', 'show_dipl.php', 'pr_practical.php']) ? 'active' : '' ?>">Διπλωματικές Εργασίες</a>
            <ul class="dropdown-content">
                <li><a href="show_dipl.php" class="<?= basename($_SERVER['PHP_SELF']) == 'show_dipl.php' ? 'active' : '' ?>">Δημιουργία κ' Επεξεργασία</a></li>
                <li><a href="pr_practical.php" class="<?= basename($_SERVER['PHP_SELF']) == 'pr_practical.php' ? 'active' : '' ?>">Πρακτικό</a></li>
            </ul>
        </li>
        <li class="dropdown">
            <a href="delegation.php" class="dropbtn <?= in_array(basename($_SERVER['PHP_SELF']), ['delegation.php', 'show_assign.php']) ? 'active' : '' ?>">Ανάθεση Διπλωματικής</a>
            <ul class="dropdown-content">
                <li><a href="show_assign.php" class="<?= basename($_SERVER['PHP_SELF']) == 'show_assign.php' ? 'active' : '' ?>">Προβολή Ανατεθημένων</a></li>
            </ul>
        </li>
        <li><a href="invites.php" class="<?= basename($_SERVER['PHP_SELF']) == 'invites.php' ? 'active' : '' ?>">Προσκλήσεις</a></li>     
        <li><a href="charts.php" class="<?= basename($_SERVER['PHP_SELF']) == 'charts.php' ? 'active' : '' ?>">Στατιστικά</a></li>
        <li><a href="announcements.php" class="<?= basename($_SERVER['PHP_SELF']) == 'announcements.php' ? 'active' : '' ?>">Ανακοίνωση</a></li> 
        <li class="logout">
            <a href="logout.php">Logout</a>
        </li>
    </ul>
</nav>
