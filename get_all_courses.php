<?php
require('index.php'); // Assumes $server_connection and $secret_key are defined
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://localhost:4200");
    header("Access-Control-Allow-Headers: Authorization, Content-Type");
    header("Access-Control-Allow-Methods: GET, OPTIONS");
    header("Content-Length: 0");
    header("Content-Type: application/json; charset=UTF-8");
    exit;
}

// CORS headers for GET requests
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if (!$server_connection) {
    echo json_encode(['message' => 'Database connection failed', 'error' => 'Check index.php configuration']);
    exit;
}

$authHeader = getallheaders()['Authorization'] ?? '';
$userId = null;

if ($authHeader) {
    try {
        $jwt = str_replace('Bearer ', '', $authHeader);
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
        $userId = $decoded->id;
    } catch (Exception $e) {
        echo json_encode(['message' => 'Invalid token', 'error' => $e->getMessage()]);
        exit;
    }
}

if ($userId) {
    $stmt = mysqli_prepare($server_connection, "SELECT id, title, description, price, image FROM courses WHERE instructor_id = ?");
    if ($stmt === false) {
        echo json_encode(['message' => 'Prepare failed', 'error' => mysqli_error($server_connection)]);
        exit;
    }
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $courses = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['message' => 'User not authenticated']);
    exit;
}

echo json_encode(['courses' => $courses]);

mysqli_close($server_connection);
?>