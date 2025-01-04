<head>
    <link rel="stylesheet" href="menus/menu.css">
</head>
<nav>
    <ul>
        <li class="logo">
            <a href="view_thesis.php">
                <img src="logo.png" alt="App Logo">
            </a>
        </li>
        <li><a href="view_thesis.php" class="<?= basename($_SERVER['PHP_SELF']) == 'view_thesis.php' ? 'active' : '' ?>">Προβολή Διπλωματικών</a></li>
        <li><a href="import_data.php" class="<?= basename($_SERVER['PHP_SELF']) == 'import_data.php' ? 'active' : '' ?>">Εισαγωγή Δεδομένων</a></li>
        <li class="logout">
            <a href="logout.php">Logout</a>
        </li>
    </ul>
</nav>
