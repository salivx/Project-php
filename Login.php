<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: url('https://source.unsplash.com/random/1920x1080') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #fff;
        }
        .container {
            background: rgba(0, 0, 0, 0.7);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
            max-width: 400px;
            width: 100%;
            text-align: center;
            animation: fadeIn 0.5s ease forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        header {
            font-size: 32px;
            margin-bottom: 20px;
            color: #4CAF50;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #fff;
            text-align: left;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            border: none;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 16px;
            transition: background 0.3s, box-shadow 0.3s;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            background: rgba(255, 255, 255, 0.4);
            box-shadow: 0 0 10px rgba(76, 175, 80, 0.5);
            outline: none;
        }
        input[type="submit"] {
            width: 100%;
            padding: 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 18px;
            transition: background-color 0.3s, transform 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
            transform: translateY(-2px);
        }
        .error {
            color: #ffcc00;
            margin-top: 10px;
            font-size: 14px;
        }
        .footer {
            margin-top: 20px;
            font-size: 14px;
            color: #ddd;
        }
        .toggle-password {
            cursor: pointer;
            color: #4CAF50;
            margin-top: -30px;
            margin-bottom: 20px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>Welcome!</header>

        <?php
        session_start();
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $error = '';

        if (isset($_POST['login'])) {
            $emailV = $_POST['email'] ?? '';
            $passwordV = $_POST['password'] ?? '';

            if (empty($emailV) || empty($passwordV)) {
                $_SESSION['errorempty'] = "Please fill in all fields.";
                header("Location: Page1.php");
                exit();
            }

            if (!preg_match("/@emsi\.ma$/", $emailV)) {
                $_SESSION['erroremail'] = "Email must end with @emsi.ma.";
                header("Location: Page1.php");
                exit();
            }

            $validEmail = "user@emsi.ma";
            $validPassword = "password123";

            if ($emailV === $validEmail && $passwordV === $validPassword) {
                $_SESSION['user'] = $emailV;
                header("Location: Page1.php");
                exit();
                
            } else {
                $_SESSION['errorlogin'] = "Invalid email or password.";
                header("Location: Page1.php");
                exit();
            }
        }

        if (isset($_SESSION['errorempty'])) {
            $error = $_SESSION['errorempty'];
            unset($_SESSION['errorempty']);
        } elseif (isset($_SESSION['erroremail'])) {
            $error = $_SESSION['erroremail'];
            unset($_SESSION['erroremail']);
        } elseif (isset($_SESSION['errorlogin'])) {
            $error = $_SESSION['errorlogin'];
            unset($_SESSION['errorlogin']);
        }
        ?>

        <div class="error"><?php echo $error; ?></div>
        <form action="" method="POST">
            <label for="email">Email</label>
            <input type="text" id="email" name="email">
            
            <label for="password">Password</label>
            <input type="password" id="password" name="password">
            <span class="toggle-password" id="togglePassword">Show</span>
            
            <input type="submit" name="login" value="Login">
        </form>

        <div class="footer">
            <p>Don't have an account? <a href="Signin.php" style="color: #4CAF50;">Sign Up</a></p>
        </div>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.textContent = type === 'password' ? 'Show' : 'Hide';
        });
    </script>
</body>
</html>
