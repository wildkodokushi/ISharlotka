<?php
require_once __DIR__ . '/bootstrap.php';
$pageTitle = 'Оформление заказа — iSharlotka';
$pageDescription = 'Оформление заказа в интернет-магазине авторских чехлов iSharlotka.';
$pageNoIndex = true;
require_once __DIR__ . '/includes/auth.php';
requireAuth('/login.php');
require_once __DIR__ . '/config/db.php';

$cartData = $_SESSION['cart'] ?? [];
if (empty($cartData)) { redirect('/cart.php'); }

// Get cart items
$ids = array_keys($cartData);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM cases_catalog WHERE id_case IN ($placeholders)");
$stmt->execute($ids);
$cases = [];
foreach ($stmt->fetchAll() as $row) { $cases[$row['id_case']] = $row; }

$total = 0;
foreach ($cartData as $caseId => $item) {
    if (isset($cases[$caseId])) $total += $cases[$caseId]['price'] * $item['qty'];
}

// ── Финальная проверка остатков перед оформлением ──────────────────────
$stockIssues = [];
foreach ($cartData as $caseId => $item) {
    if (!isset($cases[$caseId])) continue;
    $available = (int)$cases[$caseId]['count'];
    $requested = (int)$item['qty'];
    if ($requested > $available) {
        $stockIssues[] = [
            'title'     => $cases[$caseId]['title'],
            'available' => $available,
            'requested' => $requested,
        ];
        // Подрезаем количество в сессии до доступного остатка
        $_SESSION['cart'][$caseId]['qty'] = max(0, $available);
        if ($available <= 0) { unset($_SESSION['cart'][$caseId]); }
    }
}

$error = '';
if ($stockIssues) {
    $msgs = [];
    foreach ($stockIssues as $issue) {
        $msgs[] = '«'.$issue['title'].'»: запрошено '.$issue['requested'].', в наличии '.$issue['available'].' шт.';
    }
    $error = 'Некоторых товаров не хватает на складе. Количество в корзине обновлено: ' . implode('; ', $msgs);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $stockIssues) {
    // Не позволяем оформить заказ, пока остатки не подтверждены повторно
    $_SESSION['flash_error'] = $error;
    redirect('/cart.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create order
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, price) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $total]);
        $orderId = $pdo->lastInsertId();
        foreach ($cartData as $caseId => $item) {
            if (!isset($cases[$caseId])) continue;

            // Списываем остаток. Если строк не затронуто — на складе не хватает,
            // откатываем всю транзакцию, чтобы не записать недостоверный заказ.
            $upd = $pdo->prepare("UPDATE cases_catalog SET count = count - ? WHERE id_case = ? AND count >= ?");
            $upd->execute([$item['qty'], $caseId, $item['qty']]);
            if ($upd->rowCount() === 0) {
                throw new Exception('Недостаточно товара «'.$cases[$caseId]['title'].'» на складе');
            }

            $stmt = $pdo->prepare("INSERT INTO order_composition (id_order, id_case, count, custom_design) VALUES (?,?,?,?)");
            $stmt->execute([$orderId, $caseId, $item['qty'], $item['custom_design'] ?? '']);
        }
        $pdo->commit();
        cartClear();
        redirect("/profile.php?success=order&id=$orderId");
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['flash_error'] = 'Не удалось оформить заказ: ' . $e->getMessage() . '. Проверьте корзину и попробуйте снова.';
        redirect('/cart.php');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
    <h1>Оформление <em>заказа</em></h1>
</div>

<div class="container-md" style="padding:3rem 2rem">
    <?php
 if ($error): ?><div class="alert alert-error">✕ <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 340px;gap:2.5rem;align-items:start">
        <div>
            <div class="admin-form-card fade-in">
                <h3 style="font-family:var(--font-display);font-size:1.3rem;color:var(--cream);margin-bottom:1.25rem">Ваш заказ</h3>
                <div style="display:flex;flex-direction:column;gap:0.75rem;margin-bottom:1.5rem">
                    <?php
 foreach ($cartData as $caseId => $item):
                        if (!isset($cases[$caseId])) continue;
                        $c = $cases[$caseId]; ?>
                        <div style="display:flex;justify-content:space-between;padding:1rem;background:var(--surface);border-radius:8px;border:1px solid var(--border)">
                            <div>
                                <div style="color:var(--cream);font-size:0.95rem"><?= htmlspecialchars($c['title']) ?></div>
                                <?php
 if ($item['custom_design']): ?>
                                    <div style="font-size:0.75rem;color:var(--gold);margin-top:0.25rem;font-style:italic">✦ <?= htmlspecialchars($item['custom_design']) ?></div>
                                <?php
 endif; ?>
                                <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.25rem">Кол-во: <?= $item['qty'] ?></div>
                            </div>
                            <div style="color:var(--cream);font-weight:500;white-space:nowrap">
                                <?= number_format($c['price'] * $item['qty'], 0, '.', ' ') ?> ₽
                            </div>
                        </div>
                    <?php
 endforeach; ?>
                </div>

                <div style="border-top:1px solid var(--border);padding-top:1rem;display:flex;justify-content:space-between;font-size:1.1rem;color:var(--cream);margin-bottom:2rem">
                    <span>Итого:</span><span style="color:var(--gold)"><?= number_format($total,0,'.',' ') ?> ₽</span>
                </div>

                <form method="POST" action="">
                    <div class="alert alert-info" style="margin-bottom:1.5rem">
                        ℹ Оплата при получении или онлайн — менеджер свяжется с вами после оформления.
                    </div>
                    <div style="display:flex;gap:1rem">
                        <a href="<?= BASE_URL ?>/cart.php" class="btn btn-ghost">← Назад в корзину</a>
                        <button type="submit" class="btn btn-primary btn-lg">Подтвердить заказ</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="order-summary fade-in" style="position:sticky;top:88px">
            <h3>Покупатель</h3>
            <div style="display:flex;flex-direction:column;gap:0.5rem;font-size:0.875rem">
                <div style="color:var(--text-muted)">Имя</div>
                <div style="color:var(--cream)"><?= htmlspecialchars($_SESSION['fullname'] ?: $_SESSION['login']) ?></div>
                <div style="color:var(--text-muted);margin-top:0.5rem">Логин</div>
                <div style="color:var(--cream)">@<?= htmlspecialchars($_SESSION['login']) ?></div>
            </div>
        </div>
    </div>
</div>

<?php
 require_once __DIR__ . '/includes/footer.php'; ?>
