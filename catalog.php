<?php
require_once __DIR__ . '/bootstrap.php';
$pageTitle = 'Каталог авторских чехлов — iSharlotka';
$pageDescription = 'Каталог чехлов для телефона с фильтром по модели устройства, материалу и коллекции. Силикон, кожа, TPU — выбери стиль и оформи заказ онлайн.';
$pageNoIndex = true; // страница доступна только авторизованным пользователям
require_once __DIR__ . '/includes/auth.php';
requireAuth();
require_once __DIR__ . '/config/db.php';

$search       = trim($_GET['search'] ?? '');
$modelId      = (int)($_GET['model'] ?? 0);
$materialId   = (int)($_GET['material'] ?? 0);
$collectionId = (int)($_GET['collection'] ?? 0);
$sort         = $_GET['sort'] ?? 'newest';

$where  = ['1=1'];
$params = [];

if ($search)       { $where[] = '(c.title LIKE ? OR c.description LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($modelId)      { $where[] = 'c.model_id = ?';      $params[] = $modelId; }
if ($materialId)   { $where[] = 'c.material_id = ?';   $params[] = $materialId; }
if ($collectionId) { $where[] = 'c.collection_id = ?'; $params[] = $collectionId; }

$whereStr = implode(' AND ', $where);

switch ($sort) {
    case 'price_asc':  $orderBy = 'c.price ASC';       break;
    case 'price_desc': $orderBy = 'c.price DESC';      break;
    case 'name_asc':   $orderBy = 'c.title ASC';       break;
    default:           $orderBy = 'c.created_at DESC'; break;
}

$sql = "SELECT c.*, m.material_name, d.firm, d.model_name, col.name as col_name,
               COALESCE(AVG(r.rating),0) as avg_rating,
               COUNT(DISTINCT r.id_review) as review_count
        FROM cases_catalog c
        LEFT JOIN materials m ON c.material_id = m.id_material
        LEFT JOIN device_models d ON c.model_id = d.id_model
        LEFT JOIN collections col ON c.collection_id = col.id_collection
        LEFT JOIN reviews r ON r.case_id = c.id_case AND r.status = 'approved'
        WHERE $whereStr
        GROUP BY c.id_case
        ORDER BY $orderBy";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cases = $stmt->fetchAll();

// Sidebar data
$models      = $pdo->query("SELECT * FROM device_models ORDER BY firm, model_name")->fetchAll();
$materials   = $pdo->query("SELECT * FROM materials ORDER BY material_name")->fetchAll();
$collections = $pdo->query("SELECT col.*, COUNT(c.id_case) as cnt FROM collections col
    LEFT JOIN cases_catalog c ON c.collection_id = col.id_collection
    GROUP BY col.id_collection ORDER BY col.name")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="catalog-page">
    <div class="container">

        <h1 class="visually-hidden">Каталог авторских чехлов для телефона — iSharlotka</h1>


        <!-- ── FILTER TOOLBAR ─────────────────────────────────── -->
        <form method="get" class="catalog-toolbar">

            <div class="search-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" placeholder="Поиск по названию..."
                       value="<?= htmlspecialchars($search) ?>">
            </div>

            <div class="filter-group">
                <select name="model" class="filter-select" onchange="this.form.submit()">
                    <option value="">Все устройства</option>
                    <?php $curFirm = ''; foreach ($models as $m): ?>
                        <?php if ($m['firm'] !== $curFirm): $curFirm = $m['firm']; ?>
                            <optgroup label="<?= htmlspecialchars($m['firm']) ?>">
                        <?php endif; ?>
                        <option value="<?= $m['id_model'] ?>" <?= $modelId == $m['id_model'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['model_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <select name="material" class="filter-select" onchange="this.form.submit()">
                    <option value="">Все материалы</option>
                    <?php foreach ($materials as $mat): ?>
                        <option value="<?= $mat['id_material'] ?>" <?= $materialId == $mat['id_material'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($mat['material_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <select name="collection" class="filter-select" onchange="this.form.submit()">
                    <option value="">Все коллекции</option>
                    <?php foreach ($collections as $col): ?>
                        <option value="<?= $col['id_collection'] ?>" <?= $collectionId == $col['id_collection'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($col['name']) ?> (<?= $col['cnt'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <select name="sort" class="filter-select" onchange="this.form.submit()">
                    <option value="newest"     <?= $sort === 'newest'     ? 'selected' : '' ?>>Сначала новые</option>
                    <option value="price_asc"  <?= $sort === 'price_asc'  ? 'selected' : '' ?>>Цена: по возрастанию</option>
                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Цена: по убыванию</option>
                    <option value="name_asc"   <?= $sort === 'name_asc'   ? 'selected' : '' ?>>По названию</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-sm">Найти</button>
            <?php if ($search || $modelId || $materialId || $collectionId): ?>
                <a href="<?= BASE_URL ?>/catalog.php" class="btn btn-ghost btn-sm">Сбросить</a>
            <?php endif; ?>

        </form>

        <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:1.5rem">
            Найдено чехлов: <strong style="color:var(--cream)"><?= count($cases) ?></strong>
        </p>

        <!-- ── GRID ────────────────────────────────────────────── -->
        <?php if (!$cases): ?>
        <div style="text-align:center;padding:4rem 0">
            <div style="font-size:2.5rem;opacity:.2;margin-bottom:1rem">◈</div>
            <p style="color:var(--text-muted);margin-bottom:1.5rem">Ничего не найдено. Попробуйте изменить параметры поиска.</p>
            <a href="<?= BASE_URL ?>/catalog.php" class="btn btn-outline">Сбросить фильтры</a>
        </div>
        <?php else: ?>

        <div class="product-grid stagger">
            <?php foreach ($cases as $case):
                $outOfStock = (int)$case['count'] <= 0;
            ?>
            <div class="product-card <?= $outOfStock ? 'out-of-stock' : '' ?>">
                <a href="<?= BASE_URL ?>/product.php?id=<?= $case['id_case'] ?>" style="display:block;text-decoration:none">
                    <div class="product-image">
                        <?php if ($case['image'] && file_exists(__DIR__ . '/uploads/cases/' . $case['image'])): ?>
                            <img src="<?= BASE_URL ?>/uploads/cases/<?= htmlspecialchars($case['image']) ?>"
                                 alt="<?= htmlspecialchars($case['title']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="product-image-placeholder">
                                <span>◈</span>
                            </div>
                        <?php endif; ?>

                        <?php if ($case['col_name']): ?>
                            <span class="product-badge"><?= htmlspecialchars($case['col_name']) ?></span>
                        <?php endif; ?>

                        <?php if ($outOfStock): ?>
                            <span class="product-oos-badge">Нет в наличии</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-info">
                        <div class="product-collection">
                            <?= htmlspecialchars(($case['firm'] . ' ' . $case['model_name']) ?: '—') ?>
                        </div>
                        <h3 class="product-title"><?= htmlspecialchars($case['title']) ?></h3>
                        <div class="product-meta">
                            <?php if ($case['material_name']): ?>
                                <span><?= htmlspecialchars($case['material_name']) ?></span>
                            <?php endif; ?>
                            <?php if ((float)$case['avg_rating'] > 0): ?>
                                <span>
                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                        <span style="color:<?= $s <= $case['avg_rating'] ? 'var(--gold)' : 'var(--border)' ?>;font-size:0.75rem">★</span>
                                    <?php endfor; ?>
                                    (<?= $case['review_count'] ?>)
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="product-footer">
                            <div class="product-price"><?= number_format($case['price'], 0, '.', ' ') ?> ₽</div>
                        </div>
                    </div>
                </a>

                <!-- Button outside the link so it's clickable independently -->
                <div style="padding:0 1.25rem 1.25rem">
                    <?php if (!$outOfStock): ?>
                        <button class="btn btn-outline btn-sm" style="width:100%"
                                onclick="addToCart(<?= $case['id_case'] ?>, 1, '')">
                            В корзину
                        </button>
                    <?php else: ?>
                        <button class="btn btn-ghost btn-sm" style="width:100%;opacity:.5;cursor:not-allowed" disabled>
                            Нет в наличии
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
