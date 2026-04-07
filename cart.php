<?php
require_once __DIR__ . '/bootstrap.php';
$pageTitle = 'Корзина — iSharlotka';
require_once __DIR__ . '/includes/auth.php';
requireAuth('/login.php');
require_once __DIR__ . '/config/db.php';

// Get cart items from session
$cartData = $_SESSION['cart'] ?? [];
$cartItems = [];
$total = 0;
if (!empty($cartData)) {
    $ids = array_keys($cartData);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT c.*, m.material_name, d.firm, d.model_name
        FROM cases_catalog c
        LEFT JOIN materials m ON c.material_id=m.id_material
        LEFT JOIN device_models d ON c.model_id=d.id_model
        WHERE c.id_case IN ($placeholders)");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $row) {
        $qty = $cartData[$row['id_case']]['qty'] ?? 1;
        $custom = $cartData[$row['id_case']]['custom_design'] ?? '';
        $row['qty'] = $qty;
        $row['custom_design'] = $custom;
        $row['subtotal'] = $row['price'] * $qty;
        $total += $row['subtotal'];
        $cartItems[] = $row;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
    <h1>Моя <em>корзина</em></h1>
</div>

<div class="container cart-page">
    <?php
 if (empty($cartItems)): ?>
        <div class="cart-empty fade-in">
            <div class="empty-icon">◈</div>
            <h3>Корзина пуста</h3>
            <p>Вы ещё ничего не добавили. Откройте каталог и выберите чехол.</p>
            <a href="<?= BASE_URL ?>/catalog.php" class="btn btn-primary">Перейти в каталог</a>
        </div>
    <?php
 else: ?>
        <div class="cart-layout">
            <div>
                <div class="cart-items stagger">
                    <?php
 foreach ($cartItems as $item): ?>
                        <div class="cart-item">
                            <div class="cart-item-image">
                                <?php
 if ($item['image'] && file_exists('uploads/cases/'.$item['image'])): ?>
                                    <img src="<?= BASE_URL ?>/uploads/cases/<?= htmlspecialchars($item['image']) ?>" alt="">
                                <?php
 else: ?>
                                    <span style="font-size:1.8rem;opacity:0.3">◈</span>
                                <?php
 endif; ?>
                            </div>
                            <div class="cart-item-info">
                                <h4><a href="<?= BASE_URL ?>/product.php?id=<?= $item['id_case'] ?>"><?= htmlspecialchars($item['title']) ?></a></h4>
                                <p>
                                    <?= htmlspecialchars($item['firm'].' '.$item['model_name']) ?>
                                    <?php
 if ($item['material_name']): ?> · <?= htmlspecialchars($item['material_name']) ?><?php endif; ?>
                                </p>
                                <?php
 if ($item['custom_design']): ?>
                                    <div class="cart-item-custom">✦ <?= htmlspecialchars($item['custom_design']) ?></div>
                                <?php
 endif; ?>
                                <div style="font-size:0.8rem;color:var(--text-muted)">Кол-во: <?= $item['qty'] ?></div>
                            </div>
                            <div class="cart-item-actions">
                                <div class="cart-item-price"><?= number_format($item['subtotal'],0,'.',' ') ?> ₽</div>
                                <button class="btn btn-danger btn-sm"
                                    onclick="removeFromCart(<?= $item['id_case'] ?>)">Удалить</button>
                            </div>
                        </div>
                    <?php
 endforeach; ?>
                </div>
                <div style="margin-top:1.5rem;display:flex;gap:1rem">
                    <a href="<?= BASE_URL ?>/catalog.php" class="btn btn-ghost">← Продолжить покупки</a>
                    <form method="POST" action="<?= BASE_URL ?>/api/cart_clear.php" style="display:inline">
                        <button type="submit" class="btn btn-ghost" style="color:var(--error)">Очистить корзину</button>
                    </form>
                </div>
            </div>

            <div class="order-summary fade-in">
                <h3>Итого</h3>
                <?php
 foreach ($cartItems as $item): ?>
                    <div class="summary-line">
                        <span><?= htmlspecialchars($item['title']) ?> × <?= $item['qty'] ?></span>
                        <span><?= number_format($item['subtotal'],0,'.',' ') ?> ₽</span>
                    </div>
                <?php
 endforeach; ?>
                <div class="summary-line">
                    <span>Итого</span>
                    <span><?= number_format($total,0,'.',' ') ?> ₽</span>
                </div>
                <a href="<?= BASE_URL ?>/checkout.php" class="btn btn-primary btn-full" style="margin-top:1.5rem">
                    Оформить заказ
                </a>
            </div>
        </div>
    <?php
 endif; ?>
</div>

<?php
 require_once __DIR__ . '/includes/footer.php'; ?>
