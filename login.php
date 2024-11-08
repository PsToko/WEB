<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="login.css">
    <title>Login to our system</title>

    <style>
        .signup-container {
            display: none;
        }
    </style>
</head>

<body>

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
            if(isset($_GET['block'])==true){
                echo '<font colour="#FF0000"><p align="center">You need to connect to have the access</p></font>';
             }
            ?>

            <button type="submit">Login</button>
        </div>

    </form>
 
</body>
</html>