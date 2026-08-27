<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/config/db.php';
header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['success'=>false,'error'=>'Не авторизован']); exit; }

$caseId       = (int)($_POST['case_id'] ?? 0);
$qty          = max(1, (int)($_POST['qty'] ?? 1));
$customDesign = trim($_POST['custom_design'] ?? '');

if (!$caseId) { echo json_encode(['success'=>false,'error'=>'Неверный товар']); exit; }

// Проверяем актуальный остаток на складе
$stmt = $pdo->prepare("SELECT count, title FROM cases_catalog WHERE id_case = ?");
$stmt->execute([$caseId]);
$case = $stmt->fetch();

if (!$case) { echo json_encode(['success'=>false,'error'=>'Товар не найден']); exit; }

$stock        = (int)$case['count'];
$alreadyInCart = (int)($_SESSION['cart'][$caseId]['qty'] ?? 0);
$desiredTotal  = $alreadyInCart + $qty;

if ($stock <= 0) {
    echo json_encode(['success'=>false,'error'=>'Товар «'.$case['title'].'» больше нет в наличии']);
    exit;
}

if ($desiredTotal > $stock) {
    $canAdd = $stock - $alreadyInCart;
    if ($canAdd <= 0) {
        echo json_encode([
            'success' => false,
            'error'   => 'Вы уже добавили максимум доступного количества («'.$case['title'].'»: в наличии '.$stock.' шт.)'
        ]);
        exit;
    }
    cartAdd($caseId, $canAdd, $customDesign);
    echo json_encode([
        'success'  => true,
        'count'    => cartCount(),
        'clamped'  => true,
        'message'  => 'В наличии всего '.$stock.' шт. Добавлено максимум возможное количество.'
    ]);
    exit;
}

cartAdd($caseId, $qty, $customDesign);
echo json_encode(['success' => true, 'count' => cartCount()]);
