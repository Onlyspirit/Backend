<?php
// Include database and JWT setup
require('index.php'); // Assumes $server_connection and $secret_key are defined
require 'vendor/autoload.php'; // For Firebase PHP-JWT

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// CORS headers for local Angular
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Check database connection
if (!$server_connection) {
    echo json_encode(['message' => 'Database connection failed', 'error' => 'Check index.php configuration']);
    exit;
}

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);
$courseId = $data['courseId'] ?? '';
$authHeader = getallheaders()['Authorization'] ?? '';
$userId = null;

if ($authHeader) {
    try {
        $jwt = str_replace('Bearer ', '', $authHeader);
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
        $userId = $decoded->id; // Match the JWT payload field
    } catch (Exception $e) {
        echo json_encode(['message' => 'Invalid token', 'error' => $e->getMessage()]);
        exit;
    }
}

if (!empty($courseId) && $userId !== null) {
    // Check if enrollments table exists, create if not
    $checkTable = mysqli_query($server_connection, "SHOW TABLES LIKE 'enrollments'");
    if (mysqli_num_rows($checkTable) == 0) {
        $createTable = "CREATE TABLE enrollments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            course_id INT NOT NULL,
            enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (course_id) REFERENCES courses(id)
        )";
        if (!mysqli_query($server_connection, $createTable)) {
            echo json_encode(['message' => 'Failed to create enrollments table', 'error' => mysqli_error($server_connection)]);
            exit;
        }
    }

    // Check for duplicate enrollment
    $checkStmt = mysqli_prepare($server_connection, "SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND course_id = ?");
    if ($checkStmt === false) {
        echo json_encode(['message' => 'Prepare failed', 'error' => mysqli_error($server_connection)]);
        exit;
    }
    mysqli_stmt_bind_param($checkStmt, "ii", $userId, $courseId);
    mysqli_stmt_execute($checkStmt);
    mysqli_stmt_bind_result($checkStmt, $count);
    mysqli_stmt_fetch($checkStmt);
    mysqli_stmt_close($checkStmt);

    if ($count > 0) {
        echo json_encode(['message' => 'Already enrolled in this course']);
        exit;
    }

    // Insert enrollment
    $stmt = mysqli_prepare($server_connection, "INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
    if ($stmt === false) {
        echo json_encode(['message' => 'Prepare failed', 'error' => mysqli_error($server_connection)]);
        exit;
    }
    mysqli_stmt_bind_param($stmt, "ii", $userId, $courseId);
    $result = mysqli_stmt_execute($stmt);
    if ($result) {
        echo json_encode(['message' => 'Enrollment success']);
    } else {
        echo json_encode(['message' => 'Enrollment failed', 'error' => mysqli_error($server_connection)]);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['message' => 'Invalid course ID or user not authenticated']);
}

mysqli_close($server_connection);
?>