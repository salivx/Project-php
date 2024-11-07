<?php
session_start(); 

if (isset($_SESSION['user'])) {
    $email = $_SESSION['user']; 
    $username = explode('@', $email)[0]; 
} else {
    $username = "Guest"; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <title>Main Page</title>
  <style>
    body {
      font-family: 'Arial', sans-serif;
      background-color: #f8f9fa; /* Light background for contrast */
    }

    .navbar {
      background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%); /* Gradient background */
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    }

    .navbar-brand, .nav-link, .user-name {
      color: white !important; /* White text for navbar items */
      transition: color 0.3s; /* Smooth color transition */
    }

    .nav-link:hover {
      color: #ffdd57 !important; /* Change color on hover */
    }

    .user-name {
      margin-left: auto; /* Push the user name to the right */
      padding-right: 20px; /* Add some padding */
      font-weight: bold; /* Make the user name bold */
    }

    .search-bar {
      display: flex;
      align-items: center;
    }

    .form-control {
      border-radius: 20px; /* Rounded corners */
      border: 1px solid #ced4da; /* Border color */
    }

    .btn-outline-light {
      border-radius: 20px; /* Rounded corners */
      transition: background-color 0.3s, color 0.3s; /* Smooth transition */
    }

    .btn-outline-light:hover {
      background-color: #ffdd57; /* Change background on hover */
      color: #000; /* Change text color on hover */
    }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Travel Agency</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Destinations</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Packages</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Bookings</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Reviews</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Contact</a>
          </li>
        </ul>
        <div class="search-bar">
          <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
          <button class="btn btn-outline-light" type="submit">Search</button>
        </div>
        <span class="user-name"><?php echo htmlspecialchars($username); ?></span> 

      </div>
    </div>
  </nav>

  <div class="container mt-4">
    <h1>Welcome to Our Travel Agency</h1>
    <!-- Additional content for your page -->
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+YDj5ure3uZsuu1zgq5DU5QXhhY+4" crossorigin="anonymous"></script>
</body>
</html>
