<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/auth.php';
header('Content-Type: application/json');
if (!isLoggedIn()) { echo json_encode(['success'=>false,'error'=>'Не авторизован']); exit; }

$caseId = (int)($_POST['case_id'] ?? 0);
$qty = max(1, (int)($_POST['qty'] ?? 1));
$customDesign = trim($_POST['custom_design'] ?? '');

if (!$caseId) { echo json_encode(['success'=>false,'error'=>'Неверный товар']); exit; }

cartAdd($caseId, $qty, $customDesign);
echo json_encode(['success'=>true, 'count'=>cartCount()]);
