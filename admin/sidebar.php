<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
require_once dirname(__DIR__) . '/config/db.php';
$adminPage = basename($_SERVER['PHP_SELF']);
$adminDir  = basename(dirname($_SERVER['PHP_SELF']));

function adminNav(string $href, string $label, string $icon, string $current): string {
    $active = (basename($href) === $current) ? ' active' : '';
    return "<a href='$href' class='admin-nav-link$active'>$icon $label</a>";
}
?>
<div class="admin-sidebar" id="admin-sidebar">
    <div class="admin-sidebar-title">Управление</div>
    <?= adminNav(BASE_URL . '/admin/index.php',    'Dashboard',       '⬡', $adminPage) ?>
    <?= adminNav(BASE_URL . '/admin/orders.php',   'Заказы',          '◻', $adminPage) ?>
    <div class="admin-divider"></div>
    <div class="admin-sidebar-title">Каталог</div>
    <?= adminNav(BASE_URL . '/admin/catalog.php',  'Чехлы',           '◈', $adminPage) ?>
    <?= adminNav(BASE_URL . '/admin/materials.php','Материалы',       '◇', $adminPage) ?>
    <?= adminNav(BASE_URL . '/admin/models.php',   'Модели устройств','◻', $adminPage) ?>
    <div class="admin-divider"></div>
    <div class="admin-sidebar-title">Пользователи</div>
    <?= adminNav(BASE_URL . '/admin/users.php',    'Пользователи',    '○', $adminPage) ?>
    <div class="admin-divider"></div>
    <?= adminNav(BASE_URL . '/index.php',          'На сайт',         '←', '') ?>
    <?= adminNav(BASE_URL . '/logout.php',         'Выйти',           '✕', '') ?>
</div>
