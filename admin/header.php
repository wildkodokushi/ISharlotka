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
    <style>
        /* ── ADMIN TOP-NAV LAYOUT ─────────────────────── */
        .admin-topbar {
            background: #0a0a0e;
            border-bottom: 1px solid var(--border);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            gap: 0;
            height: 56px;
            position: sticky;
            top: 60px;
            z-index: 90;
        }
        .admin-topbar-brand {
            font-family: var(--font-display);
            font-size: 1rem;
            color: var(--gold);
            letter-spacing: 0.05em;
            margin-right: 2rem;
            white-space: nowrap;
        }
        .admin-nav-tabs {
            display: flex;
            gap: 0;
            flex: 1;
            overflow-x: auto;
            scrollbar-width: none;
        }
        .admin-nav-tabs::-webkit-scrollbar { display: none; }
        .admin-tab {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0 1.1rem;
            height: 56px;
            font-size: 0.82rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text-muted);
            text-decoration: none;
            border-bottom: 2px solid transparent;
            white-space: nowrap;
            transition: color 0.2s, border-color 0.2s;
        }
        .admin-tab:hover { color: var(--cream); }
        .admin-tab.active { color: var(--gold); border-bottom-color: var(--gold); }
        .admin-tab-group-label {
            padding: 0 0.5rem 0 1.2rem;
            font-size: 0.65rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #444;
            height: 56px;
            display: flex;
            align-items: center;
        }
        .admin-tab-divider {
            width: 1px;
            height: 24px;
            background: var(--border);
            margin: 0 0.5rem;
            align-self: center;
        }
        .admin-topbar-end {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-left: auto;
            flex-shrink: 0;
        }
        .admin-topbar-user {
            font-size: 0.78rem;
            color: var(--text-muted);
        }
        .admin-main-content {
            max-width: 1300px;
            margin: 0 auto;
            padding: 2rem 2rem 4rem;
        }
        .admin-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }
        .admin-page-header h1 {
            font-family: var(--font-display);
            font-size: 1.8rem;
            font-weight: 300;
            color: var(--cream);
        }
        @media (max-width: 768px) {
            .admin-topbar { padding: 0 1rem; }
            .admin-tab-group-label { display: none; }
            .admin-tab { padding: 0 0.7rem; font-size: 0.75rem; }
        }
    </style>
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a href="<?= BASE_URL ?>/admin/index.php" class="logo">
            <span class="logo-icon">◈</span>
            <span class="logo-text">iSharlotka <span style="font-size:0.7rem;color:var(--gold);letter-spacing:0.15em;font-family:var(--font-body)">Admin</span></span>
        </a>
        <div style="margin-left:auto;display:flex;align-items:center;gap:1rem">
            <span style="font-size:0.8rem;color:var(--text-muted)">@<?= htmlspecialchars($_SESSION['login']) ?></span>
            <a href="<?= BASE_URL ?>/index.php" class="nav-link">← На сайт</a>
            <a href="<?= BASE_URL ?>/logout.php" class="btn-logout">Выйти</a>
        </div>
    </div>
</header>

<?php
$adminPage = basename($_SERVER['PHP_SELF']);
function aTab(string $href, string $icon, string $label, string $cur): string {
    $active = (basename($href) === $cur) ? ' active' : '';
    return "<a href='$href' class='admin-tab$active'>$icon $label</a>";
}
?>
<nav class="admin-topbar">
    <?php echo aTab(BASE_URL.'/admin/index.php',       '⬡', 'Дашборд',          $adminPage); ?>
    <div class="admin-tab-divider"></div>
    <span class="admin-tab-group-label">Магазин</span>
    <?php echo aTab(BASE_URL.'/admin/orders.php',      '◻', 'Заказы',           $adminPage); ?>
    <?php echo aTab(BASE_URL.'/admin/reviews.php',     '★', 'Отзывы',           $adminPage); ?>
    <div class="admin-tab-divider"></div>
    <span class="admin-tab-group-label">Каталог</span>
    <?php echo aTab(BASE_URL.'/admin/catalog.php',     '◈', 'Чехлы',            $adminPage); ?>
    <?php echo aTab(BASE_URL.'/admin/collections.php', '◇', 'Коллекции',        $adminPage); ?>
    <?php echo aTab(BASE_URL.'/admin/materials.php',   '○', 'Материалы',        $adminPage); ?>
    <?php echo aTab(BASE_URL.'/admin/models.php',      '◻', 'Модели',           $adminPage); ?>
    <div class="admin-tab-divider"></div>
    <span class="admin-tab-group-label">Система</span>
    <?php echo aTab(BASE_URL.'/admin/users.php',       '◇', 'Пользователи',     $adminPage); ?>
</nav>

<div class="admin-main-content">
