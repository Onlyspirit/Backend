<?php
// CORS headers for local Angular
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Local database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "MOScholar";
$secret_key = '235894762359784243894619';

$server_connection = mysqli_connect($servername, $username, $password, $dbname);

if (!$server_connection) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . mysqli_connect_error()]);
    exit;
}
?>