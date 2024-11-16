
<?php
session_start();
require_once 'config.php';

try {
    $interests_sql = "SELECT * FROM research_interests ORDER BY category, name";
    $interests_stmt = $pdo->query($interests_sql);
    $interests = $interests_stmt->fetchAll();

    
    $grouped_interests = [];
    foreach ($interests as $interest) {
        $grouped_interests[$interest['category']][] = $interest;
    }
} catch (PDOException $e) {
    $error_message = "Failed to fetch research interests. Please try again later.";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $institution = trim($_POST['institution']);
    $selected_interests = $_POST['interests'] ?? [];
    
    $errors = [];
    
    // Validate username
    if (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters long";
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores";
    }
    
    // Check if username exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Username already exists";
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Email already exists";
    }
    
    // Validate password
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Validate interests
    if (empty($selected_interests)) {
        $errors[] = "Please select at least one research interest";
    }
    
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password, full_name, institution) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $email, $hashed_password, $full_name, $institution]);
            
            $user_id = $pdo->lastInsertId();
            
            // Insert user interests
            $interest_sql = "INSERT INTO user_interests (user_id, interest_id) VALUES (?, ?)";
            $interest_stmt = $pdo->prepare($interest_sql);
            foreach ($selected_interests as $interest_id) {
                $interest_stmt->execute([$user_id, $interest_id]);
            }
            
            $pdo->commit();
            $_SESSION['success_message'] = "Registration successful! Please login.";
            header("Location: Login.php");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join ResearchConnect - Next-Gen Research Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #2e3192;
            --secondary-color: #00ff9d;
            --dark: #0a0a1a;
            --light: #ffffff;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--dark);
            color: var(--light);
            min-height: 100vh;
        }

        .loading-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: var(--secondary-color);
            z-index: 9999;
            transition: width 1s ease;
        }

        .navbar {
            background: rgba(10, 10, 26, 0.95);
            backdrop-filter: blur(10px);
        }

        .card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
        }

        .card-header {
            background: rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--light);
            border-radius: 10px;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--secondary-color);
            color: var(--light);
            box-shadow: 0 0 0 0.25rem rgba(0, 255, 157, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #4a4eff);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #4a4eff, var(--primary-color));
            transform: translateY(-2px);
        }

        .interest-checkbox {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .interest-checkbox:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .tech-circles {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
            overflow: hidden;
        }

        .tech-circle {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), #4a4eff);
            filter: blur(60px);
            opacity: 0.15;
        }

        .tech-circle:nth-child(1) {
            width: 600px;
            height: 600px;
            top: -200px;
            right: -200px;
        }

        .tech-circle:nth-child(2) {
            width: 500px;
            height: 500px;
            bottom: -150px;
            left: -150px;
        }
    </style>
</head>
<body>
    <div class="loading-bar"></div>
    
    <div class="tech-circles">
        <div class="tech-circle"></div>
        <div class="tech-circle"></div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-braces"></i> ResearchConnect
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="Login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="register.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center mb-0">
                            <i class="bi bi-person-plus me-2"></i>
                            Join ResearchConnect
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" id="registerForm">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="username" name="username" 
                                               placeholder="Username" required
                                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                                        <label for="username">Username</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="email" 
                                               placeholder="Email" required
                                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                        <label for="email">Email</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="password" name="password" 
                                               placeholder="Password" required>
                                        <label for="password">Password</label>
                                    </div>
                                    <div id="passwordStrength" class="progress mt-2" style="height: 5px;">
                                        <div class="progress-bar" role="progressbar"></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="confirm_password" 
                                               name="confirm_password" placeholder="Confirm Password" required>
                                        <label for="confirm_password">Confirm Password</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="full_name" name="full_name" 
                                               placeholder="Full Name" required
                                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                                        <label for="full_name">Full Name</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="institution" name="institution" 
                                               placeholder="Institution" required
                                               value="<?php echo isset($_POST['institution']) ? htmlspecialchars($_POST['institution']) : ''; ?>">
                                        <label for="institution">Institution</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <h5 class="mb-3">
                                        <i class="bi bi-diagram-3 me-2"></i>
                                        Research Interests
                                    </h5>
                                    <?php foreach ($grouped_interests as $category => $interests): ?>
                                        <div class="mb-4">
                                            <h6 class="mb-3"><?php echo htmlspecialchars($category); ?></h6>
                                            <div class="row g-3">
                                                <?php foreach ($interests as $interest): ?>
                                                    <div class="col-md-6">
                                                        <div class="interest-checkbox">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" 
                                                                       name="interests[]" 
                                                                       value="<?php echo $interest['id']; ?>" 
                                                                       id="interest_<?php echo $interest['id']; ?>"
                                                                       <?php echo (isset($_POST['interests']) && in_array($interest['id'], $_POST['interests'])) ? 'checked' : ''; ?>>
                                                                <label class="form-check-label" for="interest_<?php echo $interest['id']; ?>">
                                                                    <?php echo htmlspecialchars($interest['name']); ?>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="col-12">
                                    <button type="submit" name="register" class="btn btn-primary w-100">
                                        <i class="bi bi-person-plus me-2"></i>
                                        Create Account
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            Already have an account? <a href="Login.php" class="text-decoration-none">Login here</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Loading bar animation
        document.addEventListener('DOMContentLoaded', function() {
            const loadingBar = document.querySelector('.loading-bar');
            loadingBar.style.width = '100%';
            setTimeout(() => {
                loadingBar.style.opacity = '0';
            }, 1000);
        });

        
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const progressBar = document.querySelector('#passwordStrength .progress-bar');
            let strength = 0;

            if (password.length >= 8) strength += 25;
            if (password.match(/[A-Z]/)) strength += 25;
            if (password.match(/[a-z]/)) strength += 25;
            if (password.match(/[0-9]/)) strength += 25;

            progressBar.style.width = strength + '%';
            
            if (strength <= 25) {
                progressBar.className = 'progress-bar bg-danger';
            } else if (strength <= 50) {
                progressBar.className = 'progress-bar bg-warning';
            } else if (strength <= 75) {
                progressBar.className = 'progress-bar bg-info';
            } else {
                progressBar.className = 'progress-bar bg-success';
            }
        });
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const interests = document.querySelectorAll('input[name="interests[]"]:checked');

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }

            if (interests.length === 0) {
                e.preventDefault();
                alert('Please select at least one research interest!');
                return;
            }
        });
    </script>
</body>
</html>
