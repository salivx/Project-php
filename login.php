<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        $conn = getDBConnection();
        
        // Get user data
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Start session and store user data
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['fullname'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];

            // Get user interests
            $stmt = $conn->prepare("
                SELECT rf.field_name 
                FROM user_interests ui 
                JOIN research_fields rf ON ui.field_id = rf.field_id 
                WHERE ui.user_id = ?
            ");
            $stmt->execute([$user['user_id']]);
            $_SESSION['interests'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

            header('Location: index.php');
            exit();
        } else {
            $error = "Invalid email or password";
        }
    } catch (PDOException $e) {
        $error = "Login failed. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ResearchHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #0F2027;
            position: relative;
            overflow: hidden;
        }

        .background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #0F2027, #203A43, #2C5364);
            z-index: -2;
        }

        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .particle {
            position: absolute;
            width: 2px;
            height: 2px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            animation: float 6s infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0);
            }
            50% {
                transform: translateY(-20px) translateX(10px);
            }
        }

        .login-container {
            width: 100%;
            max-width: 1200px;
            display: flex;
            gap: 2rem;
            padding: 2rem;
            position: relative;
        }

        .login-image {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .login-image img {
            width: 100%;
            max-width: 500px;
            height: auto;
            animation: float 6s ease-in-out infinite;
        }

        .login-form {
            flex: 1;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 3rem;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }

        .header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .header h1 {
            font-size: 2.8rem;
            font-weight: 700;
            background: linear-gradient(45deg, #00f2fe, #4facfe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #b0b0b0;
            font-size: 1.1rem;
        }

        .form-group {
            margin-bottom: 1.8rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.7rem;
            color: #fff;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group i {
            position: absolute;
            left: 1rem;
            top: 2.7rem;
            color: rgba(255, 255, 255, 0.5);
        }

        .form-group input:focus {
            outline: none;
            border-color: #4facfe;
            box-shadow: 0 0 15px rgba(79, 172, 254, 0.3);
        }

        .login-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(45deg, #00f2fe, #4facfe);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 1rem;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(79, 172, 254, 0.4);
        }

        .error {
            background: rgba(255, 87, 87, 0.1);
            border: 1px solid rgba(255, 87, 87, 0.3);
            color: #ff5757;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(5px);
        }

        .success {
            background: rgba(0, 255, 127, 0.1);
            border: 1px solid rgba(0, 255, 127, 0.3);
            color: #00ff7f;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(5px);
        }

        .register-link {
            text-align: center;
            margin-top: 1.8rem;
            color: #fff;
        }

        .register-link a {
            color: #4facfe;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-left: 0.5rem;
        }

        .register-link a:hover {
            color: #00f2fe;
            text-shadow: 0 0 10px rgba(79, 172, 254, 0.4);
        }

        @media (max-width: 968px) {
            .login-container {
                flex-direction: column;
                max-width: 500px;
            }

            .login-image {
                display: none;
            }
        }

        /* Create animated particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 2px;
            height: 2px;
            background: #fff;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <div class="background"></div>
    <div class="particles">
    
    </div>

    <div class="login-container">
        <div class="login-image">
            <img src="https://cdn.pixabay.com/photo/2019/10/09/07/28/development-4536630_1280.png" alt="Research Illustration">
        </div>
        <div class="login-form">
            <div class="header">
                <h1>ResearchHub</h1>
                <p>Welcome back</p>
            </div>

            <?php if (isset($_GET['registered'])): ?>
                <div class="success">Registration successful! Please login.</div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="login-btn">Login</button>
            </form>

            <div class="register-link">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>
    </div>

    <script>
        // Create animated particles
        const particles = document.querySelector('.particles');
        const particleCount = 50;

        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            
            // Random position
            particle.style.left = Math.random() * 100 + '%';
            particle.style.top = Math.random() * 100 + '%';
            
            // Random size
            const size = Math.random() * 3;
            particle.style.width = size + 'px';
            particle.style.height = size + 'px';
            
            // Random animation duration and delay
            const duration = 3 + Math.random() * 5;
            const delay = Math.random() * 5;
            particle.style.animation = `float ${duration}s ${delay}s infinite`;
            
            particles.appendChild(particle);
        }
    </script>
</body>
</html>

