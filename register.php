<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

// Fetch categories and fields for interests selection
try {
    $conn = getDBConnection();
    $stmt = $conn->query("
        SELECT mc.category_id, mc.category_name, mc.category_icon,
               rf.field_id, rf.field_name
        FROM main_categories mc
        LEFT JOIN research_fields rf ON mc.category_id = rf.category_id
        ORDER BY mc.category_name, rf.field_name
    ") ;
    
    $categories = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!isset($categories[$row['category_id']])) {
            $categories[$row['category_id']] = [
                'name' => $row['category_name'],
                'icon' => $row['category_icon'],
                'fields' => []
            ];
        }
        if ($row['field_id']) {
            $categories[$row['category_id']]['fields'][] = [
                'id' => $row['field_id'],
                'name' => $row['field_name']
            ];
        }
    }
} catch(PDOException $e) {
    $error = "System error. Please try again later.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $interests = isset($_POST['interests']) ? $_POST['interests'] : [];

    //  validation
    if (empty($fullname) || empty($email) || empty($phone) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (empty($interests)) {
        $error = "Please select at least one interest.";
    } else {
        try {
            $conn->beginTransaction();

            // Check if email or phone already exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR phone_number = ?");
            $stmt->execute([$email, $phone]);
            if ($stmt->fetchColumn() > 0) {
                throw new PDOException("Email or phone number already registered.");
            }

            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone_number, password) VALUES (?, ?, ?, ?)");
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt->execute([$fullname, $email, $phone, $password_hash]);
            
            $user_id = $conn->lastInsertId();

            // Insert interests
            $stmt = $conn->prepare("INSERT INTO user_interests (user_id, field_id) VALUES (?, ?)");
            foreach ($interests as $field_id) {
                $stmt->execute([$user_id, $field_id]);
            }

            $conn->commit();
            header('Location: login.php?registered=1');
            exit();
        } catch (PDOException $e) {
            $conn->rollBack();
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ResearchHub</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            color: #e0e0e0;
        }

        .register-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            width: 100%;
            max-width: 600px;
            padding: 2.5rem;
            position: relative;
            animation: float 6s ease-in-out infinite;
        }

        .header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .header h1 {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(45deg, #00f2fe, #4facfe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .header p {
            color: #b0b0b0;
            font-size: 1.1rem;
        }

        .form-group {
            margin-bottom: 1.8rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.7rem;
            color: #fff;
            font-weight: 500;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }

        .form-group input {
            width: 100%;
            padding: 1rem 1.2rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4facfe;
            box-shadow: 0 0 15px rgba(79, 172, 254, 0.3);
            background: rgba(255, 255, 255, 0.15);
        }

        .interests-section {
            margin-top: 2.5rem;
        }

        .interests-section h3 {
            color: #fff;
            margin-bottom: 1.5rem;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .category {
            margin-bottom: 1.8rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 1.2rem;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .category:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .category-header {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1.2rem;
            color: #4facfe;
        }

        .fields-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
        }

        .field-item {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .field-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: #4facfe;
            cursor: pointer;
        }

        .field-item label {
            color: #fff;
            font-size: 0.95rem;
            cursor: pointer;
        }

        .submit-btn {
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
        }

        .submit-btn:hover {
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

        .login-link {
            text-align: center;
            margin-top: 1.8rem;
            color: #fff;
        }

        .login-link a {
            color: #4facfe;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            color: #00f2fe;
            text-shadow: 0 0 10px rgba(79, 172, 254, 0.4);
        }

        @media (max-width: 640px) {
            body {
                padding: 1rem;
            }

            .register-container {
                padding: 1.5rem;
            }

            .header h1 {
                font-size: 2.2rem;
            }

            .fields-grid {
                grid-template-columns: 1fr;
            }
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
            100% {
                transform: translateY(0px);
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="header">
            <h1>ResearchHub</h1>
            <p>Join our research community</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="interests-section">
                <h3>Select Your Interests</h3>
                <?php foreach ($categories as $category): ?>
                    <div class="category">
                        <div class="category-header">
                            <span class="material-icons"><?php echo htmlspecialchars($category['icon']); ?></span>
                            <h4><?php echo htmlspecialchars($category['name']); ?></h4>
                        </div>
                        <div class="fields-grid">
                            <?php foreach ($category['fields'] as $field): ?>
                                <div class="field-item">
                                    <input type="checkbox" 
                                           id="field_<?php echo $field['id']; ?>" 
                                           name="interests[]" 
                                           value="<?php echo $field['id']; ?>"
                                           <?php echo (isset($_POST['interests']) && in_array($field['id'], $_POST['interests'])) ? 'checked' : ''; ?>>
                                    <label for="field_<?php echo $field['id']; ?>">
                                        <?php echo htmlspecialchars($field['name']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="submit-btn">Create Account</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>
