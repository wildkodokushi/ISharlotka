<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/auth.php';
$cartCount = cartCount();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'iSharlotka — Авторские чехлы') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <script>window.BASE_URL = '<?= BASE_URL ?>';</script>
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a href="<?= BASE_URL ?>/index.php" class="logo">
            <span class="logo-icon">◈</span>
            <span class="logo-text">iSharlotka</span>
        </a>
        <nav class="nav-main">
            <?php
 if (isLoggedIn()): ?>
                <a href="<?= BASE_URL ?>/catalog.php" class="nav-link <?= $currentPage==='catalog.php'?'active':'' ?>">Каталог</a>
                <a href="<?= BASE_URL ?>/profile.php" class="nav-link <?= $currentPage==='profile.php'?'active':'' ?>">Мои заказы</a>
                <?php
 if (isAdmin()): ?>
                    <a href="<?= BASE_URL ?>/admin/index.php" class="nav-link nav-admin">Панель</a>
                <?php
 endif; ?>
            <?php
 else: ?>
                <a href="<?= BASE_URL ?>/login.php" class="nav-link">Войти</a>
                <a href="<?= BASE_URL ?>/register.php" class="nav-link">Регистрация</a>
            <?php
 endif; ?>
        </nav>
        <div class="header-actions">
            <?php
 if (isLoggedIn()): ?>
                <a href="<?= BASE_URL ?>/cart.php" class="btn-cart <?= $currentPage==='cart.php'?'active':'' ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                    <?php
 if ($cartCount > 0): ?><span class="cart-badge"><?= $cartCount ?></span><?php endif; ?>
                </a>
                <a href="<?= BASE_URL ?>/logout.php" class="btn-logout">Выйти</a>
            <?php
 endif; ?>
        </div>
        <button class="burger" id="burger" aria-label="Меню">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>
<main class="site-main">
