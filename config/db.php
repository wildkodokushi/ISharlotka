<?php
define('DB_HOST', getenv('DB_HOST') ?: 'db'); 
define('DB_NAME', getenv('DB_NAME') ?: 'cases_shop');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'root'); 
define('DB_CHARSET', 'utf8mb4');

try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET,
        DB_USER, DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    // Для отладки локально лучше временно выводить реальную ошибку: $e->getMessage()
    die(json_encode(['error' => 'Ошибка подключения к базе данных: ' . $e->getMessage()]));
}