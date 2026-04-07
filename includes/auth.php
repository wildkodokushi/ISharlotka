<?php
require_once __DIR__ . '/../bootstrap.php';
if (session_status() === PHP_SESSION_NONE) session_start();

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireAuth(string $path = '/login.php'): void {
    if (!isLoggedIn()) {
        redirect($path);
    }
}

function requireAdmin(): void {
    if (!isAdmin()) {
        redirect('/index.php');
    }
}

function loginUser(array $user): void {
    $_SESSION['user_id']  = $user['id_user'];
    $_SESSION['login']    = $user['login'];
    $_SESSION['fullname'] = $user['fullname'];
    $_SESSION['role']     = $user['role'];
}

function logoutUser(): void {
    session_destroy();
    redirect('/index.php');
}

// Cart helpers (stored in session)
function cartAdd(int $caseId, int $qty = 1, string $customDesign = ''): void {
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    if (isset($_SESSION['cart'][$caseId])) {
        $_SESSION['cart'][$caseId]['qty'] += $qty;
    } else {
        $_SESSION['cart'][$caseId] = ['qty' => $qty, 'custom_design' => $customDesign];
    }
}

function cartRemove(int $caseId): void {
    unset($_SESSION['cart'][$caseId]);
}

function cartClear(): void {
    $_SESSION['cart'] = [];
}

function cartCount(): int {
    if (empty($_SESSION['cart'])) return 0;
    return array_sum(array_column($_SESSION['cart'], 'qty'));
}
