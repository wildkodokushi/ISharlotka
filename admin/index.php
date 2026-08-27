<?php
require_once dirname(__DIR__) . '/bootstrap.php';
$pageTitle = 'Дашборд — iSharlotka Admin';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
require_once dirname(__DIR__) . '/config/db.php';

$totalUsers    = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$totalOrders   = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalCases    = $pdo->query("SELECT COUNT(*) FROM cases_catalog")->fetchColumn();
$totalRevenue  = $pdo->query("SELECT COALESCE(SUM(price),0) FROM orders WHERE status='completed'")->fetchColumn();
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
$pendingReviews= $pdo->query("SELECT COUNT(*) FROM reviews WHERE status='pending'")->fetchColumn();

$recentOrders = $pdo->query("SELECT o.*, u.login, u.fullname FROM orders o
    JOIN users u ON o.user_id=u.id_user ORDER BY o.date DESC LIMIT 6")->fetchAll();

$topCases = $pdo->query("SELECT c.title, COUNT(oc.id_case) as sold
    FROM order_composition oc JOIN cases_catalog c ON oc.id_case=c.id_case
    GROUP BY oc.id_case ORDER BY sold DESC LIMIT 5")->fetchAll();

$statusLabels = ['pending'=>'Ожидает','processing'=>'Обрабатывается','completed'=>'Выполнен','cancelled'=>'Отменён'];

require_once __DIR__ . '/header.php';
?>

<div class="admin-page-header">
    <h1>Дашборд</h1>
    <span style="font-size:0.8rem;color:var(--text-muted)"><?= date('d F Y') ?></span>
</div>

<!-- Главные метрики -->
<div class="stats-grid stagger" style="margin-bottom:2.5rem">
    <div class="stat-card">
        <div class="stat-label">Выручка (выполнено)</div>
        <div class="stat-value"><em><?= number_format($totalRevenue,0,'.',' ') ?></em> <span style="font-size:1rem;color:var(--text-muted)">₽</span></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Всего заказов</div>
        <div class="stat-value"><em><?= $totalOrders ?></em>
            <?php if ($pendingOrders > 0): ?>
                <span style="font-size:0.8rem;color:#E8A040;margin-left:0.5rem"><?= $pendingOrders ?> новых</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Чехлов в каталоге</div>
        <div class="stat-value"><em><?= $totalCases ?></em></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Пользователей</div>
        <div class="stat-value"><em><?= $totalUsers ?></em></div>
    </div>
</div>

<!-- Уведомления -->
<?php if ($pendingOrders > 0 || $pendingReviews > 0): ?>
<div style="display:flex;gap:1rem;margin-bottom:2rem;flex-wrap:wrap">
    <?php if ($pendingOrders > 0): ?>
    <a href="<?= BASE_URL ?>/admin/orders.php" style="display:flex;align-items:center;gap:0.6rem;background:rgba(232,160,64,0.08);border:1px solid rgba(232,160,64,0.3);border-radius:8px;padding:0.75rem 1.2rem;text-decoration:none;color:var(--cream)">
        <span style="color:#E8A040;font-size:1.1rem">◻</span>
        <span><strong style="color:#E8A040"><?= $pendingOrders ?></strong> заказа ждут обработки</span>
        <span style="color:var(--gold);margin-left:0.5rem">→</span>
    </a>
    <?php endif; ?>
    <?php if ($pendingReviews > 0): ?>
    <a href="<?= BASE_URL ?>/admin/reviews.php" style="display:flex;align-items:center;gap:0.6rem;background:rgba(200,169,110,0.08);border:1px solid rgba(200,169,110,0.3);border-radius:8px;padding:0.75rem 1.2rem;text-decoration:none;color:var(--cream)">
        <span style="color:var(--gold);font-size:1.1rem">★</span>
        <span><strong style="color:var(--gold)"><?= $pendingReviews ?></strong> отзыва ждут модерации</span>
        <span style="color:var(--gold);margin-left:0.5rem">→</span>
    </a>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Два столбца: последние заказы + топ чехлов -->
<div style="display:grid;grid-template-columns:1fr 340px;gap:2rem;align-items:start">

    <!-- Последние заказы -->
    <div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
            <h2 style="font-family:var(--font-display);font-size:1.2rem;color:var(--cream)">Последние заказы</h2>
            <a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-ghost btn-sm">Все заказы →</a>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr><th>#</th><th>Покупатель</th><th>Дата</th><th>Сумма</th><th>Статус</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $o): ?>
                    <tr>
                        <td><?= $o['order_id'] ?></td>
                        <td style="color:var(--cream)"><?= htmlspecialchars($o['fullname'] ?: $o['login']) ?></td>
                        <td><?= date('d.m H:i', strtotime($o['date'])) ?></td>
                        <td style="color:var(--gold)"><?= number_format($o['price'],0,'.',' ') ?> ₽</td>
                        <td><span class="order-status status-<?= $o['status'] ?>"><?= $statusLabels[$o['status']] ?></span></td>
                        <td><a href="<?= BASE_URL ?>/admin/orders.php?id=<?= $o['order_id'] ?>" class="btn btn-ghost btn-sm">→</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Топ чехлов -->
    <div>
        <h2 style="font-family:var(--font-display);font-size:1.2rem;color:var(--cream);margin-bottom:1rem">Топ продаж</h2>
        <div style="display:flex;flex-direction:column;gap:0.6rem">
            <?php if ($topCases): ?>
                <?php foreach ($topCases as $i => $tc): ?>
                <div style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem;background:var(--card-bg);border:1px solid var(--border);border-radius:8px">
                    <span style="font-family:var(--font-display);font-size:1.4rem;color:var(--border);width:1.5rem;text-align:center"><?= $i+1 ?></span>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:0.85rem;color:var(--cream);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($tc['title']) ?></div>
                        <div style="font-size:0.75rem;color:var(--text-muted)"><?= $tc['sold'] ?> продаж</div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="color:var(--text-muted);font-size:0.9rem;padding:1rem">Пока нет данных</div>
            <?php endif; ?>
        </div>

        <!-- Быстрые ссылки -->
        <h2 style="font-family:var(--font-display);font-size:1.2rem;color:var(--cream);margin:1.5rem 0 1rem">Быстрые действия</h2>
        <div style="display:flex;flex-direction:column;gap:0.5rem">
            <a href="<?= BASE_URL ?>/admin/add_case.php" class="btn btn-primary btn-sm" style="justify-content:center">+ Добавить чехол</a>
            <a href="<?= BASE_URL ?>/admin/collections.php" class="btn btn-ghost btn-sm" style="justify-content:center">+ Добавить коллекцию</a>
            <a href="<?= BASE_URL ?>/admin/reviews.php" class="btn btn-ghost btn-sm" style="justify-content:center">Модерация отзывов</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
