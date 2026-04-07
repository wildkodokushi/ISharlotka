<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Админ — iSharlotka') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a href="<?= BASE_URL ?>/admin/index.php" class="logo">
            <span class="logo-icon">◈</span>
            <span class="logo-text">iSharlotka <span style="font-size:0.7rem;color:var(--gold);letter-spacing:0.15em;text-transform:uppercase;font-family:var(--font-body)">Admin</span></span>
        </a>
        <div style="margin-left:auto;display:flex;align-items:center;gap:1rem">
            <span style="font-size:0.8rem;color:var(--text-muted)">@<?= htmlspecialchars($_SESSION['login']) ?></span>
            <a href="<?= BASE_URL ?>/index.php" class="nav-link">На сайт</a>
            <a href="<?= BASE_URL ?>/logout.php" class="btn-logout">Выйти</a>
        </div>
    </div>
</header>
<div class="admin-layout">
<?php
 require_once __DIR__ . '/sidebar.php'; ?>
<div class="admin-content">
