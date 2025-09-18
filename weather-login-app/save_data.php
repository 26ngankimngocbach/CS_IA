<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Initialize variables
$date = $location = $temperature = $humidity = $wind_speed = $precipitation = $pressure = "";
$dateErr = $locationErr = $temperatureErr = $humidityErr = $wind_speedErr = $precipitationErr = $pressureErr = "";

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
    
    // If no validation errors, save to database
    if (empty($dateErr) && empty($locationErr) && empty($temperatureErr) && 
        empty($humidityErr) && empty($wind_speedErr) && empty($precipitationErr) && empty($pressureErr)) {
        
        $conn = new mysqli("localhost", "root", "", "weatherdb");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO weather_logs (user_id, date, location, temperature, humidity, wind_speed, precipitation, pressure) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issddddd", $user_id, $date, $location, $temperature, $humidity, $wind_speed, $precipitation, $pressure);
        
        if ($stmt->execute()) {
            header("Location: view.php?success=1");
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }
        
        $stmt->close();
        $conn->close();
    }
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

