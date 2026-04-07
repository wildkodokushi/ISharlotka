<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/auth.php';
header('Content-Type: application/json');
if (!isLoggedIn()) { echo json_encode(['success'=>false]); exit; }
$caseId = (int)($_POST['case_id'] ?? 0);
cartRemove($caseId);
echo json_encode(['success'=>true,'count'=>cartCount()]);
