<?php
require('config.php');

// Set response type to JSON
header("Content-Type: application/json; charset=UTF-8");

// Get JSON input
$data = json_decode(file_get_contents('php://input'));

// Extract fields
$Firstname = trim($data->Firstname ?? '');
$Lastname  = trim($data->Lastname ?? '');
$Email     = trim($data->email ?? '');
$password  = $data->password ?? '';
$role      = trim($data->role ?? '');

// ✅ Validate BEFORE inserting
if (empty($Firstname) || empty($Lastname) || empty($Email) || empty($password) || empty($role)) {
    echo json_encode(['message' => 'All fields are required']);
    exit;
}

// ✅ Optional: check for duplicate email
$check = mysqli_query($server_connection, "SELECT * FROM users WHERE Email='$Email'");
if (mysqli_num_rows($check) > 0) {
    echo json_encode(['message' => 'This email is already registered']);
    exit;
}

// ✅ Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// ✅ Insert user
$query = "INSERT INTO users (Firstname, Lastname, Email, Password, Role)
          VALUES ('$Firstname', '$Lastname', '$Email', '$hashedPassword', '$role')";

$result = mysqli_query($server_connection, $query);

if ($result) {
    echo json_encode(['message' => 'User registered successfully']);
} else {
    echo json_encode([
        'message' => 'Database error',
        'error' => mysqli_error($server_connection)
    ]);
}
?>
