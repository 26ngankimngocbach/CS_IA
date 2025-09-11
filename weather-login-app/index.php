<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Weather Logger</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <!-- Header Section -->
    <div class="header">
      <h1>Weather Logger</h1>
      <p class="subtitle">Track and monitor weather conditions</p>
    </div>

    <!-- Data Entry Section -->
    <div class="panel">
      <h2 style="margin-top: 0; color: #0277bd; text-align: center;">Add Weather Data</h2>
      <form id="weatherForm" method="POST" action="save_data.php">
        <div class="grid">
          <div class="field">
            <label>Date</label>
            <input type="date" name="date" required>
          </div>
          <div class="field">
            <label>Location</label>
            <input type="text" name="location" placeholder="Enter location" required>
          </div>
          <div class="field">
            <label>Temperature (°C)</label>
            <input type="number" name="temperature" placeholder="Temperature" required>
          </div>
          <div class="field">
            <label>Humidity (%)</label>
            <input type="number" name="humidity" placeholder="Humidity" required>
          </div>
          <div class="field">
            <label>Wind Speed (km/h)</label>
            <input type="number" name="wind_speed" placeholder="Wind Speed" required>
          </div>
          <div class="field">
            <label>Precipitation (mm)</label>
            <input type="number" name="precipitation" placeholder="Precipitation" required>
          </div>
          <div class="field">
            <label>Pressure (hPa)</label>
            <input type="number" name="pressure" placeholder="Pressure" required>
          </div>
        </div>
        <div class="actions">
          <button type="submit" class="button primary">Save Weather Data</button>
        </div>
      </form>
    </div>

    <!-- Navigation Section -->
    <div class="panel">
      <div class="actions">
        <a href="view.php" class="button primary">View Saved Data</a>
        <a href="logout.php" class="button">Logout</a>
      </div>
    </div>
  </div>
</body>
</html>

