<?php
require('index.php');

// Set response type to JSON with CORS
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Content-Type: application/json; charset=UTF-8");

// Fetch courses
$result = mysqli_query($server_connection, "SELECT * FROM courses");
$courses = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo json_encode($courses);

mysqli_close($server_connection);
?>