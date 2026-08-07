<?php
$host = 'localhost';
$db = 'garden';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // If it's an API request, return JSON
     if (defined('API_REQUEST') || strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
         header("Content-Type: application/json; charset=UTF-8");
         http_response_code(500);
         echo json_encode([
             'status' => 'error',
             'message' => 'Database connection failed: ' . $e->getMessage()
         ]);
         exit;
     } else {
         die("Database connection failed: " . $e->getMessage());
     }
}
?>
