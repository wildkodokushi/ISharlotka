<?php
require_once __DIR__ . '/bootstrap.php';
$pageTitle = 'Каталог — iSharlotka';
require_once __DIR__ . '/includes/auth.php';
requireAuth('/login.php');
require_once __DIR__ . '/config/db.php';

// Get filters
$search     = trim($_GET['search'] ?? '');
$modelId    = (int)($_GET['model'] ?? 0);
$materialId = (int)($_GET['material'] ?? 0);
$collection = trim($_GET['collection'] ?? '');
$sort       = $_GET['sort'] ?? 'newest';

// Build query
$where = ['1=1'];
$params = [];
if ($search) {
    $where[] = '(c.title LIKE ? OR c.description LIKE ? OR c.inscription LIKE ?)';
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}
if ($modelId) { $where[] = 'c.model_id = ?'; $params[] = $modelId; }
if ($materialId) { $where[] = 'c.material_id = ?'; $params[] = $materialId; }
if ($collection) { $where[] = 'c.collection = ?'; $params[] = $collection; }
$where[] = 'c.count > 0';

$orderMap = [
    'newest'    => 'c.created_at DESC',
    'oldest'    => 'c.created_at ASC',
    'price_asc' => 'c.price ASC',
    'price_desc'=> 'c.price DESC',
    'name'      => 'c.title ASC',
];
$orderBy = $orderMap[$sort] ?? 'c.created_at DESC';

$sql = "SELECT c.*, m.material_name, d.firm, d.model_name
        FROM cases_catalog c
        LEFT JOIN materials m ON c.material_id = m.id_material
        LEFT JOIN device_models d ON c.model_id = d.id_model
        WHERE " . implode(' AND ', $where) . "
        ORDER BY $orderBy";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cases = $stmt->fetchAll();

// Filters data
$models = $pdo->query("SELECT * FROM device_models ORDER BY firm, model_name")->fetchAll();
$materials = $pdo->query("SELECT * FROM materials ORDER BY material_name")->fetchAll();
$collections = $pdo->query("SELECT DISTINCT collection FROM cases_catalog WHERE collection IS NOT NULL AND collection != '' ORDER BY collection")->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
    <h1>Каталог <em>чехлов</em></h1>
    <p>Найдите свой идеальный авторский чехол</p>
</div>

<div class="catalog-page container">
    <!-- Filters -->
    <form id="filter-form" method="GET" action="" class="catalog-toolbar">
        <div class="search-box">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" id="search-input"
                value="<?= htmlspecialchars($search) ?>" placeholder="Поиск по названию, описанию...">
        </div>
        <div class="filter-group">
            <select name="model" class="filter-select" onchange="this.form.submit()">
                <option value="">Все устройства</option>
                <?php
 foreach ($models as $m): ?>
                    <option value="<?= $m['id_model'] ?>" <?= $modelId==$m['id_model']?'selected':'' ?>>
                        <?= htmlspecialchars($m['firm'].' '.$m['model_name']) ?>
                    </option>
                <?php
 endforeach; ?>
            </select>
            <select name="material" class="filter-select" onchange="this.form.submit()">
                <option value="">Все материалы</option>
                <?php
 foreach ($materials as $m): ?>
                    <option value="<?= $m['id_material'] ?>" <?= $materialId==$m['id_material']?'selected':'' ?>>
                        <?= htmlspecialchars($m['material_name']) ?>
                    </option>
                <?php
 endforeach; ?>
            </select>
            <select name="collection" class="filter-select" onchange="this.form.submit()">
                <option value="">Все коллекции</option>
                <?php
 foreach ($collections as $col): ?>
                    <option value="<?= htmlspecialchars($col) ?>" <?= $collection===$col?'selected':'' ?>>
                        <?= htmlspecialchars($col) ?>
                    </option>
                <?php
 endforeach; ?>
            </select>
            <select name="sort" class="filter-select" onchange="this.form.submit()">
                <option value="newest" <?= $sort==='newest'?'selected':'' ?>>Новинки</option>
                <option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>Цена ↑</option>
                <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Цена ↓</option>
                <option value="name" <?= $sort==='name'?'selected':'' ?>>А–Я</option>
            </select>
            <?php
 if ($search||$modelId||$materialId||$collection): ?>
                <a href="<?= BASE_URL ?>/catalog.php" class="btn btn-ghost btn-sm">✕ Сбросить</a>
            <?php
 endif; ?>
        </div>
    </form>

    <!-- Results count -->
    <p style="color:var(--text-muted);font-size:0.8rem;margin-bottom:1.5rem;letter-spacing:0.05em">
        Найдено: <strong style="color:var(--gold)"><?= count($cases) ?></strong> чехл<?= count($cases)===1?'':( count($cases)>=2&&count($cases)<=4?'а':'ов') ?>
    </p>

    <!-- Grid -->
    <?php
 if (empty($cases)): ?>
        <div class="empty-state">
            <div class="icon">◈</div>
            <h3>Ничего не найдено</h3>
            <p>Попробуйте изменить параметры фильтрации или поискового запроса</p>
            <a href="<?= BASE_URL ?>/catalog.php" class="btn btn-outline">Сбросить фильтры</a>
        </div>
    <?php
 else: ?>
        <div class="product-grid stagger">
            <?php
 foreach ($cases as $case): ?>
                <article class="product-card" onclick="location.href=BASE_URL+'/product.php?id=<?= $case['id_case'] ?>'">
                    <div class="product-image">
                        <?php
 if ($case['image'] && file_exists('uploads/cases/'.$case['image'])): ?>
                            <img src="<?= BASE_URL ?>/uploads/cases/<?= htmlspecialchars($case['image']) ?>" alt="<?= htmlspecialchars($case['title']) ?>">
                        <?php
 else: ?>
                            <div class="product-image-placeholder">
                                <span>◈</span>
                                <p><?= htmlspecialchars($case['collection'] ?? 'Case') ?></p>
                            </div>
                        <?php
 endif; ?>
                        <?php
 if ($case['sticker']): ?>
                            <div class="product-badge">Стикер</div>
                        <?php
 endif; ?>
                    </div>
                    <div class="product-info">
                        <?php
 if ($case['collection']): ?>
                            <div class="product-collection"><?= htmlspecialchars($case['collection']) ?></div>
                        <?php
 endif; ?>
                        <h3 class="product-title"><?= htmlspecialchars($case['title']) ?></h3>
                        <div class="product-meta">
                            <?php
 if ($case['model_name']): ?>
                                <span><?= htmlspecialchars($case['firm'].' '.$case['model_name']) ?></span>
                            <?php
 endif; ?>
                            <?php
 if ($case['material_name']): ?>
                                <span><?= htmlspecialchars($case['material_name']) ?></span>
                            <?php
 endif; ?>
                        </div>
                        <div class="product-footer">
                            <div class="product-price">
                                <?= number_format($case['price'], 0, '.', ' ') ?> <small>₽</small>
                            </div>
                            <button class="btn btn-outline btn-sm" onclick="event.stopPropagation();addToCart(<?= $case['id_case'] ?>,1)">В корзину</button>
                        </div>
                    </div>
                </article>
            <?php
 endforeach; ?>
        </div>
    <?php
 endif; ?>
</div>

<?php
 require_once __DIR__ . '/includes/footer.php'; ?>
