<?php
require_once __DIR__ . '/bootstrap.php';
$pageTitle = 'Мои заказы — iSharlotka';
require_once __DIR__ . '/includes/auth.php';
requireAuth('/login.php');
require_once __DIR__ . '/config/db.php';

$success = $_GET['success'] ?? '';
$newOrderId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT o.*, 
    (SELECT COUNT(*) FROM order_composition oc WHERE oc.id_order = o.order_id) AS items_count
    FROM orders o WHERE o.user_id = ? ORDER BY o.date DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

$statusLabels = ['pending'=>'Ожидает','processing'=>'Обрабатывается','completed'=>'Выполнен','cancelled'=>'Отменён'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
    <h1>Мои <em>заказы</em></h1>
</div>

<div class="container" style="padding:2.5rem 2rem 4rem">
    <?php
 if ($success === 'order'): ?>
        <div class="alert alert-success fade-in">✓ Заказ #<?= $newOrderId ?> успешно оформлен! Мы свяжемся с вами.</div>
    <?php
 endif; ?>

    <div class="profile-header fade-in">
        <div class="profile-avatar"><?= mb_strtoupper(mb_substr($_SESSION['fullname'] ?: $_SESSION['login'], 0, 1)) ?></div>
        <div>
            <div class="profile-name"><?= htmlspecialchars($_SESSION['fullname'] ?: $_SESSION['login']) ?></div>
            <div class="profile-role"><?= isAdmin() ? 'Администратор' : 'Покупатель' ?></div>
            <div style="font-size:0.8rem;color:var(--text-muted);margin-top:0.25rem">@<?= htmlspecialchars($_SESSION['login']) ?></div>
        </div>
    </div>

    <?php
 if (empty($orders)): ?>
        <div class="empty-state">
            <div class="icon">◈</div>
            <h3>Заказов пока нет</h3>
            <p>Оформите свой первый заказ в каталоге</p>
            <a href="<?= BASE_URL ?>/catalog.php" class="btn btn-primary">Перейти в каталог</a>
        </div>
    <?php
 else: ?>
        <div class="orders-list stagger">
            <?php
 foreach ($orders as $order):
                $items = $pdo->prepare("SELECT oc.*, c.title, c.price, c.image
                    FROM order_composition oc JOIN cases_catalog c ON oc.id_case=c.id_case
                    WHERE oc.id_order=?");
                $items->execute([$order['order_id']]);
                $orderItems = $items->fetchAll();
            ?>
                <div class="order-card">
                    <div class="order-card-header">
                        <div>
                            <div class="order-id">Заказ #<?= $order['order_id'] ?></div>
                            <div class="order-date"><?= date('d.m.Y, H:i', strtotime($order['date'])) ?></div>
                        </div>
                        <span class="order-status status-<?= $order['status'] ?>">
                            <?= $statusLabels[$order['status']] ?? $order['status'] ?>
                        </span>
                    </div>
                    <div class="order-card-body">
                        <div class="order-items-mini">
                            <?php
 foreach ($orderItems as $item): ?>
                                <div class="order-item-mini">
                                    <span><?= htmlspecialchars($item['title']) ?> × <?= $item['count'] ?>
                                        <?php
 if ($item['custom_design']): ?>
                                            <em style="color:var(--gold);font-size:0.75rem"> (авт. дизайн)</em>
                                        <?php
 endif; ?>
                                    </span>
                                    <span><?= number_format($item['price'] * $item['count'], 0, '.', ' ') ?> ₽</span>
                                </div>
                            <?php
 endforeach; ?>
                        </div>
                        <div class="order-total">
                            <span>Сумма заказа</span>
                            <span><?= number_format($order['price'], 0, '.', ' ') ?> ₽</span>
                        </div>
                    </div>
                </div>
            <?php
 endforeach; ?>
        </div>
    <?php
 endif; ?>
</div>

<?php
 require_once __DIR__ . '/includes/footer.php'; ?>
