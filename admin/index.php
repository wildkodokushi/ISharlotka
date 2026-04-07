<?php
require_once dirname(__DIR__) . '/bootstrap.php';
$pageTitle = 'Dashboard — iSharlotka Admin';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
require_once dirname(__DIR__) . '/config/db.php';

$totalUsers   = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$totalOrders  = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalCases   = $pdo->query("SELECT COUNT(*) FROM cases_catalog")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(price),0) FROM orders WHERE status != 'cancelled'")->fetchColumn();

$recentOrders = $pdo->query("SELECT o.*, u.login, u.fullname FROM orders o
    JOIN users u ON o.user_id=u.id_user ORDER BY o.date DESC LIMIT 8")->fetchAll();
$statusLabels = ['pending'=>'Ожидает','processing'=>'Обрабатывается','completed'=>'Выполнен','cancelled'=>'Отменён'];

require_once __DIR__ . '/header.php';
?>

<div class="admin-page-header">
    <h1>Dashboard</h1>
    <span style="font-size:0.8rem;color:var(--text-muted)"><?= date('d.m.Y') ?></span>
</div>

<div class="stats-grid stagger">
    <div class="stat-card">
        <div class="stat-label">Пользователи</div>
        <div class="stat-value"><em><?= $totalUsers ?></em></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Заказы</div>
        <div class="stat-value"><em><?= $totalOrders ?></em></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Чехлов в каталоге</div>
        <div class="stat-value"><em><?= $totalCases ?></em></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Выручка</div>
        <div class="stat-value"><em><?= number_format($totalRevenue,0,'.',' ') ?></em> <span style="font-size:1rem;color:var(--text-muted)">₽</span></div>
    </div>
</div>

<h2 style="font-family:var(--font-display);font-size:1.3rem;color:var(--cream);margin-bottom:1rem">Последние заказы</h2>
<div class="table-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Покупатель</th>
                <th>Дата</th>
                <th>Сумма</th>
                <th>Статус</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
 foreach ($recentOrders as $o): ?>
                <tr>
                    <td><?= $o['order_id'] ?></td>
                    <td style="color:var(--cream)"><?= htmlspecialchars($o['fullname'] ?: $o['login']) ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($o['date'])) ?></td>
                    <td style="color:var(--gold)"><?= number_format($o['price'],0,'.',' ') ?> ₽</td>
                    <td><span class="order-status status-<?= $o['status'] ?>"><?= $statusLabels[$o['status']] ?></span></td>
                    <td><a href="<?= BASE_URL ?>/admin/orders.php?id=<?= $o['order_id'] ?>" class="btn btn-ghost btn-sm">Открыть</a></td>
                </tr>
            <?php
 endforeach; ?>
        </tbody>
    </table>
</div>

<?php
 require_once __DIR__ . '/footer.php'; ?>
