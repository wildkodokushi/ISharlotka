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
    LEFT JOIN collections col ON c.collection_id = col.id_collection
    WHERE c.id_case = ?");
$stmt->execute([$id]);
$case = $stmt->fetch();
if (!$case) { redirect('/catalog.php'); }

/**
 * Грубое сопоставление русского названия цвета с hex-кодом для 3D-превью.
 * Если цвет не распознан — возвращает приглушённый золотисто-серый по умолчанию.
 */
function hexFromColorName(string $name): string {
    $map = [
        'розовый'        => '#E8A8C0',
        'чёрный'         => '#1A1A1E',
        'черный'         => '#1A1A1E',
        'тёмно-синий'    => '#1E2A4A',
        'темно-синий'    => '#1E2A4A',
        'синий'          => '#3458A8',
        'серый'          => '#8A8A92',
        'оранжевый'      => '#D88A48',
        'белый'          => '#EDE8DD',
        'зелёный'        => '#5A8A5E',
        'зеленый'        => '#5A8A5E',
        'фиолетовый'     => '#7A5C9E',
        'золотой'        => '#C8A96E',
        'красный'        => '#A8453E',
        'бежевый'        => '#D6C4A8',
    ];
    $key = mb_strtolower(trim($name));
    return $map[$key] ?? '#3A3A42';
}
$pageTitle = $case['title'] . ' — купить чехол для ' . trim(($case['firm'] ?? '') . ' ' . ($case['model_name'] ?? '')) . ' | iSharlotka';
$descParts = [];
if ($case['material_name']) $descParts[] = 'материал: ' . $case['material_name'];
if ($case['firm'] || $case['model_name']) $descParts[] = 'для ' . trim(($case['firm'] ?? '') . ' ' . ($case['model_name'] ?? ''));
$pageDescription = 'Чехол «' . $case['title'] . '» — ' . implode(', ', $descParts) .
                   '. Цена ' . number_format($case['price'], 0, '.', ' ') . ' ₽. Доступна персонализация в онлайн-конструкторе.';
if ($case['image'] && file_exists(__DIR__ . '/uploads/cases/' . $case['image'])) {
    $pageImage = BASE_URL . '/uploads/cases/' . $case['image'];
}
$pageNoIndex = true; // страница доступна только авторизованным пользователям

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
            <?php if ($case['col_name']): ?>
                <a href="<?= BASE_URL ?>/catalog.php?collection=<?= urlencode($case['col_name']) ?>"><?= htmlspecialchars($case['col_name']) ?></a>
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
                    <span style="font-size:0.8rem;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted)"><?= htmlspecialchars($case['col_name'] ?? 'Case') ?></span>
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
<?php
                        $isFav = false;
                        try {
                            $favStmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id=? AND case_id=?");
                            $favStmt->execute([$_SESSION['user_id'], $id]);
                            $isFav = (bool)$favStmt->fetchColumn();
                        } catch (PDOException $e) {
                            // Таблица favorites ещё не создана — продолжаем без ошибки
                            $isFav = false;
                        }
                    ?>
                    <button class="fav-btn <?= $isFav ? 'fav-active' : '' ?>" id="fav-btn"
                            data-case-id="<?= $id ?>" onclick="toggleFav(this)">
                        <svg width="15" height="15" viewBox="0 0 24 24"
                             fill="<?= $isFav ? 'var(--gold)' : 'none' ?>"
                             stroke="<?= $isFav ? 'var(--gold)' : 'currentColor' ?>"
                             stroke-width="1.5" class="fav-icon">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                        <span class="fav-label"><?= $isFav ? 'В избранном' : 'В избранное' ?></span>
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
                             data-id="<?= $m['id_model'] ?>"
                             data-name="<?= htmlspecialchars($m['firm'].' '.$m['model_name']) ?>">
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
                             data-id="<?= $m['id_material'] ?>"
                             data-name="<?= htmlspecialchars($m['material_name']) ?>">
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
                <?php if ($case['col_name']): ?>
                    <div class="spec-row"><span class="sl">Коллекция:</span><span class="sv"><?= htmlspecialchars($case['col_name']) ?></span></div>
                <?php endif; ?>
                <?php if ($case['color']): ?>
                    <div class="spec-row"><span class="sl">Цвет:</span><span class="sv"><?= htmlspecialchars($case['color']) ?></span></div>
                <?php endif; ?>
            </div>

            <!-- CTA buttons -->
            <div class="product-cta">
                <a href="<?= BASE_URL ?>/constructor.php?id=<?= $case['id_case'] ?>" class="btn-constructor" id="btnToConstructor">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    В конструктор
                </a>
                <button class="btn-cart-outline" id="btnAddToCart">
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
const baseCaseId = <?= $case['id_case'] ?>;
let selectedModelId    = <?= (int)$case['model_id'] ?>;
let selectedMaterialId  = <?= (int)$case['material_id'] ?>;
let selectedModelName    = '<?= htmlspecialchars(($case['firm'] ?? '').' '.($case['model_name'] ?? ''), ENT_QUOTES) ?>';
let selectedMaterialName = '<?= htmlspecialchars($case['material_name'] ?? '', ENT_QUOTES) ?>';

function setThumb(el, idx) {
    document.querySelectorAll('.thumb-item').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
}

function selectOption(el, groupId) {
    document.querySelectorAll('#' + groupId + ' .option-chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');

    const id   = el.dataset.id;
    const name = el.dataset.name;

    if (groupId === 'model-options') {
        selectedModelId   = id;
        selectedModelName = name;
    } else if (groupId === 'material-options') {
        selectedMaterialId   = id;
        selectedMaterialName = name;
    }
    updateConstructorLink();
}

function updateConstructorLink() {
    const link = document.getElementById('btnToConstructor');
    if (link) {
        link.href = '<?= BASE_URL ?>/constructor.php?id=' + baseCaseId +
                     '&model=' + encodeURIComponent(selectedModelId) +
                     '&material=' + encodeURIComponent(selectedMaterialId);
    }
}

document.getElementById('btnAddToCart').addEventListener('click', function () {
    const customParts = [];
    customParts.push('Устройство: ' + selectedModelName);
    customParts.push('Материал: ' + selectedMaterialName);
    const customDesign = customParts.join(', ');
    addToCart(baseCaseId, 1, customDesign);
});

updateConstructorLink();
</script>


<?php
// ── REVIEWS ──────────────────────────────────────────────
// Handle review submit
$reviewMsg = '';
$reviewErr = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_text'])) {
    $rating = (int)($_POST['rating'] ?? 0);
    $text   = trim($_POST['review_text'] ?? '');
    if ($rating < 1 || $rating > 5) {
        $reviewErr = 'Выберите оценку от 1 до 5';
    } elseif (mb_strlen($text) < 10) {
        $reviewErr = 'Напишите отзыв (минимум 10 символов)';
    } else {
        // Check if user already left review on this case
        $exists = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE user_id=? AND case_id=?");
        $exists->execute([$_SESSION['user_id'], $id]);
        if ($exists->fetchColumn()) {
            $reviewErr = 'Вы уже оставили отзыв на этот чехол';
        } else {
            $pdo->prepare("INSERT INTO reviews (user_id, case_id, rating, text, status) VALUES (?,?,?,?,'pending')")
                ->execute([$_SESSION['user_id'], $id, $rating, $text]);
            $reviewMsg = 'Спасибо! Отзыв отправлен на модерацию.';
        }
    }
}

// Load approved reviews
$reviews = $pdo->prepare("SELECT r.*, u.fullname, u.login
    FROM reviews r JOIN users u ON r.user_id=u.id_user
    WHERE r.case_id=? AND r.status='approved' ORDER BY r.created_at DESC");
$reviews->execute([$id]);
$reviews = $reviews->fetchAll();

$avgRating = count($reviews) ? round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1) : 0;

// Check if current user already reviewed
$userReviewed = false;
$userReviewStmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE user_id=? AND case_id=?");
$userReviewStmt->execute([$_SESSION['user_id'], $id]);
$userReviewed = (bool)$userReviewStmt->fetchColumn();
?>

<div class="reviews-section">
    <div class="container">
        <div class="reviews-header">
            <h2 class="reviews-title">Отзывы покупателей</h2>
            <?php if (count($reviews)): ?>
            <div class="reviews-summary">
                <span class="reviews-avg"><?= $avgRating ?></span>
                <div>
                    <div class="reviews-stars-lg">
                        <?php for($s=1;$s<=5;$s++): ?>
                            <span style="color:<?= $s<=$avgRating?'var(--gold)':'var(--border)' ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <span class="reviews-count"><?= count($reviews) ?> <?= count($reviews)===1?'отзыв':( count($reviews)<5?'отзыва':'отзывов') ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="reviews-layout">
            <!-- Reviews list -->
            <div class="reviews-list">
                <?php if (!$reviews): ?>
                    <div class="reviews-empty">Отзывов пока нет. Будьте первым!</div>
                <?php else: ?>
                    <?php foreach ($reviews as $r): ?>
                    <div class="review-card">
                        <div class="review-top">
                            <div class="review-avatar"><?= mb_strtoupper(mb_substr($r['fullname'] ?: $r['login'], 0, 1)) ?></div>
                            <div class="review-meta">
                                <div class="review-author"><?= htmlspecialchars($r['fullname'] ?: $r['login']) ?></div>
                                <div class="review-date"><?= date('d.m.Y', strtotime($r['created_at'])) ?></div>
                            </div>
                            <div class="review-stars">
                                <?php for($s=1;$s<=5;$s++): ?>
                                    <span style="color:<?= $s<=$r['rating']?'var(--gold)':'var(--border)' ?>">★</span>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="review-text"><?= htmlspecialchars($r['text']) ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Review form -->
            <div class="review-form-wrap">
                <h3 class="review-form-title">Оставить отзыв</h3>
                <?php if ($reviewMsg): ?>
                    <div class="review-msg-ok"><?= htmlspecialchars($reviewMsg) ?></div>
                <?php elseif ($userReviewed): ?>
                    <div class="review-msg-ok">Вы уже оставили отзыв на этот чехол.</div>
                <?php else: ?>
                    <?php if ($reviewErr): ?>
                        <div class="review-msg-err"><?= htmlspecialchars($reviewErr) ?></div>
                    <?php endif; ?>
                    <form method="post" class="review-form">
                        <div class="star-picker">
                            <span class="star-picker-label">Ваша оценка</span>
                            <div class="star-picker-stars" id="starPicker">
                                <?php for($s=1;$s<=5;$s++): ?>
                                <span class="star-pick" data-val="<?= $s ?>" onclick="pickStar(<?= $s ?>)">★</span>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating" id="ratingInput" value="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ваш отзыв</label>
                            <textarea name="review_text" class="form-input" rows="4"
                                placeholder="Расскажите о качестве, доставке, впечатлениях..."
                                required minlength="10"></textarea>
                        </div>
                        <button class="btn btn-primary" style="width:100%">Отправить отзыв</button>
                        <p style="font-size:0.78rem;color:var(--text-muted);margin-top:0.5rem;text-align:center">
                            Отзыв появится после проверки модератором
                        </p>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function pickStar(val) {
    document.getElementById('ratingInput').value = val;
    document.querySelectorAll('.star-pick').forEach(function(s) {
        s.style.color = parseInt(s.dataset.val) <= val ? 'var(--gold)' : 'var(--border)';
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
function toggleFav(btn) {
    const caseId = btn.dataset.caseId;
    const icon   = btn.querySelector('.fav-icon');
    const label  = btn.querySelector('.fav-label');

    fetch('<?= BASE_URL ?>/api/fav_toggle.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'case_id=' + caseId
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'added') {
            icon.setAttribute('fill', 'var(--gold)');
            icon.setAttribute('stroke', 'var(--gold)');
            btn.classList.add('fav-active');
            if (label) label.textContent = 'В избранном';
            showToast('Добавлено в избранное ♥', 'success');
        } else if (data.status === 'removed') {
            icon.setAttribute('fill', 'none');
            icon.setAttribute('stroke', 'currentColor');
            btn.classList.remove('fav-active');
            if (label) label.textContent = 'В избранное';
            showToast('Убрано из избранного', 'info');
        } else if (data.error) {
            showToast('Не удалось обновить избранное', 'error');
        }
    })
    .catch(() => showToast('Ошибка соединения', 'error'));
}
</script>

