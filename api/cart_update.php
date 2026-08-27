<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/config/db.php';
header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['success'=>false,'error'=>'Не авторизован']); exit; }

$caseId = (int)($_POST['case_id'] ?? 0);
$delta  = (int)($_POST['delta'] ?? 0);

if (!$caseId || !isset($_SESSION['cart'][$caseId])) {
    echo json_encode(['success'=>false,'error'=>'Товар не найден в корзине']);
    exit;
}

$stmt = $pdo->prepare("SELECT count, title, price FROM cases_catalog WHERE id_case = ?");
$stmt->execute([$caseId]);
$case = $stmt->fetch();

if (!$case) {
    unset($_SESSION['cart'][$caseId]);
    echo json_encode(['success'=>false,'error'=>'Товар больше не существует', 'removed'=>true]);
    exit;
}

$stock   = (int)$case['count'];
$current = (int)$_SESSION['cart'][$caseId]['qty'];
$newQty  = $current + $delta;

if ($newQty < 1) { $newQty = 1; }

if ($newQty > $stock) {
    $newQty = $stock > 0 ? $stock : 1;
    $clamped = true;
} else {
    $clamped = false;
}

if ($stock <= 0) {
    echo json_encode([
        'success' => false,
        'error'   => '«'.$case['title'].'» закончился на складе',
        'qty'     => $current
    ]);
    exit;
}

$_SESSION['cart'][$caseId]['qty'] = $newQty;

echo json_encode([
    'success'  => true,
    'qty'      => $newQty,
    'subtotal' => $newQty * (float)$case['price'],
    'clamped'  => $clamped,
    'stock'    => $stock,
    'message'  => $clamped ? 'Максимум в наличии: '.$stock.' шт.' : null
]);
