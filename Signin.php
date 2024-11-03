<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f0f0f0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .container {
      background-color: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      max-width: 400px;
      width: 100%;
    }
    header {
      font-size: 24px;
      margin-bottom: 20px;
      text-align: center;
      color: #333;
    }
    label {
      display: block;
      margin-bottom: 5px;
      color: #555;
    }
    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    input[type="submit"] {
      width: 100%;
      padding: 10px;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
    }
    input[type="submit"]:hover {
      background-color: #45a049;
    }
    .error {
      color: red;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="form-box">
      <header>Sign Up</header>
      <?php
        $error = "";
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
          $firstname = $_POST['firstname'] ?? '';
          $lastname = $_POST['lastname'] ?? '';
          $email = $_POST['email'] ?? '';
          $password = $_POST['password'] ?? '';

          if (empty($firstname) || empty($lastname) || empty($email) || empty($password)) {
            $error = "Please fill in all fields.";
          } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
          } elseif (strlen($password) < 8 || !preg_match("/[A-Za-z]/", $password) || !preg_match("/\d/", $password)) {
            $error = "Password must be at least 8 characters long and contain both letters and numbers.";
          }

          if ($error) {
            echo "<p class='error'>$error</p>";
          } else {
            // Redirect to Page1.php
            header("Location: Login.php");
            exit();
          }
        }
      ?>
      <form action="" method="post">
        <div>
          <label for="firstname">First name</label>
          <input name="firstname" type="text" id="firstname" placeholder="Enter first name" value="<?php echo isset($firstname) ? htmlspecialchars($firstname) : ''; ?>">
        </div>
        <div>
          <label for="lastname">Last name</label>
          <input name="lastname" type="text" id="lastname" placeholder="Enter last name" value="<?php echo isset($lastname) ? htmlspecialchars($lastname) : ''; ?>">
        </div>
        <div>
          <label for="email">Email Address</label>
          <input name="email" type="text" id="email" placeholder="Enter email address" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
        </div>
        <div>
          <label for="password">Password</label>
          <input name="password" type="password" id="password" placeholder="Enter password">
        </div>
        <div>
          <input type="submit" value="Submit">
        </div>
      </form>
    </div>
  </div>
</body>
</html>

