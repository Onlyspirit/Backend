<?php
require('index.php');
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Set response type to JSON
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Content-Type: application/json; charset=UTF-8");

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Extract fields
$Email    = trim($data['Email'] ?? '');
$password = $data['Password'] ?? '';

// Validate fields
if (empty($Email) || empty($password)) {
    echo json_encode(['message' => 'Email and password are required']);
    exit;
}

// Fetch user with prepared statement
$stmt = mysqli_prepare($server_connection, "SELECT * FROM users WHERE Email = ?");
mysqli_stmt_bind_param($stmt, "s", $Email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    echo json_encode(['message' => 'Invalid email or password']);
    exit;
}

$user = mysqli_fetch_assoc($result);

// Verify password
if (!password_verify($password, $user['Password'])) {
    echo json_encode(['message' => 'Invalid email or password', 'status' => 'error']);
    exit;
}

// Successful login
$payload = [
    'id' => $user['Id_Number'], // Adjust to your DB column (e.g., 'id' or 'Id_Number')
    'email' => $user['Email'],
    'iat' => time(),
    'Role' => $user['Role'],
    'exp' => time() + (60 * 60) // 1 hour expiration
];
$jwt = JWT::encode($payload, $secret_key, 'HS256');

echo json_encode([
    'status'   => 'success',
    'Firstname' => $user['Firstname'],
    'message' => 'Login successful',
    'user' => [
        'id' => $user['Id_Number'],
        'Role' => $user['Role'],
        'Lastname' => $user['Lastname'],
        'Email' => $user['Email'],
        'token' => $jwt
    ]
]);

mysqli_close($server_connection);
?>