<?php
require_once __DIR__ . '/bootstrap.php';
$pageTitle = 'Товар — iSharlotka';
require_once __DIR__ . '/includes/auth.php';
requireAuth('/login.php');
require_once __DIR__ . '/config/db.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT c.*, m.material_name, d.firm, d.model_name
    FROM cases_catalog c
    LEFT JOIN materials m ON c.material_id = m.id_material
    LEFT JOIN device_models d ON c.model_id = d.id_model
    WHERE c.id_case = ?");
$stmt->execute([$id]);
$case = $stmt->fetch();
if (!$case) { redirect('/catalog.php'); }
$pageTitle = htmlspecialchars($case['title']) . ' — iSharlotka';

// Get all materials and models for selectors
$materials = $pdo->query("SELECT * FROM materials ORDER BY material_name")->fetchAll();
$models    = $pdo->query("SELECT * FROM device_models ORDER BY firm, model_name")->fetchAll();

// Old price = +20%
$oldPrice = round($case['price'] * 1.22 / 10) * 10;

require_once __DIR__ . '/includes/header.php';
?>

<div class="product-card-page">
    <!-- Breadcrumb -->
    <div class="container" style="padding-bottom:0.75rem">
        <div class="breadcrumb" style="margin-bottom:0">
            <a href="<?= BASE_URL ?>/catalog.php">Каталог</a>
            <span>›</span>
            <?php if ($case['collection']): ?>
                <a href="<?= BASE_URL ?>/catalog.php?collection=<?= urlencode($case['collection']) ?>"><?= htmlspecialchars($case['collection']) ?></a>
                <span>›</span>
            <?php endif; ?>
            <span style="color:var(--text)"><?= htmlspecialchars($case['title']) ?></span>
        </div>
    </div>

    <!-- Main layout: thumbnails | main image | info -->
    <div class="product-card-layout fade-in">

        <!-- Thumbnails column -->
        <div class="thumb-gallery">
            <button class="thumb-nav-btn" id="thumb-up" aria-label="Выше">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
            </button>
            <?php
            // Generate 4 thumb placeholders (first is "active")
            $thumbColors = ['#1a1a22','#16161e','#1e1a14','#141820'];
            for ($t = 0; $t < 4; $t++): ?>
                <div class="thumb-item <?= $t===0?'active':'' ?>" onclick="setThumb(this, <?= $t ?>)" data-idx="<?= $t ?>">
                    <?php if ($case['image'] && file_exists(__DIR__.'/uploads/cases/'.$case['image'])): ?>
                        <img src="<?= BASE_URL ?>/uploads/cases/<?= htmlspecialchars($case['image']) ?>" alt="">
                    <?php else: ?>
                        <span class="thumb-placeholder">◈</span>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
            <button class="thumb-nav-btn" id="thumb-down" aria-label="Ниже">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
        </div>

        <!-- Main image -->
        <div class="main-image-wrap" id="main-img-wrap">
            <?php if ($case['image'] && file_exists(__DIR__.'/uploads/cases/'.$case['image'])): ?>
                <img id="main-img" src="<?= BASE_URL ?>/uploads/cases/<?= htmlspecialchars($case['image']) ?>"
                     alt="<?= htmlspecialchars($case['title']) ?>">
            <?php else: ?>
                <div class="main-image-placeholder">
                    <span class="phone-icon">📱</span>
                    <span style="font-size:0.8rem;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted)"><?= htmlspecialchars($case['collection'] ?? 'Case') ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Info panel -->
        <div class="product-info-panel">
            <!-- Title + rating -->
            <div class="product-title-row">
                <h1 class="product-card-title"><?= htmlspecialchars($case['title']) ?></h1>
                <div class="product-rating">
                    <div class="stars" id="stars-row">
                        <?php for ($s = 1; $s <= 5; $s++): ?>
                            <span class="star <?= $s<=4?'':'empty' ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <span class="rating-count">17</span>
                    <button class="fav-btn" id="fav-btn" onclick="toggleFav(this)">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        В избранное
                    </button>
                </div>
            </div>

            <!-- Stock -->
            <div class="stock-badge <?= $case['count']>0?'in-stock':'out-stock' ?>">
                <span class="stock-dot"></span>
                <?= $case['count']>0 ? 'В наличии' : 'Нет в наличии' ?>
            </div>

            <!-- Device selector -->
            <div>
                <div style="font-size:0.75rem;color:var(--text-muted);letter-spacing:0.08em;text-transform:uppercase;margin-bottom:0.5rem">Устройство</div>
                <div class="options-row" id="model-options">
                    <?php foreach (array_slice($models, 0, 5) as $i => $m): ?>
                        <div class="option-chip <?= ($m['id_model']==$case['model_id'])?'active':'' ?>"
                             onclick="selectOption(this, 'model-options')"
                             data-id="<?= $m['id_model'] ?>">
                            <?= htmlspecialchars($m['firm'].' '.$m['model_name']) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Material selector -->
            <div>
                <div style="font-size:0.75rem;color:var(--text-muted);letter-spacing:0.08em;text-transform:uppercase;margin-bottom:0.5rem">Материал</div>
                <div class="options-row" id="material-options">
                    <?php foreach ($materials as $i => $m): ?>
                        <div class="option-chip <?= ($m['id_material']==$case['material_id'])?'active':'' ?>"
                             onclick="selectOption(this, 'material-options')"
                             data-id="<?= $m['id_material'] ?>">
                            <?= htmlspecialchars($m['material_name']) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Price -->
            <div class="price-block">
                <div>
                    <div class="price-old"><?= number_format($oldPrice,0,'.',' ') ?> ₽</div>
                    <div class="price-current"><?= number_format($case['price'],0,'.',' ') ?> <span style="font-size:1.5rem">₽</span></div>
                </div>
                <div class="price-badge">
                    При покупке от 3-х чехлов<br>
                    Стоимость 1 чехла составит <?= number_format($case['price']*0.5,0,'.',' ') ?> ₽/Шт.
                </div>
            </div>

            <!-- Specs -->
            <div class="specs-list">
                <div class="spec-row"><span class="sl">Артикул:</span><span class="sv">S<?= str_pad($case['id_case'], 4, '0', STR_PAD_LEFT) ?>-<?= rand(100,999) ?></span></div>
                <?php if ($case['inscription']): ?>
                    <div class="spec-row"><span class="sl">Модель:</span><span class="sv"><?= htmlspecialchars($case['inscription']) ?></span></div>
                <?php endif; ?>
                <?php if ($case['material_name']): ?>
                    <div class="spec-row"><span class="sl">Материал:</span><span class="sv"><?= htmlspecialchars($case['material_name']) ?></span></div>
                <?php endif; ?>
                <?php if ($case['collection']): ?>
                    <div class="spec-row"><span class="sl">Коллекция:</span><span class="sv"><?= htmlspecialchars($case['collection']) ?></span></div>
                <?php endif; ?>
                <?php if ($case['color']): ?>
                    <div class="spec-row"><span class="sl">Цвет:</span><span class="sv"><?= htmlspecialchars($case['color']) ?></span></div>
                <?php endif; ?>
            </div>

            <!-- CTA buttons -->
            <div class="product-cta">
                <a href="<?= BASE_URL ?>/constructor.php?id=<?= $case['id_case'] ?>" class="btn-constructor">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    В конструктор
                </a>
                <button class="btn-cart-outline" onclick="addToCart(<?= $case['id_case'] ?>, 1, '')">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                    В корзину
                </button>
            </div>

            <?php if ($case['description']): ?>
                <div style="font-size:0.875rem;color:var(--text-muted);line-height:1.7;border-top:1px solid var(--border);padding-top:1rem">
                    <?= nl2br(htmlspecialchars($case['description'])) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Guarantees bar -->
    <div class="guarantees-bar">
        <div class="guarantee-item">Обмен и возврат в течение 14 дней</div>
        <div class="guarantee-item">Гарантия 90 дней</div>
        <div class="guarantee-item">Производство чехла 24 часа</div>
        <div class="guarantee-item">Оплата при получении</div>
    </div>
</div>

<script>
function setThumb(el, idx) {
    document.querySelectorAll('.thumb-item').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
}
function selectOption(el, groupId) {
    document.querySelectorAll('#' + groupId + ' .option-chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
}
function toggleFav(btn) {
    btn.classList.toggle('active');
    const svg = btn.querySelector('svg');
    if (btn.classList.contains('active')) {
        svg.setAttribute('fill', '#E8647A');
        svg.setAttribute('stroke', '#E8647A');
    } else {
        svg.setAttribute('fill', 'none');
        svg.setAttribute('stroke', 'currentColor');
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
