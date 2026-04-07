<?php
/**
 * СКРИПТ СБРОСА ПАРОЛЯ АДМИНИСТРАТОРА
 * Откройте этот файл в браузере один раз, затем удалите его!
 * Пример: http://localhost/cases_shop/reset_admin.php
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/config/db.php';

$newPassword = 'admin123';
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE login = 'admin' AND role = 'admin'");
$affected = $stmt->execute([$hash]);

if ($stmt->rowCount() > 0) {
    echo '<div style="font-family:sans-serif;max-width:480px;margin:60px auto;padding:2rem;background:#1a1a1f;border:1px solid #333;border-radius:12px;color:#d8d0c4">';
    echo '<h2 style="color:#5ABA8A;margin-top:0">✓ Пароль сброшен!</h2>';
    echo '<p>Логин: <strong style="color:#F0EAD6">admin</strong></p>';
    echo '<p>Пароль: <strong style="color:#F0EAD6">admin123</strong></p>';
    echo '<p style="color:#E05A5A;font-size:0.9rem">⚠ Удалите этот файл (reset_admin.php) после использования!</p>';
    echo '<a href="' . BASE_URL . '/login.php" style="display:inline-block;margin-top:1rem;padding:0.75rem 1.5rem;background:#C8A96E;color:#0C0C0E;border-radius:8px;text-decoration:none;font-weight:600">Войти →</a>';
    echo '</div>';
} else {
    echo '<div style="font-family:sans-serif;max-width:480px;margin:60px auto;padding:2rem;background:#1a1a1f;border:1px solid #E05A5A;border-radius:12px;color:#E05A5A">';
    echo '<h2 style="margin-top:0">✕ Ошибка</h2>';
    echo '<p>Администратор с логином "admin" не найден в базе данных.</p>';
    echo '<p style="color:#7A7570">Убедитесь, что вы импортировали database.sql и в таблице users есть запись с login=admin и role=admin.</p>';
    echo '</div>';
}