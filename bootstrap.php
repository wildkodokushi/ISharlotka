<?php
// Корневой путь проекта (папка cases_shop)
define('BASE_PATH', __DIR__);

// Автоматически определяем URL-префикс (работает и в корне домена, и в подпапке)
$_scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$_base = rtrim($_scriptDir, '/');
// Если скрипт находится в /admin или /api — поднимаемся на уровень выше
if (in_array(basename($_base), ['admin', 'api'])) {
    $_base = dirname($_base);
}
define('BASE_URL', rtrim($_base, '/'));

// Универсальный редирект с учётом подпапки
function redirect(string $path): void {
    header('Location: ' . BASE_URL . $path);
    exit;
}
