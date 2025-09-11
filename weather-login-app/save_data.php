k<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "Not logged in.";
    exit;
}

$conn = new mysqli("localhost", "root", "", "weatherdb");
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Get form data
$date   = $_POST['date'];
$loc    = $_POST['location'];
$temp   = $_POST['temperature'];
$hum    = $_POST['humidity'];
$wind   = $_POST['wind_speed'];
$precip = $_POST['precipitation'];
$press  = $_POST['pressure'];

$errors = [];
if ($temp < -60 || $temp > 60) $errors[] = "Temperature out of range.";
if ($hum < 0 || $hum > 100)    $errors[] = "Humidity out of range.";
if ($wind < 0)                 $errors[] = "Wind cannot be negative.";
if ($precip < 0)               $errors[] = "Precip cannot be negative.";
if ($press < 800 || $press > 1100) $errors[] = "Pressure out of range.";

if ($errors) {
    echo implode("<br>", $errors);
    exit;
}

// If valid → insert into DB
$stmt = $conn->prepare(
    "INSERT INTO weather_logs (user_id, date, location, temperature, 
humidity, wind_speed, precipitation, pressure) 
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("issddddd", $user_id, $date, $loc, $temp, $hum, $wind, 
$precip, $press);
$stmt->execute();

$stmt->close();
$conn->close();

// Redirect to view
header("Location: view.php");
exit;
?>

