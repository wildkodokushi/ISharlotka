<?php
require_once __DIR__ . '/bootstrap.php';
$pageTitle = 'Мой профиль — iSharlotka';
$pageDescription = 'Личный кабинет покупателя iSharlotka: история заказов и избранные чехлы.';
$pageNoIndex = true;
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

// Favorites
$favorites = [];
try {
    $favStmt = $pdo->prepare("
        SELECT c.*, m.material_name, d.firm, d.model_name, f.created_at as fav_date
        FROM favorites f
        JOIN cases_catalog c ON c.id_case = f.case_id
        LEFT JOIN materials m ON c.material_id = m.id_material
        LEFT JOIN device_models d ON c.model_id = d.id_model
        WHERE f.user_id = ? ORDER BY f.created_at DESC");
    $favStmt->execute([$_SESSION['user_id']]);
    $favorites = $favStmt->fetchAll();
} catch (PDOException $e) {
    // Таблица favorites ещё не создана в БД — показываем пустой список
    $favorites = [];
}

// Saved designs (templates from constructor)
$savedDesigns = [];
try {
    $designStmt = $pdo->prepare("
        SELECT sd.*, c.title as case_title, c.price as case_price, c.image as case_image
        FROM saved_designs sd
        JOIN cases_catalog c ON c.id_case = sd.case_id
        WHERE sd.user_id = ? ORDER BY sd.created_at DESC");
    $designStmt->execute([$_SESSION['user_id']]);
    $savedDesigns = $designStmt->fetchAll();
} catch (PDOException $e) {
    // Таблица saved_designs ещё не создана в БД
    $savedDesigns = [];
}

$statusLabels = ['pending'=>'Ожидает','processing'=>'Обрабатывается','completed'=>'Выполнен','cancelled'=>'Отменён'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
    <h1>Мой <em>профиль</em></h1>
</div>

<div class="container" style="padding:2.5rem 2rem 4rem">

    <!-- User info card -->
    <div class="profile-user-card fade-in">
        <div class="profile-avatar"><?= mb_strtoupper(mb_substr($_SESSION['fullname'] ?? $_SESSION['login'], 0, 1)) ?></div>
        <div class="profile-user-info">
            <div class="profile-user-name"><?= htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['login']) ?></div>
            <div class="profile-user-meta">
                <span>@<?= htmlspecialchars($_SESSION['login']) ?></span>
                <span class="profile-role-badge"><?= $_SESSION['role']==='admin' ? 'Администратор' : 'Покупатель' ?></span>
            </div>
        </div>
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-primary btn-sm" style="margin-left:auto">Панель админа →</a>
        <?php endif; ?>
    </div>

    <!-- Tabs -->
    <div class="profile-tabs">
        <button class="profile-tab active" onclick="switchTab('orders',this)" data-tab="orders">
            Мои заказы <span class="tab-count"><?= count($orders) ?></span>
        </button>
        <button class="profile-tab" onclick="switchTab('favorites',this)" data-tab="favorites">
            Избранное <span class="tab-count"><?= count($favorites) ?></span>
        </button>
        <button class="profile-tab" onclick="switchTab('designs',this)" data-tab="designs">
            Мои дизайны <span class="tab-count"><?= count($savedDesigns) ?></span>
        </button>
    </div>

    <!-- Orders tab -->
    <div id="tab-orders" class="profile-tab-content active">
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
                                <div class="order-item-mini-wrap">
                                    <div class="order-item-mini">
                                        <span><?= htmlspecialchars($item['title']) ?> × <?= $item['count'] ?></span>
                                        <span><?= number_format($item['price'] * $item['count'], 0, '.', ' ') ?> ₽</span>
                                    </div>
                                    <?php
 if ($item['custom_design']): ?>
                                        <div class="custom-design-tag">
                                            <span class="custom-design-icon">◈</span>
                                            <span class="custom-design-text"><?= htmlspecialchars($item['custom_design']) ?></span>
                                        </div>
                                    <?php
 endif; ?>
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
    </div><!-- /tab-orders -->

    <!-- Favorites tab -->
    <div id="tab-favorites" class="profile-tab-content" style="display:none">
        <?php if (empty($favorites)): ?>
        <div class="empty-state">
            <div class="empty-icon">♡</div>
            <p>В избранном пока ничего нет</p>
            <a href="<?= BASE_URL ?>/catalog.php" class="btn btn-primary">Перейти в каталог</a>
        </div>
        <?php else: ?>
        <div class="fav-grid stagger">
            <?php foreach ($favorites as $fav): ?>
            <div class="fav-card" id="fav-item-<?= $fav['id_case'] ?>">
                <a href="<?= BASE_URL ?>/product.php?id=<?= $fav['id_case'] ?>" class="fav-card-img">
                    <?php if ($fav['image'] && file_exists(__DIR__.'/uploads/cases/'.$fav['image'])): ?>
                        <img src="<?= BASE_URL ?>/uploads/cases/<?= htmlspecialchars($fav['image']) ?>"
                             alt="<?= htmlspecialchars($fav['title']) ?>" loading="lazy">
                    <?php else: ?>
                        <span class="fav-img-placeholder">◈</span>
                    <?php endif; ?>
                </a>
                <div class="fav-card-body">
                    <a href="<?= BASE_URL ?>/product.php?id=<?= $fav['id_case'] ?>" class="fav-card-title">
                        <?= htmlspecialchars($fav['title']) ?>
                    </a>
                    <div class="fav-card-meta">
                        <?= htmlspecialchars(($fav['firm'].' '.$fav['model_name']) ?: '') ?>
                        <?php if ($fav['material_name']): ?> · <?= htmlspecialchars($fav['material_name']) ?><?php endif; ?>
                    </div>
                    <div class="fav-card-price"><?= number_format($fav['price'],0,'.',' ') ?> ₽</div>
                    <div class="fav-card-actions">
                        <?php if ($fav['count'] > 0): ?>
                        <button class="btn btn-primary btn-sm"
                                onclick="addToCart(<?= $fav['id_case'] ?>,1,'')">В корзину</button>
                        <?php else: ?>
                        <span class="fav-oos">Нет в наличии</span>
                        <?php endif; ?>
                        <button class="btn btn-ghost btn-sm fav-remove-btn"
                                onclick="removeFav(<?= $fav['id_case'] ?>)">✕ Убрать</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div><!-- /tab-favorites -->

    <!-- Saved designs tab -->
    <div id="tab-designs" class="profile-tab-content" style="display:none">
        <?php
 if (empty($savedDesigns)): ?>
        <div class="empty-state">
            <div class="empty-icon">◈</div>
            <p>Вы пока не сохранили ни одного дизайна</p>
            <a href="<?= BASE_URL ?>/catalog.php" class="btn btn-primary">Перейти в каталог</a>
        </div>
        <?php
 else: ?>
        <div class="fav-grid stagger">
            <?php
 foreach ($savedDesigns as $design): ?>
            <div class="fav-card design-card" id="design-item-<?= $design['id_design'] ?>">
                <a href="<?= BASE_URL ?>/design_view.php?id=<?= $design['id_design'] ?>" class="fav-card-img">
                    <?php
 if ($design['case_image'] && file_exists(__DIR__.'/uploads/cases/'.$design['case_image'])): ?>
                        <img src="<?= BASE_URL ?>/uploads/cases/<?= htmlspecialchars($design['case_image']) ?>"
                             alt="<?= htmlspecialchars($design['title']) ?>" loading="lazy">
                    <?php
 else: ?>
                        <span class="fav-img-placeholder">◈</span>
                    <?php
 endif; ?>
                    <span class="design-card-badge">✦ Авторский дизайн</span>
                </a>
                <div class="fav-card-body">
                    <a href="<?= BASE_URL ?>/design_view.php?id=<?= $design['id_design'] ?>" class="fav-card-title">
                        <?= htmlspecialchars($design['title']) ?>
                    </a>
                    <div class="fav-card-meta">
                        на основе «<?= htmlspecialchars($design['case_title']) ?>» · <?= date('d.m.Y', strtotime($design['created_at'])) ?>
                    </div>
                    <div class="fav-card-actions">
                        <a href="<?= BASE_URL ?>/design_view.php?id=<?= $design['id_design'] ?>" class="btn btn-primary btn-sm">Просмотреть</a>
                        <button class="btn btn-ghost btn-sm" onclick="shareDesign('<?= $design['share_token'] ?>')">Поделиться</button>
                        <button class="btn btn-ghost btn-sm fav-remove-btn" onclick="removeDesign(<?= $design['id_design'] ?>)">✕ Удалить</button>
                    </div>
                </div>
            </div>
            <?php
 endforeach; ?>
        </div>
        <?php
 endif; ?>
    </div><!-- /tab-designs -->

</div>

<script>
function switchTab(name, btn) {
    document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.profile-tab-content').forEach(t => t.style.display = 'none');
    btn.classList.add('active');
    const tab = document.getElementById('tab-' + name);
    tab.style.display = '';
    tab.classList.add('active');
}

// Helper: count remaining cards in a tab and update its badge
function updateTabCount(tabId) {
    const tabName = tabId.replace('tab-', '');
    const tab = document.getElementById(tabId);
    if (!tab) return;
    const remaining = tab.querySelectorAll('.fav-card').length;
    const btn = document.querySelector(`.profile-tab[data-tab="${tabName}"]`);
    if (btn) {
        const badge = btn.querySelector('.tab-count');
        if (badge) badge.textContent = remaining;
    }
}

function removeFav(caseId) {
    fetch('<?= BASE_URL ?>/api/fav_toggle.php', {
        method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'case_id='+caseId
    }).then(r=>r.json()).then(data=>{
        if(data.status==='removed'){
            const el=document.getElementById('fav-item-'+caseId);
            if(el){
                el.style.opacity='0'; el.style.transition='opacity .3s';
                setTimeout(()=>{ el.remove(); updateTabCount('tab-favorites'); },300);
            }
            showToast('Убрано из избранного','info');
        }
    });
}

function shareDesign(token) {
    const url = '<?= BASE_URL ?>' + '/design_view.php?token=' + token;
    navigator.clipboard.writeText(url).then(() => {
        showToast('Ссылка для шеринга скопирована!', 'success');
    }).catch(() => {
        const tmp = document.createElement('input');
        tmp.value = url; document.body.appendChild(tmp);
        tmp.select(); document.execCommand('copy'); tmp.remove();
        showToast('Ссылка для шеринга скопирована!', 'success');
    });
}

function removeDesign(designId) {
    confirm('Удалить дизайн?', 'Это действие необратимо.', () => {
        const fd = new FormData();
        fd.append('design_id', designId);
        fetch('<?= BASE_URL ?>/api/design_delete.php', { method:'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const el = document.getElementById('design-item-' + designId);
                    if (el) {
                        el.style.opacity='0'; el.style.transition='opacity .3s';
                        setTimeout(()=>{ el.remove(); updateTabCount('tab-designs'); },300);
                    }
                    showToast('Дизайн удалён', 'info');
                } else {
                    showToast(data.error || 'Не удалось удалить', 'error');
                }
            });
    });
}
</script>

<?php
 require_once __DIR__ . '/includes/footer.php'; ?>
