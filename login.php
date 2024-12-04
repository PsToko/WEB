<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="login.css">
    <title>Login to our system</title>

    <style>
        body {
            position: relative;
        }

        .presentation-btn {
            text-decoration: none;
            color: #fff;
            background-color: #007BFF;
            padding: 10px 20px;
            border-radius: 5px;
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .presentation-btn:hover {
            background-color: #0056b3;
        }

        .container {
            max-width: 400px;
            margin: 100px auto;
            text-align: center;
        }
    </style>
</head>

<body>

    <!-- Thesis Presentations Button -->
    <a href="demo.php" class="presentation-btn">Thesis Presentations</a>

    <form id="loginForm" action="check_login.php" method="post">
        <div class="container">
            <h1>Login form</h1>

            <label for="uname"><b>Username</b></label>
            <input type="text" placeholder="Enter Username" name="uname" required>

            <label for="psw"><b>Password</b></label>
            <input type="password" placeholder="Enter Password" name="psw" required>

            <?php
            if (isset($_GET['error']) == true) {
                echo '<font color="#FF0000"><p align="center">Invalid username/password</p></font>';
            }
            if (isset($_GET['block']) == true) {
                echo '<font color="#FF0000"><p align="center">You need to connect to have the access</p></font>';
            }
            ?>

            <button type="submit">Login</button>
        </div>
    </form>

</body>
</html>
