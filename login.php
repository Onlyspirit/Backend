<?php
require('index.php');
require 'vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
// Set response type to JSON
header("Content-Type: application/json; charset=UTF-8");
// Get JSON input
$data = json_decode(file_get_contents('php://input'));
// Extract fields
$Email    = trim($data->Email ?? '');
$password = $data->Password ?? '';
// ✅ Validate BEFORE querying
if (empty($Email) || empty($password)) {
    echo json_encode(['message' => 'Email and password are required']);
    exit;
}
// ✅ Fetch user
$query = "SELECT * FROM users WHERE Email='$Email'";
$result = mysqli_query($server_connection, $query);
if (mysqli_num_rows($result) === 0) {
    echo json_encode(['message' => 'Invalid email or password']);
    exit;
}
$user = mysqli_fetch_assoc($result);
// ✅ Verify password
if (!password_verify($password, $user['Password'])) {
    echo json_encode(['message' => 'Invalid email or password', 'status' => 'error']);
    exit;
}
// ✅ Successful login
$payload = [
    'id' => $user['Id_Number'],
    'email' => $user['Email'],
    'iat' => time(),
    'Role' => $user['Role'],
    'exp' => time() + (60 * 60) // Token valid for 1 hour
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
