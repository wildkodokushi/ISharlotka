<?php
// Скрипт автоматического импорта таблиц в облаке Railway
$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'cases_shop';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: 'root';
$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Ищем файл схемы в корне проекта
    $sqlFile = __DIR__ . '/init.sql'; // <-- Укажите тут точное имя вашего файла (database.sql или init.sql)
    if (!file_exists($sqlFile)) {
        die("Файл базы данных не найден в контейнере!");
    }
    
    $sql = file_get_contents($sqlFile);
    $pdo->exec($sql);
    
    echo "<h1>Ура! База данных успешно импортирована!</h1>";
} catch (PDOException $e) {
    echo "<h1>Ошибка импорта:</h1> " . $e->getMessage();
}
