<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/auth.php';
$cartCount   = cartCount();
$currentPage = basename($_SERVER['PHP_SELF']);

// ── SEO defaults (переопределяются конкретной страницей до подключения header.php) ──
$pageTitle       = $pageTitle       ?? 'iSharlotka — Авторские чехлы для телефона';
$pageDescription = $pageDescription ?? 'iSharlotka — интернет-магазин авторских чехлов для мобильных устройств с онлайн-конструктором. Персонализируй свой чехол: выбери цвет, добавь фото, надпись и стикеры.';
$pageImage       = $pageImage       ?? (BASE_URL . '/og-image.jpg');
$pageCanonical   = $pageCanonical   ?? (BASE_URL . '/' . $currentPage . (isset($_GET['id']) ? '?id=' . (int)$_GET['id'] : ''));
$pageNoIndex     = $pageNoIndex     ?? false; // true для приватных страниц (корзина, профиль и т.д.)
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- ── Primary SEO ──────────────────────────────────────────── -->
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($pageCanonical) ?>">
    <?php if ($pageNoIndex): ?>
    <meta name="robots" content="noindex, nofollow">
    <?php else: ?>
    <meta name="robots" content="index, follow">
    <?php endif; ?>
    <meta name="theme-color" content="#0C0C0E">

    <!-- ── Open Graph ───────────────────────────────────────────── -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="iSharlotka">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($pageImage) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($pageCanonical) ?>">
    <meta property="og:locale" content="ru_RU">

    <!-- ── Twitter Card ─────────────────────────────────────────── -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($pageImage) ?>">

    <!-- ── Favicon ──────────────────────────────────────────────── -->
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/favicon.svg">

    <!-- ── Styles (шрифты подключены через @import внутри style.css) ── -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">

    <!-- ── Structured data ──────────────────────────────────────── -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "iSharlotka",
        "url": "<?= BASE_URL ?>/",
        "description": "Интернет-магазин авторских чехлов для мобильных устройств с онлайн-конструктором",
        "logo": "<?= BASE_URL ?>/favicon.svg"
    }
    </script>

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
