<?php
session_start();

// If form is submitted, process registration
if ($_POST) {
    $conn = new mysqli("localhost", "root", "", "weatherdb");
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);
    
    if ($stmt->execute()) {
        $success = "Registration successful! You can now login.";
    } else {
        $error = "Registration failed. Username might already exist.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - Weather Logger</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="auth-body">
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <h1>Create Account</h1>
        <p>Join Weather Logger to start tracking weather data</p>
      </div>
      
      <?php if (isset($error)): ?>
        <div style="color: red; margin-bottom: 20px; text-align: center;"><?php echo $error; ?></div>
      <?php endif; ?>
      
      <?php if (isset($success)): ?>
        <div style="color: green; margin-bottom: 20px; text-align: center;"><?php echo $success; ?></div>
      <?php endif; ?>
      
      <form class="auth-form" action="register.php" method="POST">
        <div class="form-group">
          <input type="text" name="username" placeholder="Username" required>
        </div>
        <div class="form-group">
          <input type="password" name="password" placeholder="Password" required>
        </div>
        <button type="submit" class="auth-button">Create Account</button>
      </form>
      
      <div class="auth-footer">
        <p>Already have an account? <a href="login.php" class="auth-link">Sign In</a></p>
      </div>
    </div>
  </div>
</body>
</html>
