<?php
require('index.php');
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Content-Length: 0");
    exit;
}

if (!$server_connection) {
    echo json_encode(['message' => 'Database connection failed', 'error' => 'Check index.php configuration']);
    exit;
}

$userId = isset($_GET['userId']) ? intval($_GET['userId']) : null;
$authHeader = getallheaders()['Authorization'] ?? '';

if ($authHeader) {
    try {
        $jwt = str_replace('Bearer ', '', $authHeader);
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
        if ($decoded->id != $userId) {
            echo json_encode(['message' => 'Token does not match user ID', 'status' => 'error']);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['message' => 'Invalid token', 'error' => $e->getMessage()]);
        exit;
    }
}

if ($userId !== null) {
    $stmt = mysqli_prepare($server_connection, "SELECT c.id, c.title, c.description, c.price, c.image FROM courses c JOIN enrollments e ON c.id = e.course_id WHERE e.user_id = ?");
    if ($stmt === false) {
        echo json_encode(['message' => 'Prepare failed', 'error' => mysqli_error($server_connection)]);
        exit;
    }
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $courses = mysqli_fetch_all($result, MYSQLI_ASSOC);
    echo json_encode(['courses' => $courses]);
} else {
    echo json_encode(['message' => 'User not authenticated']);
}

mysqli_close($server_connection);
?>