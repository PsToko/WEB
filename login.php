<!DOCTYPE html>
<html lang="el">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Σύνδεση στο Σύστημα μας</title>

    <link rel="stylesheet" href="AllCss.css">
</head>

<body>

    <!-- Κουμπί Παρουσιάσεων Διατριβής -->
    <a href="demo.php" class="golden_button">Ανακοινώσεις Διπλωματικών</a>

    <!-- Φόρμα Σύνδεσης -->
    <form id="loginForm" action="check_login.php" method="post">
        <div class="container">
            <h1>Σύνδεση</h1>

            <label for="uname"><b>Όνομα Χρήστη</b></label>
            <input type="text" placeholder="Εισάγετε Όνομα Χρήστη" name="uname" id="uname" required>

            <label for="psw"><b>Κωδικός Πρόσβασης</b></label>
            <input type="password" placeholder="Εισάγετε Κωδικό Πρόσβασης" name="psw" id="psw" required>

            <?php if (isset($_GET['error'])): ?>
                <div class="error">Μη έγκυρο όνομα χρήστη/κωδικός πρόσβασης</div>
            <?php endif; ?>

            <?php if (isset($_GET['block'])): ?>
                <div class="error">Πρέπει να συνδεθείτε για να αποκτήσετε πρόσβαση</div>
            <?php endif; ?>

            <button type="submit">Σύνδεση</button>
        </div>
    </form>

</body>
</html>