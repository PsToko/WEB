<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="login.css">
    <title>Login to Our System</title>

    <style>
        /* Body background and font */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to bottom, #eaf4fc, #ffffff);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Thesis Presentation Button */
        .presentation-btn {
            text-decoration: none;
            color: #fff;
            background-color: #0056b3;
            padding: 12px 20px;
            border-radius: 5px;
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .presentation-btn:hover {
            background-color: #004080;
            transform: translateY(-2px);
        }

        /* Container for the login form */
        .container {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            width: 350px;
            text-align: center;
            animation: fadeIn 0.5s ease;
        }

        /* Heading style */
        h1 {
            font-family: 'Pacifico', cursive;
            color: #0056b3;
            margin-bottom: 20px;
            font-size: 1.8rem;
        }

        /* Label styles */
        label {
            display: block;
            text-align: left;
            margin: 15px 0 5px;
            font-weight: bold;
            color: #333;
            font-size: 0.9rem;
        }

        /* Input styles */
        input {
            width: 100%;
            padding: 12px;
            margin: 5px 0 20px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            background-color: #f9f9f9;
            transition: background-color 0.2s ease, border 0.2s ease;
        }

        input:focus {
            border-color: #0056b3;
            background-color: #fff;
            outline: none;
        }

        /* Button styles */
        button {
            width: 100%;
            background-color: #0056b3;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        button:hover {
            background-color: #004080;
            transform: translateY(-1px);
        }

        button:active {
            background-color: #003366;
            transform: translateY(1px);
        }

        /* Error message styles */
        .error {
            color: #ff0000;
            font-size: 0.9rem;
            margin-top: 10px;
        }

        /* Animation for fade-in effect */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
</head>

<body>

    <!-- Thesis Presentations Button -->
    <a href="demo.php" class="presentation-btn">Thesis Presentations</a>

    <!-- Login Form -->
    <form id="loginForm" action="check_login.php" method="post">
        <div class="container">
            <h1>Login</h1>

            <label for="uname"><b>Username</b></label>
            <input type="text" placeholder="Enter Username" name="uname" id="uname" required>

            <label for="psw"><b>Password</b></label>
            <input type="password" placeholder="Enter Password" name="psw" id="psw" required>

            <?php if (isset($_GET['error'])): ?>
                <div class="error">Invalid username/password</div>
            <?php endif; ?>

            <?php if (isset($_GET['block'])): ?>
                <div class="error">You need to connect to have access</div>
            <?php endif; ?>

            <button type="submit">Login</button>
        </div>
    </form>

</body>
</html>