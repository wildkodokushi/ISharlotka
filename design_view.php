<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

$byToken = isset($_GET['token']);
$byId    = isset($_GET['id']);

$design = null;

try {
    if ($byToken) {
        // Публичный просмотр по токену — авторизация не требуется
        $token = trim($_GET['token']);
        $stmt = $pdo->prepare("
            SELECT sd.*, c.title as case_title, c.price as case_price, c.image as case_image,
                   c.material_id as case_material_id, c.model_id as case_model_id
            FROM saved_designs sd
            JOIN cases_catalog c ON c.id_case = sd.case_id
            WHERE sd.share_token = ?");
        $stmt->execute([$token]);
        $design = $stmt->fetch();
    } elseif ($byId) {
        // Просмотр владельцем — требуется авторизация и совпадение user_id
        requireAuth('/login.php');
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("
            SELECT sd.*, c.title as case_title, c.price as case_price, c.image as case_image,
                   c.material_id as case_material_id, c.model_id as case_model_id
            FROM saved_designs sd
            JOIN cases_catalog c ON c.id_case = sd.case_id
            WHERE sd.id_design = ? AND sd.user_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        $design = $stmt->fetch();
    }
} catch (PDOException $e) {
    $design = null;
}

if (!$design) {
    $pageTitle   = 'Дизайн не найден — iSharlotka';
    $pageNoIndex = true;
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="container" style="padding:4rem 2rem;text-align:center">
        <div style="font-size:2.5rem;opacity:.2;margin-bottom:1rem">◈</div>
        <p style="color:var(--text-muted);margin-bottom:1.5rem">
            Дизайн не найден. Возможно, ссылка устарела или дизайн был удалён.
        </p>
        <a href="<?= BASE_URL ?>/catalog.php" class="btn btn-outline">Перейти в каталог</a>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$isOwner = isLoggedIn() && (int)$design['user_id'] === (int)($_SESSION['user_id'] ?? 0);

$designData = json_decode($design['design_data'], true) ?: [];
$bgColor      = $designData['bgColor']      ?? '#111111';
$bgGradient   = $designData['bgGradient']   ?? null;
$hasImage     = $designData['hasImage']     ?? false;
$imageDataUrl = $designData['imageDataUrl'] ?? null;
$texts        = $designData['texts']        ?? [];
$stickers     = $designData['stickers']     ?? [];
$designModelId    = $designData['modelId']    ?? $design['case_model_id'];
$designMaterialId = $designData['materialId'] ?? $design['case_material_id'];

// Подтянем названия модели/материала для отображения
$modelName = '';
$materialName = '';
if ($designModelId) {
    $m = $pdo->prepare("SELECT firm, model_name FROM device_models WHERE id_model = ?");
    $m->execute([$designModelId]);
    if ($row = $m->fetch()) { $modelName = $row['firm'] . ' ' . $row['model_name']; }
}
if ($designMaterialId) {
    $m = $pdo->prepare("SELECT material_name FROM materials WHERE id_material = ?");
    $m->execute([$designMaterialId]);
    if ($row = $m->fetch()) { $materialName = $row['material_name']; }
}

$pageTitle       = htmlspecialchars($design['title']) . ' — авторский дизайн на основе «' . htmlspecialchars($design['case_title']) . '» | iSharlotka';
$pageDescription = 'Посмотрите авторский дизайн чехла «' . htmlspecialchars($design['case_title']) . '», созданный в конструкторе iSharlotka.';
$pageNoIndex     = !$byToken; // публичные share-ссылки можно индексировать, приватные id-ссылки — нет
if ($design['case_image'] && file_exists(__DIR__ . '/uploads/cases/' . $design['case_image'])) {
    $pageImage = BASE_URL . '/uploads/cases/' . $design['case_image'];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-hero" style="padding:2.5rem 2rem 2rem">
    <h1><?= htmlspecialchars($design['title']) ?></h1>
    <p>Авторский дизайн на основе «<?= htmlspecialchars($design['case_title']) ?>»</p>
</div>

<div class="constructor-page">
<div class="constructor-layout fade-in">

    <!-- LEFT: Phone preview (reconstructed) -->
    <div class="phone-preview-wrap">
        <div class="phone-frame">
            <div class="phone-canvas" id="phone-canvas" style="position:relative;overflow:hidden">

                <!-- Background -->
                <div style="position:absolute;inset:0;background:<?= $bgGradient ? htmlspecialchars($bgGradient) : htmlspecialchars($bgColor) ?>;"></div>

                <!-- Uploaded image -->
                <?php if ($hasImage && $imageDataUrl): ?>
                    <img src="<?= htmlspecialchars($imageDataUrl) ?>" alt=""
                         style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:1;">
                <?php endif; ?>

                <!-- Texts -->
                <?php foreach ($texts as $t): ?>
                    <div style="
                        position:absolute;
                        top:<?= htmlspecialchars((string)($t['top'] ?? 50)) ?>%;
                        left:<?= htmlspecialchars((string)($t['left'] ?? 50)) ?>%;
                        transform:translate(-50%,-50%);
                        color:<?= htmlspecialchars($t['color'] ?? '#ffffff') ?>;
                        font-size:<?= (int)($t['size'] ?? 16) ?>px;
                        font-weight:<?= !empty($t['bold']) ? '700' : '400' ?>;
                        font-style:<?= !empty($t['italic']) ? 'italic' : 'normal' ?>;
                        text-align:center; white-space:nowrap;
                        text-shadow:0 1px 3px rgba(0,0,0,0.8);
                        z-index:2;
                    "><?= htmlspecialchars($t['val'] ?? '') ?></div>
                <?php endforeach; ?>

                <!-- Stickers -->
                <?php foreach ($stickers as $s): ?>
                    <div style="
                        position:absolute;
                        top:<?= htmlspecialchars((string)($s['top'] ?? 40)) ?>%;
                        left:<?= htmlspecialchars((string)($s['left'] ?? 40)) ?>%;
                        transform:translate(-50%,-50%);
                        font-size:32px; z-index:3;
                    "><?= htmlspecialchars($s['emoji'] ?? '') ?></div>
                <?php endforeach; ?>

            </div>
            <div class="phone-chrome"></div>
        </div>

        <p style="text-align:center;margin-top:1rem;font-size:0.78rem;color:var(--text-muted)">
            Сохранено <?= date('d.m.Y', strtotime($design['created_at'])) ?>
            <?php if ($isOwner): ?> · ваш дизайн<?php endif; ?>
        </p>
    </div>

    <!-- RIGHT: Info panel -->
    <div class="constructor-controls">

        <div class="ctrl-section">
            <div class="ctrl-label">Базовый чехол</div>
            <p style="color:var(--cream);font-size:1rem;margin-bottom:0.5rem">
                <?= htmlspecialchars($design['case_title']) ?>
            </p>
            <?php if ($modelName): ?>
                <p style="color:var(--text-muted);font-size:0.85rem">Устройство: <?= htmlspecialchars($modelName) ?></p>
            <?php endif; ?>
            <?php if ($materialName): ?>
                <p style="color:var(--text-muted);font-size:0.85rem">Материал: <?= htmlspecialchars($materialName) ?></p>
            <?php endif; ?>
        </div>

        <div class="constructor-price">
            <div>
                <div class="p-new"><?= number_format($design['case_price'],0,'.',' ') ?> <span style="font-size:1.2rem;color:var(--text-muted)">₽</span></div>
            </div>
            <?php if (isLoggedIn()): ?>
                <button class="btn-constructor" style="min-width:unset;flex:unset;padding:0.875rem 1.75rem"
                    onclick="addDesignToCart()">В корзину</button>
            <?php endif; ?>
        </div>

        <?php if (!isLoggedIn()): ?>
            <div class="alert" style="margin-top:0.75rem;font-size:0.85rem;text-align:center">
                <a href="<?= BASE_URL ?>/login.php" style="color:var(--gold)">Войдите</a>, чтобы добавить этот дизайн в корзину
            </div>
        <?php endif; ?>

        <?php if ($isOwner): ?>
        <button class="btn-save-design" onclick="shareThisDesign()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
            </svg>
            Поделиться этим дизайном
        </button>
        <a href="<?= BASE_URL ?>/profile.php" style="display:block;text-align:center;margin-top:0.75rem;font-size:0.8rem;color:var(--text-muted)">
            ← Назад в профиль
        </a>
        <?php else: ?>
        <a href="<?= BASE_URL ?>/product.php?id=<?= $design['case_id'] ?>" style="display:block;text-align:center;margin-top:0.75rem;font-size:0.8rem;color:var(--text-muted)">
            ← Смотреть базовый чехол
        </a>
        <?php endif; ?>

    </div>
</div>
</div>

<script>
function addDesignToCart() {
    const parts = [];
    <?php if ($modelName): ?>parts.push('Устройство: <?= htmlspecialchars($modelName, ENT_QUOTES) ?>');<?php endif; ?>
    <?php if ($materialName): ?>parts.push('Материал: <?= htmlspecialchars($materialName, ENT_QUOTES) ?>');<?php endif; ?>
    parts.push('Авторский дизайн «<?= htmlspecialchars($design['title'], ENT_QUOTES) ?>»');
    <?php if (!empty($texts)): ?>parts.push('текст: <?= htmlspecialchars(implode(', ', array_column($texts, 'val')), ENT_QUOTES) ?>');<?php endif; ?>
    <?php if (!empty($stickers)): ?>parts.push('со стикерами');<?php endif; ?>
    addToCart(<?= (int)$design['case_id'] ?>, 1, parts.join(', '));
}
function shareThisDesign() {
    const url = '<?= BASE_URL ?>/design_view.php?token=<?= htmlspecialchars($design['share_token']) ?>';
    navigator.clipboard.writeText(url).then(() => {
        showToast('Ссылка для шеринга скопирована!', 'success');
    }).catch(() => {
        const tmp = document.createElement('input');
        tmp.value = url; document.body.appendChild(tmp);
        tmp.select(); document.execCommand('copy'); tmp.remove();
        showToast('Ссылка для шеринга скопирована!', 'success');
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
