<?php
session_start();

// Initialize variables
$username = $password = "";
$usernameErr = $passwordErr = $success = $error = "";

// If form is submitted, process registration
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username
    if (empty($_POST["username"])) {
        $usernameErr = "Username is required";
    } else {
        $username = test_input($_POST["username"]);
        // Check if username already exists
        $conn = new mysqli("localhost", "root", "", "weatherdb");
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        if ($result->num_rows > 0) {
            $usernameErr = "Username already exists";
        }
    }
    
    // Validate password
    if (empty($_POST["password"])) {
        $passwordErr = "Password is required";
    } else {
        $password = test_input($_POST["password"]);
        if (strlen($password) < 6) {
            $passwordErr = "Password must be at least 6 characters";
        }
    }
    
    // If no validation errors, register user
    if (empty($usernameErr) && empty($passwordErr)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed_password);
        
        if ($stmt->execute()) {
            $success = "Registration successful! You can now login.";
            $username = $password = ""; // Clear form
        } else {
            $error = "Registration failed. Please try again.";
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
  <title>Sign Up - MeteoTrack</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="auth-body">
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <h1>Create Account</h1>
        <p>Join MeteoTrack to start tracking weather data</p>
      </div>
      
      <?php if (!empty($error)): ?>
        <div style="color: red; margin-bottom: 20px; text-align: center; background: #ffebee; padding: 10px; border-radius: 5px; border: 1px solid #f44336;">
          <strong>Error:</strong> <?php echo $error; ?>
        </div>
      <?php endif; ?>
      
      <?php if (!empty($success)): ?>
        <div style="color: green; margin-bottom: 20px; text-align: center; background: #e8f5e8; padding: 10px; border-radius: 5px; border: 1px solid #4caf50;">
          <strong>Success:</strong> <?php echo $success; ?>
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
        <button type="submit" class="auth-button">Create Account</button>
      </form>
      
      <div class="auth-footer">
        <p>Already have an account? <a href="login.php" class="auth-link">Sign In</a></p>
      </div>
    </div>
  </div>
</body>
</html>
