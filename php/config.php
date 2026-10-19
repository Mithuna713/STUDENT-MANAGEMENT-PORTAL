<?php
$host = 'localhost';
$db   = 'student_portal';
$user = 'root';
$pass = ''; // Change this if your MySQL account uses a password.
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
];
try { $pdo = new PDO($dsn, $user, $pass, $options); }
catch (PDOException $e) { http_response_code(500); exit('Database connection failed. Check config.php and MySQL.'); }
?>
