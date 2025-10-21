<?php
// Allow CORS from both localhost (for dev) and Render (for production)
$allowed_origins = [
    "http://localhost:4200",            // your Angular dev server
    "https://moscholar.vercel.app/landing" // your deployed frontend
];

// Dynamically set CORS header based on request origin
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
} else {
    header("Access-Control-Allow-Origin: *"); // fallback
}

header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Secret key
$secret_key = '235894762359784243894619';

// Detect environment (local vs render)
$isLocal = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']);

// Database credentials
if ($isLocal) {
    // 🧩 Local setup
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "MOScholar";
} else {
    // 🌍 Render setup (values will come from environment variables)
    $servername = getenv("DB_HOST");
    $username = getenv("DB_USER");
    $password = getenv("DB_PASS");
    $dbname = getenv("DB_NAME");
}

// Connect to database
$server_connection = mysqli_connect($servername, $username, $password, $dbname);

// Connection error handling
if (!$server_connection) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . mysqli_connect_error()]);
    exit;
}
?>
