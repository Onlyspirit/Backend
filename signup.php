<?php
require('index.php');

// Set response type to JSON
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Content-Type: application/json; charset=UTF-8");

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Extract and trim fields
$Firstname = trim($data['Firstname'] ?? '');
$Lastname  = trim($data['Lastname'] ?? '');
$Email     = trim($data['email'] ?? '');
$password  = $data['password'] ?? '';
$role      = trim($data['role'] ?? '');

// Validate fields
if (empty($Firstname) || empty($Lastname) || empty($Email) || empty($password) || empty($role)) {
    echo json_encode(['message' => 'All fields are required']);
    exit;
}

// Check duplicate email with prepared statement
$stmt = mysqli_prepare($server_connection, "SELECT * FROM users WHERE Email = ?");
mysqli_stmt_bind_param($stmt, "s", $Email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($result) > 0) {
    echo json_encode(['message' => 'This email is already registered']);
    exit;
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert user with prepared statement
$stmt = mysqli_prepare($server_connection, "INSERT INTO users (Firstname, Lastname, Email, Password, Role) VALUES (?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "sssss", $Firstname, $Lastname, $Email, $hashedPassword, $role);
$result = mysqli_stmt_execute($stmt);

if ($result) {
    echo json_encode(['message' => 'User registered successfully']);
} else {
    echo json_encode(['message' => 'Database error', 'error' => mysqli_error($server_connection)]);
}

mysqli_close($server_connection);
?>