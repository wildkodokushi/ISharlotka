<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/config/db.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

$caseId = (int)($_POST['case_id'] ?? 0);
$title  = trim($_POST['title'] ?? '') ?: 'Мой дизайн';
$designData = $_POST['design_data'] ?? '';

if (!$caseId) {
    echo json_encode(['success' => false, 'error' => 'Не указан базовый чехол']);
    exit;
}

// Проверим, что JSON валиден
$decoded = json_decode($designData, true);
if ($decoded === null) {
    echo json_encode(['success' => false, 'error' => 'Некорректные данные дизайна']);
    exit;
}

// Проверим, что чехол существует
$check = $pdo->prepare("SELECT id_case FROM cases_catalog WHERE id_case = ?");
$check->execute([$caseId]);
if (!$check->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Чехол не найден']);
    exit;
}

try {
    $shareToken = bin2hex(random_bytes(16));
    $stmt = $pdo->prepare("INSERT INTO saved_designs (user_id, case_id, title, design_data, share_token) VALUES (?,?,?,?,?)");
    $stmt->execute([$_SESSION['user_id'], $caseId, $title, $designData, $shareToken]);
    $designId = $pdo->lastInsertId();

    echo json_encode([
        'success'   => true,
        'designId'  => $designId,
        'shareUrl'  => BASE_URL . '/design_view.php?token=' . $shareToken,
        'viewUrl'   => BASE_URL . '/design_view.php?id=' . $designId,
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error'   => 'Не удалось сохранить дизайн. Возможно, таблица saved_designs ещё не создана — импортируйте обновлённый database.sql.'
    ]);
}
