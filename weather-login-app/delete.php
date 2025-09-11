<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$conn = new mysqli("localhost", "root", "", "weatherdb");
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

$id = intval($_POST['id']);
$user_id = $_SESSION['user_id'];

// Delete only if it belongs to the logged-in user
$conn->query("DELETE FROM weather_logs WHERE id=$id AND 
user_id=$user_id");

header("Location: view.php");
exit;
?>

