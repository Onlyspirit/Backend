<?php
require('index.php');
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

$data = json_decode(file_get_contents('php://input'), true);
$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');
$userId = $data['userId'] ?? null;
$price = floatval($data['price'] ?? 0);
$image = $data['image'] ?? 'https://via.placeholder.com/220x140'; // Default if no image

if (empty($title) || empty($description) || $userId === null) {
    echo json_encode(['message' => 'Invalid input: title, description, and userId are required']);
    exit;
}

$authHeader = getallheaders()['Authorization'] ?? '';
if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
    echo json_encode(['message' => 'Invalid or missing token', 'status' => 'error']);
    exit;
}
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

// Store base64 image directly (for local testing; consider file system later)
$stmt = mysqli_prepare($server_connection, "INSERT INTO courses (title, description, price, instructor_id, image) VALUES (?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "ssdss", $title, $description, $price, $userId, $image);
$result = mysqli_stmt_execute($stmt);

if ($result) {
    $newCourseId = mysqli_insert_id($server_connection);
    echo json_encode([
        'message' => 'Course created successfully',
        'id' => $newCourseId
    ]);
} else {
    echo json_encode([
        'message' => 'Database error',
        'error' => mysqli_error($server_connection)
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($server_connection);
?>