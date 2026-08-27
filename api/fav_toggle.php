<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/config/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']); exit;
}

$caseId = (int)($_POST['case_id'] ?? 0);
$userId = (int)$_SESSION['user_id'];

if (!$caseId) { echo json_encode(['error' => 'Invalid']); exit; }

try {
    // Toggle
    $exists = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id=? AND case_id=?");
    $exists->execute([$userId, $caseId]);

    if ($exists->fetchColumn()) {
        $pdo->prepare("DELETE FROM favorites WHERE user_id=? AND case_id=?")->execute([$userId, $caseId]);
        echo json_encode(['status' => 'removed']);
    } else {
        $pdo->prepare("INSERT IGNORE INTO favorites (user_id, case_id) VALUES (?,?)")->execute([$userId, $caseId]);
        echo json_encode(['status' => 'added']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Таблица favorites не найдена в базе данных. Импортируйте обновлённый database.sql.']);
}
