<?php
require_once dirname(__DIR__) . '/bootstrap.php';
$pageTitle = 'Заказы — iSharlotka Admin';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
require_once dirname(__DIR__) . '/config/db.php';

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $allowed = ['pending','processing','completed','cancelled'];
    if (in_array($_POST['status'], $allowed)) {
        $pdo->prepare("UPDATE orders SET status=? WHERE order_id=?")->execute([$_POST['status'],(int)$_POST['order_id']]);
    }
    redirect('/admin/orders.php?updated=1');
}

$orderId = (int)($_GET['id'] ?? 0);
$statusLabels = ['pending'=>'Ожидает','processing'=>'Обрабатывается','completed'=>'Выполнен','cancelled'=>'Отменён'];

if ($orderId) {
    $stmt = $pdo->prepare("SELECT o.*, u.login, u.fullname, u.email FROM orders o JOIN users u ON o.user_id=u.id_user WHERE o.order_id=?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    if (!$order) { redirect('/admin/orders.php'); }
    $items = $pdo->prepare("SELECT oc.*, c.title, c.price, c.image FROM order_composition oc JOIN cases_catalog c ON oc.id_case=c.id_case WHERE oc.id_order=?");
    $items->execute([$orderId]);
    $orderItems = $items->fetchAll();
}

$orders = $pdo->query("SELECT o.*, u.login, u.fullname FROM orders o JOIN users u ON o.user_id=u.id_user ORDER BY o.date DESC")->fetchAll();

require_once __DIR__ . '/header.php';
?>
<div class="admin-page-header">
    <h1><?= $orderId ? 'Заказ #'.$orderId : 'Все заказы' ?></h1>
    <?php
 if ($orderId): ?><a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-ghost">← Все заказы</a><?php endif; ?>
</div>

<?php
 if (isset($_GET['updated'])): ?><div class="alert alert-success">✓ Статус обновлён.</div><?php endif; ?>

<?php
 if ($orderId && isset($order)): ?>
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:2rem;align-items:start">
        <div class="admin-form-card">
            <h3 style="font-family:var(--font-display);font-size:1.2rem;color:var(--cream);margin-bottom:1.25rem">Состав заказа</h3>
            <div style="display:flex;flex-direction:column;gap:0.75rem">
                <?php
 foreach ($orderItems as $item): ?>
                    <div style="display:flex;justify-content:space-between;padding:0.875rem;background:var(--surface);border-radius:8px;border:1px solid var(--border)">
                        <div>
                            <div style="color:var(--cream)"><?= htmlspecialchars($item['title']) ?></div>
                            <?php
 if ($item['custom_design']): ?>
                                <div style="font-size:0.75rem;color:var(--gold);margin-top:0.2rem;font-style:italic">✦ <?= htmlspecialchars($item['custom_design']) ?></div>
                            <?php
 endif; ?>
                            <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.2rem">× <?= $item['count'] ?></div>
                        </div>
                        <div style="color:var(--gold);font-weight:500"><?= number_format($item['price']*$item['count'],0,'.',' ') ?> ₽</div>
                    </div>
                <?php
 endforeach; ?>
            </div>
            <div style="display:flex;justify-content:space-between;padding-top:1rem;margin-top:0.5rem;border-top:1px solid var(--border);font-size:1.1rem">
                <span style="color:var(--text-muted)">Итого:</span>
                <span style="color:var(--gold);font-weight:500"><?= number_format($order['price'],0,'.',' ') ?> ₽</span>
            </div>
        </div>
        <div>
            <div class="admin-form-card" style="margin-bottom:1.5rem">
                <h3 style="font-family:var(--font-display);font-size:1.1rem;color:var(--cream);margin-bottom:1rem">Покупатель</h3>
                <div style="font-size:0.875rem;display:flex;flex-direction:column;gap:0.4rem">
                    <div><span style="color:var(--text-muted)">Логин: </span><?= htmlspecialchars($order['login']) ?></div>
                    <div><span style="color:var(--text-muted)">Имя: </span><?= htmlspecialchars($order['fullname'] ?: '—') ?></div>
                    <div><span style="color:var(--text-muted)">Email: </span><?= htmlspecialchars($order['email']) ?></div>
                    <div style="margin-top:0.5rem"><span style="color:var(--text-muted)">Дата: </span><?= date('d.m.Y H:i', strtotime($order['date'])) ?></div>
                </div>
            </div>
            <div class="admin-form-card">
                <h3 style="font-family:var(--font-display);font-size:1.1rem;color:var(--cream);margin-bottom:1rem">Статус заказа</h3>
                <form method="POST" action="">
                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                    <div class="form-group">
                        <select class="form-control" name="status">
                            <?php
 foreach ($statusLabels as $val => $label): ?>
                                <option value="<?= $val ?>" <?= $order['status']===$val?'selected':'' ?>><?= $label ?></option>
                            <?php
 endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm btn-full">Обновить статус</button>
                </form>
            </div>
        </div>
    </div>
<?php
 else: ?>
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>#</th><th>Покупатель</th><th>Дата</th><th>Сумма</th><th>Статус</th><th></th></tr></thead>
            <tbody>
                <?php
 foreach ($orders as $o): ?>
                    <tr>
                        <td><?= $o['order_id'] ?></td>
                        <td style="color:var(--cream)"><?= htmlspecialchars($o['fullname']?:'@'.$o['login']) ?></td>
                        <td><?= date('d.m.Y H:i', strtotime($o['date'])) ?></td>
                        <td style="color:var(--gold)"><?= number_format($o['price'],0,'.',' ') ?> ₽</td>
                        <td><span class="order-status status-<?= $o['status'] ?>"><?= $statusLabels[$o['status']] ?></span></td>
                        <td><a href="<?= BASE_URL ?>/admin/orders.php?id=<?= $o['order_id'] ?>" class="btn btn-outline btn-sm">Открыть</a></td>
                    </tr>
                <?php
 endforeach; ?>
            </tbody>
        </table>
    </div>
<?php
 endif; ?>

<?php
 require_once __DIR__ . '/footer.php'; ?>
