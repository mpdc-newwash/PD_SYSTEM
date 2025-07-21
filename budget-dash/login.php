<?php
session_start();
$error = "";

// DEBUG: Check submitted POST data
// Uncomment below to debug
// var_dump($_POST);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST["username"] ?? "";
  $password = $_POST["password"] ?? "";

  // Simple hardcoded login
  if ($username === "admin" && $password === "mpdc123") {
    $_SESSION["loggedin"] = true;
    header("Location: index.php");
    exit;
  } else {
    $error = "Invalid login credentials.";
  }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Budget Login</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #e0f0ff;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .login-box {
      background: #ffffff;
      border: 2px solid #3399ff;
      padding: 30px;
      text-align: center;
      width: 300px;
      box-shadow: 0 0 10px rgba(0,0,0,0.2);
    }
    .login-box img {
      width: 80px;
      margin-bottom: 15px;
    }
    .login-box input {
      width: 100%;
      padding: 10px;
      margin-bottom: 12px;
      border: 1px solid #ccc;
    }
    .login-box button {
      background: #3399ff;
      border: none;
      color: white;
      padding: 10px;
      width: 100%;
      cursor: pointer;
    }
    .error {
      color: red;
      font-size: 0.9em;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <form class="login-box" method="POST">
    <img src="Official-Logo.png" alt="MPDC Logo">
    <h2>PROGRAM STATEMENT SYSTEM</h2>
    <h2>Budget Office Login</h2>
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
    
    <h5>Forgot your password? Contact MPDC Office</h5>
    <h6>Maintained by MPDC Office</h6>
  </form>
</body>
</html>
