<?php
session_start();

// Initialize variables
$username = $password = "";
$usernameErr = $passwordErr = $loginErr = "";

// If form is submitted, process login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username
    if (empty($_POST["username"])) {
        $usernameErr = "Username is required";
    } else {
        $username = test_input($_POST["username"]);
    }
    
    // Validate password
    if (empty($_POST["password"])) {
        $passwordErr = "Password is required";
    } else {
        $password = test_input($_POST["password"]);
    }
    
    // If no validation errors, check credentials
    if (empty($usernameErr) && empty($passwordErr)) {
        $conn = new mysqli("localhost", "root", "", "weatherdb");
        
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
                $loginErr = "Invalid username or password";
            }
        } else {
            $loginErr = "Invalid username or password";
        }
    }
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - MeteoTrack</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="auth-body">
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <h1>Welcome Back</h1>
        <p>Sign in to your MeteoTrack account</p>
      </div>
      
      <?php if (!empty($loginErr)): ?>
        <div style="color: red; margin-bottom: 20px; text-align: center; background: #ffebee; padding: 10px; border-radius: 5px; border: 1px solid #f44336;">
          <strong>Error:</strong> <?php echo $loginErr; ?>
        </div>
      <?php endif; ?>
      
      <form class="auth-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <div class="form-group">
          <input type="text" name="username" placeholder="Username" value="<?php echo $username; ?>" required>
          <span style="color: red; font-size: 12px;"><?php echo $usernameErr; ?></span>
        </div>
        <div class="form-group">
          <input type="password" name="password" placeholder="Password" value="<?php echo $password; ?>" required>
          <span style="color: red; font-size: 12px;"><?php echo $passwordErr; ?></span>
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
