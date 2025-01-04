
    <!-- Desktop menu structure remains unchanged -->
    <nav>
        <ul>
            <li class="logo">
                <a href="st_dipl.php">
                    <img src="logo.png" alt="App Logo">
                </a>
            </li>
            <li class="dropdown">
                <a href="st_dipl.php" class="dropbtn <?= in_array(basename($_SERVER['PHP_SELF']), ['st_dipl.php', 'your_thesis.php', 'practical.php', 'nemertes.php']) ? 'active' : '' ?>">Διπλωματική</a>
                <ul class="dropdown-content">
                    <li><a href="your_thesis.php" class="<?= basename($_SERVER['PHP_SELF']) == 'your_thesis.php' ? 'active' : '' ?>">Παράδοση</a></li>
                    <li><a href="practical.php" class="<?= basename($_SERVER['PHP_SELF']) == 'practical.php' ? 'active' : '' ?>">Πρακτικό</a></li>
                    <li><a href="nemertes.php" class="<?= basename($_SERVER['PHP_SELF']) == 'nemertes.php' ? 'active' : '' ?>">Νεμέρτης</a></li>
                </ul>
            </li>
            <li><a href="st_invitation.php" class="<?= basename($_SERVER['PHP_SELF']) == 'st_invitation.php' ? 'active' : '' ?>">Προσκλήσεις</a></li>
            <li><a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">Προφίλ</a></li>
            <li class="logout">
                <a href="logout.php">Logout</a>
            </li>
        </ul>
    </nav>

