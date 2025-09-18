<?php
// edit.php - Edit weather data entry
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Initialize variables
$date = $location = $temperature = $humidity = $wind_speed = $precipitation = $pressure = "";
$dateErr = $locationErr = $temperatureErr = $humidityErr = $wind_speedErr = $precipitationErr = $pressureErr = "";
$success_message = "";

// Get the record ID from URL parameter
$record_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($record_id <= 0) {
    header("Location: view.php");
    exit;
}

// Connect to database and fetch the record
$conn = new mysqli("localhost", "root", "", "weatherdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Fetch the record to edit
$stmt = $conn->prepare("SELECT id, date, location, temperature, humidity, wind_speed, precipitation, pressure FROM weather_logs WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $record_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: view.php");
    exit;
}

$record = $result->fetch_assoc();
$stmt->close();

// Populate form fields with existing data
$date = $record['date'];
$location = $record['location'];
$temperature = $record['temperature'];
$humidity = $record['humidity'];
$wind_speed = $record['wind_speed'];
$precipitation = $record['precipitation'];
$pressure = $record['pressure'];

// If form is submitted, process data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate date
    if (empty($_POST["date"])) {
        $dateErr = "Date is required";
    } else {
        $date = test_input($_POST["date"]);
    }
    
    // Validate location
    if (empty($_POST["location"])) {
        $locationErr = "Location is required";
    } else {
        $location = test_input($_POST["location"]);
    }
    
    // Validate temperature
    if (empty($_POST["temperature"])) {
        $temperatureErr = "Temperature is required";
    } else {
        $temperature = test_input($_POST["temperature"]);
        if (!is_numeric($temperature) || $temperature < -60 || $temperature > 60) {
            $temperatureErr = "Temperature must be between -60°C and 60°C";
        }
    }
    
    // Validate humidity
    if (empty($_POST["humidity"])) {
        $humidityErr = "Humidity is required";
    } else {
        $humidity = test_input($_POST["humidity"]);
        if (!is_numeric($humidity) || $humidity < 0 || $humidity > 100) {
            $humidityErr = "Humidity must be between 0% and 100%";
        }
    }
    
    // Validate wind speed
    if (empty($_POST["wind_speed"])) {
        $wind_speedErr = "Wind speed is required";
    } else {
        $wind_speed = test_input($_POST["wind_speed"]);
        if (!is_numeric($wind_speed) || $wind_speed < 0) {
            $wind_speedErr = "Wind speed cannot be negative";
        }
    }
    
    // Validate precipitation
    if (empty($_POST["precipitation"])) {
        $precipitationErr = "Precipitation is required";
    } else {
        $precipitation = test_input($_POST["precipitation"]);
        if (!is_numeric($precipitation) || $precipitation < 0) {
            $precipitationErr = "Precipitation cannot be negative";
        }
    }
    
    // Validate pressure
    if (empty($_POST["pressure"])) {
        $pressureErr = "Pressure is required";
    } else {
        $pressure = test_input($_POST["pressure"]);
        if (!is_numeric($pressure) || $pressure < 800 || $pressure > 1100) {
            $pressureErr = "Pressure must be between 800 and 1100 hPa";
        }
    }
    
    // If no validation errors, update the database
    if (empty($dateErr) && empty($locationErr) && empty($temperatureErr) && 
        empty($humidityErr) && empty($wind_speedErr) && empty($precipitationErr) && empty($pressureErr)) {
        
        $stmt = $conn->prepare("UPDATE weather_logs SET date = ?, location = ?, temperature = ?, humidity = ?, wind_speed = ?, precipitation = ?, pressure = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssdddddi", $date, $location, $temperature, $humidity, $wind_speed, $precipitation, $pressure, $record_id, $user_id);
        
        if ($stmt->execute()) {
            $success_message = "Weather data updated successfully!";
            // Redirect to view page after successful update
            header("Location: view.php?updated=1");
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

$conn->close();

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
  <title>Edit Weather Data - MeteoTrack</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <!-- Header Section -->
    <div class="header">
      <h1>Edit Weather Data</h1>
      <p class="subtitle">Modify your weather log entry</p>
    </div>

    <!-- Success Message -->
    <?php if (!empty($success_message)): ?>
      <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
        <?php echo $success_message; ?>
      </div>
    <?php endif; ?>

    <!-- Data Entry Section -->
    <div class="panel">
      <h2 style="margin-top: 0; color: #0277bd; text-align: center;">Edit Weather Data</h2>
      <form id="weatherForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $record_id); ?>">
        <div class="grid">
          <div class="field">
            <label>Date</label>
            <input type="date" name="date" value="<?php echo $date; ?>" required>
            <span style="color: red; font-size: 12px;"><?php echo $dateErr; ?></span>
          </div>
          <div class="field">
            <label>Location</label>
            <input type="text" name="location" placeholder="Enter location" value="<?php echo $location; ?>" required>
            <span style="color: red; font-size: 12px;"><?php echo $locationErr; ?></span>
          </div>
          <div class="field">
            <label>Temperature (°C)</label>
            <input type="number" name="temperature" placeholder="Temperature" value="<?php echo $temperature; ?>" required>
            <span style="color: red; font-size: 12px;"><?php echo $temperatureErr; ?></span>
          </div>
          <div class="field">
            <label>Humidity (%)</label>
            <input type="number" name="humidity" placeholder="Humidity" value="<?php echo $humidity; ?>" required>
            <span style="color: red; font-size: 12px;"><?php echo $humidityErr; ?></span>
          </div>
          <div class="field">
            <label>Wind Speed (km/h)</label>
            <input type="number" name="wind_speed" placeholder="Wind Speed" value="<?php echo $wind_speed; ?>" required>
            <span style="color: red; font-size: 12px;"><?php echo $wind_speedErr; ?></span>
          </div>
          <div class="field">
            <label>Precipitation (mm)</label>
            <input type="number" name="precipitation" placeholder="Precipitation" value="<?php echo $precipitation; ?>" required>
            <span style="color: red; font-size: 12px;"><?php echo $precipitationErr; ?></span>
          </div>
          <div class="field">
            <label>Pressure (hPa)</label>
            <input type="number" name="pressure" placeholder="Pressure" value="<?php echo $pressure; ?>" required>
            <span style="color: red; font-size: 12px;"><?php echo $pressureErr; ?></span>
          </div>
        </div>
        <div class="actions">
          <button type="submit" class="button primary">Update Weather Data</button>
          <a href="view.php" class="button">Cancel</a>
        </div>
      </form>
    </div>

    <!-- Navigation Section -->
    <div class="panel">
      <div class="actions">
        <a href="view.php" class="button primary">Back to View Data</a>
        <a href="index.php" class="button">Add New Entry</a>
        <a href="logout.php" class="button">Logout</a>
      </div>
    </div>
  </div>
</body>
</html>
