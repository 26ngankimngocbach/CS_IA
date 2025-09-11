<?php
session_start();

// If form is submitted, process login
if ($_POST) {
    $conn = new mysqli("localhost", "root", "", "weatherdb");
    
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Weather Logger</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="auth-body">
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <h1>Welcome Back</h1>
        <p>Sign in to your Weather Logger account</p>
      </div>
      
      <?php if (isset($error)): ?>
        <div style="color: red; margin-bottom: 20px; text-align: center;"><?php echo $error; ?></div>
      <?php endif; ?>
      
      <form class="auth-form" action="login.php" method="POST">
        <div class="form-group">
          <input type="text" name="username" placeholder="Username" required>
        </div>
        <div class="form-group">
          <input type="password" name="password" placeholder="Password" required>
        </div>
        <button type="submit" class="auth-button">Sign In</button>
      </form>
      
      <div class="auth-footer">
        <p>Don't have an account? <a href="register.php" class="auth-link">Sign Up</a></p>
      </div>
    </div>
  </div>
</body>
</html>
