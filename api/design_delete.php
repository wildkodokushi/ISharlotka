<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/config/db.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

$designId = (int)($_POST['design_id'] ?? 0);
if (!$designId) {
    echo json_encode(['success' => false, 'error' => 'Неверный запрос']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM saved_designs WHERE id_design = ? AND user_id = ?");
    $stmt->execute([$designId, $_SESSION['user_id']]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Дизайн не найден или принадлежит другому пользователю']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Ошибка базы данных']);
}
