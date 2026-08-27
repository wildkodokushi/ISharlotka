<?php
require_once __DIR__ . '/bootstrap.php';
$pageTitle = 'Онлайн-конструктор чехлов — iSharlotka';
$pageDescription = 'Создай уникальный чехол для телефона: выбери цвет корпуса, загрузи своё фото, добавь надпись и стикеры прямо в браузере.';
$pageNoIndex = true;
require_once __DIR__ . '/includes/auth.php';
requireAuth('/login.php');
require_once __DIR__ . '/config/db.php';

$id = (int)($_GET['id'] ?? 0);
$case = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT c.*, m.material_name, d.firm, d.model_name
        FROM cases_catalog c
        LEFT JOIN materials m ON c.material_id=m.id_material
        LEFT JOIN device_models d ON c.model_id=d.id_model
        WHERE c.id_case=?");
    $stmt->execute([$id]);
    $case = $stmt->fetch();
}

// Предвыбор модели/материала, переданный со страницы товара (выбор пользователя через чипы)
$preselectModelId    = isset($_GET['model'])    ? (int)$_GET['model']    : ($case['model_id']    ?? 0);
$preselectMaterialId = isset($_GET['material']) ? (int)$_GET['material'] : ($case['material_id'] ?? 0);

$materials = $pdo->query("SELECT * FROM materials ORDER BY material_name")->fetchAll();
$models    = $pdo->query("SELECT * FROM device_models ORDER BY firm, model_name")->fetchAll();
// Group models by firm
$firms = [];
foreach ($models as $m) { $firms[$m['firm']][] = $m; }

// Определяем фирму для предвыбранной модели (важно, если модель пришла через GET с другим брендом)
$preselectFirm = $case['firm'] ?? '';
foreach ($models as $m) {
    if ($m['id_model'] == $preselectModelId) { $preselectFirm = $m['firm']; break; }
}

$oldPrice = $case ? round($case['price'] * 1.22 / 10) * 10 : 890;
$price    = $case ? $case['price'] : 650;

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-hero" style="padding:2.5rem 2rem 2rem">
    <h1>Конструктор <em>чехлов</em></h1>
    <p>Выберите параметры и создайте уникальный дизайн</p>
</div>

<div class="constructor-page">
<div class="constructor-layout fade-in">

    <!-- LEFT: Phone preview -->
    <div class="phone-preview-wrap">
        <div class="phone-frame">
            <div class="phone-canvas" id="phone-canvas">
                <!-- Background colour layer -->
                <div id="canvas-color-bg" style="position:absolute;inset:0;background:#111;transition:background 0.3s;"></div>
                <!-- Template / uploaded image layer -->
                <img id="canvas-bg" src="" alt="" style="display:none;position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:1;">
                <!-- Text overlay layer -->
                <div id="text-overlays" style="position:absolute;inset:0;z-index:2;pointer-events:none;overflow:hidden;"></div>
                <!-- Sticker overlay layer -->
                <div id="sticker-overlays" style="position:absolute;inset:0;z-index:3;pointer-events:none;overflow:hidden;"></div>
                <!-- Placeholder text -->
                <div id="canvas-placeholder" style="position:absolute;inset:0;z-index:4;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1rem;pointer-events:none">
                    <span style="font-size:3.5rem;opacity:0.1">◈</span>
                    <span style="font-size:0.7rem;letter-spacing:0.15em;text-transform:uppercase;color:rgba(255,255,255,0.2)">Ваш дизайн</span>
                </div>
            </div>
            <div class="phone-chrome"></div>

            <div id="case-3d-wrap" class="case-3d-wrap" style="display:none"></div>
            <button class="btn-3d-toggle" id="btn3dToggle" onclick="toggle3DView()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
                <span id="btn3dLabel">Смотреть в 3D</span>
            </button>
        </div>

        <!-- Price + add to cart -->
        <div class="constructor-price">
            <div>
                <div class="p-old"><?= number_format($oldPrice,0,'.',' ') ?> ₽</div>
                <div class="p-new"><?= number_format($price,0,'.',' ') ?> <span style="font-size:1.2rem;color:var(--text-muted)">₽</span></div>
            </div>
            <button class="btn-constructor" style="min-width:unset;flex:unset;padding:0.875rem 1.75rem"
                onclick="addToCartFromConstructor()">В корзину</button>
        </div>

        <?php if ($case): ?>
        <button class="btn-save-design" onclick="saveDesignTemplate()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
            </svg>
            Сохранить дизайн в профиль
        </button>
        <div id="save-design-result" style="display:none"></div>
        <?php endif; ?>

        <?php if ($case): ?>
            <a href="<?= BASE_URL ?>/product.php?id=<?= $case['id_case'] ?>"
               style="display:block;text-align:center;margin-top:0.75rem;font-size:0.8rem;color:var(--text-muted)">
                ← Вернуться к товару
            </a>
        <?php endif; ?>
    </div>

    <!-- RIGHT: Controls -->
    <div class="constructor-controls">

        <!-- Device selectors -->
        <div class="ctrl-section">
            <div class="ctrl-label">Устройство</div>
            <div class="select-row">
                <select class="filter-select" id="firm-select" style="flex:1" onchange="updateModelsByFirm()">
                    <?php foreach (array_keys($firms) as $firm): ?>
                        <option value="<?= htmlspecialchars($firm) ?>"
                            <?= ($preselectFirm === $firm) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($firm) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select class="filter-select" id="model-select" style="flex:2">
                    <?php foreach ($models as $m): ?>
                        <option value="<?= $m['id_model'] ?>"
                            <?= ($preselectModelId == $m['id_model']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['model_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Material -->
        <div class="ctrl-section">
            <div class="ctrl-label">Материал</div>
            <div class="select-row">
                <select class="filter-select" id="material-select" style="flex:1">
                    <?php foreach ($materials as $m): ?>
                        <option value="<?= $m['id_material'] ?>"
                            <?= ($preselectMaterialId == $m['id_material']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['material_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button style="width:28px;height:38px;background:none;border:1px solid var(--border);border-radius:6px;cursor:pointer;color:var(--text-muted);font-size:0.85rem;flex-shrink:0"
                    title="Подсказка о материалах" onclick="showToast('Силикон — мягкий, защищает от ударов. Пластик — жёсткий, точная форма. Кожа — премиум ощущение.','info')">?</button>
            </div>
        </div>

        <!-- Colour of case -->
        <div class="ctrl-section">
            <div class="ctrl-label">Цвет материала</div>
            <div class="color-swatches" id="color-swatches">
                <div class="color-swatch checked active" style="background:#111111" data-color="#111111" onclick="setColor(this)" title="Чёрный"></div>
                <div class="color-swatch" style="background:#CC2222" data-color="#CC2222" onclick="setColor(this)" title="Красный"></div>
                <div class="color-swatch" style="background:#1A3A6A" data-color="#1A3A6A" onclick="setColor(this)" title="Тёмно-синий"></div>
                <div class="color-swatch" style="background:#2D5A2D" data-color="#2D5A2D" onclick="setColor(this)" title="Зелёный"></div>
                <div class="color-swatch" style="background:#FFFFFF;border-color:var(--border)" data-color="#FFFFFF" onclick="setColor(this)" title="Белый"></div>
                <div class="color-swatch" style="background:#7B3B8C" data-color="#7B3B8C" onclick="setColor(this)" title="Фиолетовый"></div>
                <div class="color-swatch" style="background:#C87830" data-color="#C87830" onclick="setColor(this)" title="Коричневый"></div>
                <div class="color-swatch" style="background:#1A8A7A" data-color="#1A8A7A" onclick="setColor(this)" title="Бирюзовый"></div>
                <div class="color-swatch" style="background:#222" data-color="transparent" onclick="setColor(this)" title="Прозрачный">
                    <span style="font-size:0.55rem;color:#777;line-height:1;text-align:center;display:block;padding-top:5px">TPU</span>
                </div>
                <label class="color-swatch" style="background:conic-gradient(red,yellow,lime,cyan,blue,magenta,red);cursor:pointer" title="Свой цвет">
                    <input type="color" id="custom-color" style="opacity:0;position:absolute;width:0;height:0" onchange="setCustomColor(this.value)">
                </label>
            </div>
        </div>

        <!-- Tool buttons -->
        <div class="ctrl-section">
            <div class="ctrl-label">Инструменты</div>
            <div class="tool-grid">
                <!-- Upload photo -->
                <label class="tool-btn" for="upload-photo" title="Загрузить фото">
                    <input type="file" id="upload-photo" accept="image/*" style="display:none" onchange="uploadPhoto(this)">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/></svg>
                    Загрузить<br>фото
                </label>

                <!-- Add text -->
                <button class="tool-btn" id="btn-text" onclick="toggleTextPanel()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/>
                        <line x1="12" y1="4" x2="12" y2="20"/></svg>
                    Добавить<br>надпись
                </button>

                <!-- Choose template -->
                <button class="tool-btn" id="btn-template" onclick="toggleTemplates()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Выбрать<br>шаблон
                </button>

                <!-- Add sticker -->
                <button class="tool-btn" id="btn-sticker" onclick="toggleStickerPanel()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 2a10 10 0 0 1 10 10c0 5.52-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2z"/>
                        <path d="M8.56 2.75c4.37 6.03 6.02 9.42 8.03 17.72m2.54-15.38c-3.72 4.35-8.94 5.66-16.88 5.85m19.5 1.9c-3.5-.93-6.63-.82-8.94 0-2.58.92-5.01 2.86-7.44 6.32"/></svg>
                    Добавить<br>стикер
                </button>

                <!-- Choose background colour -->
                <button class="tool-btn" id="btn-color" onclick="document.getElementById('bg-color-pick').click()">
                    <input type="color" id="bg-color-pick" style="display:none;position:absolute" onchange="setBgFromPicker(this.value)">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M20.71 5.63l-2.34-2.34a1 1 0 0 0-1.41 0l-3.12 3.12-1.41-1.42-1.42 1.42 1.41 1.41-6.6 6.6A2 2 0 0 0 5 16v3h3a2 2 0 0 0 1.42-.59l6.6-6.6 1.41 1.42 1.42-1.42-1.42-1.41 3.12-3.12a1 1 0 0 0 0-1.65z"/></svg>
                    Выбрать<br>цвет
                </button>

                <!-- Clear -->
                <button class="tool-btn" onclick="clearAll()" style="color:var(--error)">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/>
                        <path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                    Очистить<br>всё
                </button>
            </div>
        </div>

        <!-- Text panel (hidden by default) -->
        <div class="text-overlay-panel" id="text-panel">
            <div class="text-input-row">
                <input type="text" id="text-input" class="form-control" placeholder="Введите текст..." style="flex:1">
                <input type="color" id="text-color" value="#ffffff" title="Цвет текста"
                    style="width:38px;height:38px;border-radius:6px;border:1px solid var(--border);background:none;cursor:pointer;padding:2px">
                <button class="btn btn-primary btn-sm" onclick="addText()">Добавить</button>
            </div>
            <div class="text-font-row">
                <div style="font-size:0.7rem;color:var(--text-muted);width:100%;margin-bottom:0.25rem;letter-spacing:0.08em;text-transform:uppercase">Размер</div>
                <div class="font-chip active" data-size="16" onclick="selectFontSize(this)">S</div>
                <div class="font-chip" data-size="22" onclick="selectFontSize(this)">M</div>
                <div class="font-chip" data-size="30" onclick="selectFontSize(this)">L</div>
                <div class="font-chip" data-size="40" onclick="selectFontSize(this)">XL</div>
                <div style="font-size:0.7rem;color:var(--text-muted);width:100%;margin:0.35rem 0 0.25rem;letter-spacing:0.08em;text-transform:uppercase">Стиль</div>
                <div class="font-chip active" data-style="normal" onclick="selectFontStyle(this)">Обычный</div>
                <div class="font-chip" data-style="bold" onclick="selectFontStyle(this)" style="font-weight:700">Жирный</div>
                <div class="font-chip" data-style="italic" onclick="selectFontStyle(this)" style="font-style:italic">Курсив</div>
            </div>
        </div>

        <!-- Sticker panel -->
        <div class="stickers-grid" id="sticker-panel">
            <?php
            $stickers = ['⭐','🔥','💎','✨','🎨','🦋','🌸','🏆',
                         '❤️','🎸','🌙','⚡','🦅','🎭','🌊','🎯'];
            foreach ($stickers as $s): ?>
                <div class="sticker-item" onclick="addSticker('<?= $s ?>')" title="<?= $s ?>"><?= $s ?></div>
            <?php endforeach; ?>
        </div>

        <!-- Templates panel -->
        <div id="template-panel" style="display:none">
            <div class="ctrl-label" style="margin-bottom:0.5rem">Шаблоны фонов</div>
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.5rem;">
                <?php
                $templates = [
                    ['label'=>'Чёрный', 'bg'=>'#111'],
                    ['label'=>'Белый', 'bg'=>'#fff'],
                    ['label'=>'Ночь', 'bg'=>'linear-gradient(135deg,#0f0c29,#302b63,#24243e)'],
                    ['label'=>'Закат', 'bg'=>'linear-gradient(135deg,#f093fb,#f5576c)'],
                    ['label'=>'Океан', 'bg'=>'linear-gradient(135deg,#4facfe,#00f2fe)'],
                    ['label'=>'Лес', 'bg'=>'linear-gradient(135deg,#11998e,#38ef7d)'],
                    ['label'=>'Золото', 'bg'=>'linear-gradient(135deg,#f7971e,#ffd200)'],
                    ['label'=>'Галактика', 'bg'=>'radial-gradient(ellipse at 20% 50%,#1a1a2e,#16213e,#0f3460)'],
                ];
                foreach ($templates as $t): ?>
                    <div style="aspect-ratio:2/3;border-radius:8px;background:<?= $t['bg'] ?>;border:1px solid var(--border);cursor:pointer;transition:all 0.2s;overflow:hidden;display:flex;align-items:flex-end;padding:0.3rem"
                         onclick="setTemplate('<?= addslashes($t['bg']) ?>')"
                         onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform=''">
                        <span style="font-size:0.6rem;color:rgba(255,255,255,0.6);background:rgba(0,0,0,0.4);padding:1px 5px;border-radius:4px;white-space:nowrap"><?= $t['label'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Layers / history -->
        <div id="layers-section" style="display:none">
            <div class="ctrl-label" style="margin-bottom:0.5rem">Слои</div>
            <div id="layers-list" style="display:flex;flex-direction:column;gap:0.35rem;font-size:0.8rem;color:var(--text-muted);"></div>
        </div>

    </div><!-- constructor-controls -->
</div><!-- constructor-layout -->
</div><!-- constructor-page -->

<script>
// ===== STATE =====
const state = {
    bgColor: '#111111',
    bgGradient: null,
    texts: [],
    stickers: [],
    selectedFontSize: 16,
    selectedFontStyle: 'normal',
    hasImage: false,
};
// Мост для модульного скрипта 3D-превью: модули не видят let/const обычных
// <script>-блоков, поэтому пробрасываем тот же объект явно через window.
window.appState = state;

// ===== COLOUR =====
function setColor(el) {
    document.querySelectorAll('.color-swatch').forEach(s => {
        s.classList.remove('active','checked');
        s.style.outline = '';
    });
    el.classList.add('active','checked');
    const color = el.dataset.color;
    state.bgGradient = null;
    state.bgColor = color;
    applyBg();
    hidePlaceholder();
    if (typeof window.force3DSync === 'function') window.force3DSync();
}
function setCustomColor(val) {
    state.bgColor = val; state.bgGradient = null;
    document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active','checked'));
    applyBg(); hidePlaceholder();
    if (typeof window.force3DSync === 'function') window.force3DSync();
}
function setBgFromPicker(val) {
    state.bgColor = val; state.bgGradient = null; applyBg(); hidePlaceholder();
    if (typeof window.force3DSync === 'function') window.force3DSync();
}
function setTemplate(bg) {
    state.bgGradient = bg; state.bgColor = null;
    applyBg(); hidePlaceholder();
    if (typeof window.force3DSync === 'function') window.force3DSync();
}
function applyBg() {
    const el = document.getElementById('canvas-color-bg');
    if (state.bgGradient) {
        el.style.background = state.bgGradient;
    } else {
        el.style.background = state.bgColor || '#111';
    }
}

// ===== UPLOAD PHOTO =====
function uploadPhoto(input) {
    if (!input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const img = document.getElementById('canvas-bg');
        img.src = e.target.result;
        img.style.display = 'block';
        state.hasImage = true;
        hidePlaceholder();
        if (typeof window.force3DSync === 'function') window.force3DSync();
    };
    reader.readAsDataURL(input.files[0]);
}

// ===== TEXT =====
function toggleTextPanel() {
    const p = document.getElementById('text-panel');
    const btn = document.getElementById('btn-text');
    const show = !p.classList.contains('show');
    closeAllPanels();
    if (show) { p.classList.add('show'); btn.classList.add('active'); }
}

function selectFontSize(el) {
    document.querySelectorAll('[data-size]').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    state.selectedFontSize = parseInt(el.dataset.size);
}
function selectFontStyle(el) {
    document.querySelectorAll('[data-style]').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    state.selectedFontStyle = el.dataset.style;
}

function addText() {
    const val = document.getElementById('text-input').value.trim();
    if (!val) return;
    const color = document.getElementById('text-color').value;
    const id = 'txt-' + Date.now();
    state.texts.push({ id, val, color, size: state.selectedFontSize, style: state.selectedFontStyle });

    const container = document.getElementById('text-overlays');
    const el = document.createElement('div');
    el.id = id;
    el.style.cssText = `
        position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
        color:${color}; font-size:${state.selectedFontSize}px;
        font-weight:${state.selectedFontStyle==='bold'?'700':'400'};
        font-style:${state.selectedFontStyle==='italic'?'italic':'normal'};
        text-align:center; white-space:nowrap; cursor:move; user-select:none;
        text-shadow:0 1px 3px rgba(0,0,0,0.8); pointer-events:all;
        padding:4px 6px; border-radius:4px;
    `;
    el.textContent = val;
    makeDraggable(el);
    el.addEventListener('dblclick', () => { if (confirm('Удалить текст?')) el.remove(); });
    container.appendChild(el);

    document.getElementById('text-input').value = '';
    hidePlaceholder();
    updateLayers();
    if (typeof window.force3DSync === 'function') window.force3DSync();
}

// ===== STICKERS =====
function toggleStickerPanel() {
    const p = document.getElementById('sticker-panel');
    const btn = document.getElementById('btn-sticker');
    const show = !p.classList.contains('show');
    closeAllPanels();
    if (show) { p.classList.add('show'); btn.classList.add('active'); }
}

function addSticker(emoji) {
    const id = 'stk-' + Date.now();
    const container = document.getElementById('sticker-overlays');
    const el = document.createElement('div');
    el.id = id;
    el.style.cssText = `
        position:absolute; top:40%; left:40%; transform:translate(-50%,-50%);
        font-size:2.5rem; cursor:move; user-select:none; pointer-events:all;
        filter:drop-shadow(0 2px 4px rgba(0,0,0,0.5));
    `;
    el.textContent = emoji;
    makeDraggable(el);
    el.addEventListener('dblclick', () => { if (confirm('Удалить стикер?')) el.remove(); });
    container.appendChild(el);
    hidePlaceholder();
    updateLayers();
    if (typeof window.force3DSync === 'function') window.force3DSync();
}

// ===== TEMPLATES =====
function toggleTemplates() {
    const p = document.getElementById('template-panel');
    const btn = document.getElementById('btn-template');
    const show = p.style.display === 'none';
    closeAllPanels();
    if (show) { p.style.display = 'block'; btn.classList.add('active'); }
}

function closeAllPanels() {
    document.getElementById('text-panel').classList.remove('show');
    document.getElementById('sticker-panel').classList.remove('show');
    document.getElementById('template-panel').style.display = 'none';
    document.querySelectorAll('.tool-btn').forEach(b => b.classList.remove('active'));
}

// ===== CLEAR =====
function clearAll() {
    confirm('Очистить весь дизайн?', 'Все изменения — цвет, фото, надписи и стикеры — будут удалены без возможности восстановления.', () => {
        document.getElementById('canvas-bg').style.display = 'none';
        document.getElementById('canvas-bg').src = '';
        document.getElementById('text-overlays').innerHTML = '';
        document.getElementById('sticker-overlays').innerHTML = '';
        document.getElementById('canvas-color-bg').style.background = '#111';
        document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active','checked'));
        document.querySelector('[data-color="#111111"]').classList.add('active','checked');
        state.bgColor = '#111'; state.bgGradient = null; state.hasImage = false;
        document.getElementById('canvas-placeholder').style.display = 'flex';
        updateLayers();
    });
    if (typeof window.force3DSync === 'function') window.force3DSync();
}

// ===== DRAG =====
function makeDraggable(el) {
    let startX, startY, startL, startT;
    el.addEventListener('mousedown', e => {
        e.preventDefault();
        const rect = el.getBoundingClientRect();
        startX = e.clientX; startY = e.clientY;
        startL = el.offsetLeft; startT = el.offsetTop;
        el.style.transform = '';
        el.style.left = startL + 'px';
        el.style.top = startT + 'px';

        const onMove = ev => {
            el.style.left = (startL + ev.clientX - startX) + 'px';
            el.style.top  = (startT + ev.clientY - startY) + 'px';
        };
        const onUp = () => {
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
        };
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
    });
    // Touch support
    el.addEventListener('touchstart', e => {
        const t = e.touches[0];
        startX = t.clientX; startY = t.clientY;
        startL = el.offsetLeft; startT = el.offsetTop;
        el.style.transform = '';
        el.style.left = startL + 'px'; el.style.top = startT + 'px';
    }, {passive:true});
    el.addEventListener('touchmove', e => {
        const t = e.touches[0];
        el.style.left = (startL + t.clientX - startX) + 'px';
        el.style.top  = (startT + t.clientY - startY) + 'px';
        e.preventDefault();
    }, {passive:false});
}

// ===== LAYERS =====
function updateLayers() {
    const sec = document.getElementById('layers-section');
    const list = document.getElementById('layers-list');
    const allEls = [
        ...document.querySelectorAll('#text-overlays > div'),
        ...document.querySelectorAll('#sticker-overlays > div'),
    ];
    if (allEls.length === 0) { sec.style.display = 'none'; return; }
    sec.style.display = 'block';
    list.innerHTML = allEls.map(el => `
        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.35rem 0.5rem;background:var(--card);border-radius:6px;border:1px solid var(--border)">
            <span>${el.textContent.substring(0,20)}</span>
            <button onclick="document.getElementById('${el.id}').remove();updateLayers()"
                style="background:none;border:none;color:var(--error);cursor:pointer;font-size:0.85rem">✕</button>
        </div>
    `).join('');
}

// ===== MODEL FILTER =====
function updateModelsByFirm() {
    // In a real app would filter via AJAX; here models are all loaded
}

// ===== PLACEHOLDER =====
function hidePlaceholder() {
    document.getElementById('canvas-placeholder').style.display = 'none';
}

// ===== CART =====
function addToCartFromConstructor() {
    const caseId = <?= $case ? $case['id_case'] : 'null' ?>;
    if (!caseId) { showToast('Выберите базовый чехол из каталога', 'error'); return; }
    const customDesc = 'Конструктор: цвет=' + (state.bgColor||'градиент') +
        (state.texts.length ? ', текст: ' + state.texts.map(t=>t.val).join(', ') : '') +
        (document.querySelectorAll('#sticker-overlays>div').length ? ', со стикерами' : '');
    addToCart(caseId, 1, customDesc);
}

// ===== SAVE DESIGN TEMPLATE =====
function readElementPosition(el, container) {
    const elRect   = el.getBoundingClientRect();
    const contRect = container.getBoundingClientRect();
    const centerX  = elRect.left + elRect.width / 2 - contRect.left;
    const centerY  = elRect.top  + elRect.height / 2 - contRect.top;
    return {
        leftPct: +(centerX / contRect.width  * 100).toFixed(2),
        topPct:  +(centerY / contRect.height * 100).toFixed(2),
    };
}

function saveDesignTemplate() {
    const caseId = <?= $case ? $case['id_case'] : 'null' ?>;
    if (!caseId) { showToast('Выберите базовый чехол из каталога', 'error'); return; }

    const container = document.getElementById('phone-canvas');
    const bgImg = document.getElementById('canvas-bg');

    const texts = Array.from(document.querySelectorAll('#text-overlays > div')).map(el => {
        const pos = readElementPosition(el, container);
        return {
            val:   el.textContent,
            color: el.style.color || '#ffffff',
            size:  parseInt(el.style.fontSize) || 16,
            bold:  el.style.fontWeight === '700',
            italic: el.style.fontStyle === 'italic',
            top:   pos.topPct,
            left:  pos.leftPct,
        };
    });

    const stickers = Array.from(document.querySelectorAll('#sticker-overlays > div')).map(el => {
        const pos = readElementPosition(el, container);
        return { emoji: el.textContent, top: pos.topPct, left: pos.leftPct };
    });

    const designData = {
        bgColor:     state.bgGradient ? null : (state.bgColor || '#111111'),
        bgGradient:  state.bgGradient || null,
        hasImage:    state.hasImage,
        imageDataUrl: state.hasImage ? bgImg.src : null,
        texts:    texts,
        stickers: stickers,
        modelId:    document.getElementById('model-select')    ? document.getElementById('model-select').value    : null,
        materialId: document.getElementById('material-select') ? document.getElementById('material-select').value : null,
    };

    const btn = document.querySelector('.btn-save-design');
    const originalText = btn ? btn.textContent : '';
    if (btn) { btn.disabled = true; btn.textContent = 'Сохраняем...'; }

    const fd = new FormData();
    fd.append('case_id', caseId);
    fd.append('design_data', JSON.stringify(designData));

    fetch('<?= BASE_URL ?>/api/design_save.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (btn) { btn.disabled = false; btn.textContent = originalText; }
            if (data.success) {
                showToast('Дизайн сохранён в профиле!', 'success');
                const box = document.getElementById('save-design-result');
                box.style.display = 'block';
                box.className = 'save-design-share-box';
                box.innerHTML = `
                    <div class="label">✓ Сохранено! Ссылка для просмотра и шеринга:</div>
                    <div class="save-design-share-row">
                        <input type="text" readonly value="${data.shareUrl}" id="share-url-input" onclick="this.select()">
                        <button class="btn btn-primary btn-sm" onclick="copyShareLink()">Копировать</button>
                    </div>
                    <a href="<?= BASE_URL ?>/profile.php" style="display:inline-block;margin-top:0.6rem;color:var(--gold);font-size:0.78rem">
                        Смотреть в профиле →
                    </a>
                `;
            } else {
                showToast(data.error || 'Не удалось сохранить дизайн', 'error');
            }
        })
        .catch(() => {
            if (btn) { btn.disabled = false; btn.textContent = originalText; }
            showToast('Ошибка сети', 'error');
        });
}

function copyShareLink() {
    const input = document.getElementById('share-url-input');
    input.select();
    navigator.clipboard.writeText(input.value).then(() => {
        showToast('Ссылка скопирована!', 'success');
    }).catch(() => {
        document.execCommand('copy');
        showToast('Ссылка скопирована!', 'success');
    });
}
</script>

<script type="module">
// ============================================================
// 3D-ПРЕВЬЮ КОНСТРУКТОРА – финальная версия
// ============================================================

let THREE;
let threeReady   = false;
let initPromise  = null;
let loopRunning  = false;
let renderer, scene, camera, mesh, animId;
let texCanvas, texCtx;
let frontMesh = null;
let isDragging = false, prevX = 0, prevY = 0, rotY = 0.45, rotX = -0.12, autoRotate = true;

const TEX_W = 600, TEX_H = 1067;

const GRADIENTS = {
    'linear-gradient(135deg,#0f0c29,#302b63,#24243e)': { type:'linear', stops:[[0,'#0f0c29'],[0.5,'#302b63'],[1,'#24243e']] },
    'linear-gradient(135deg,#f093fb,#f5576c)':          { type:'linear', stops:[[0,'#f093fb'],[1,'#f5576c']] },
    'linear-gradient(135deg,#4facfe,#00f2fe)':          { type:'linear', stops:[[0,'#4facfe'],[1,'#00f2fe']] },
    'linear-gradient(135deg,#11998e,#38ef7d)':          { type:'linear', stops:[[0,'#11998e'],[1,'#38ef7d']] },
    'linear-gradient(135deg,#f7971e,#ffd200)':          { type:'linear', stops:[[0,'#f7971e'],[1,'#ffd200']] },
    'radial-gradient(ellipse at 20% 50%,#1a1a2e,#16213e,#0f3460)': { type:'radial', stops:[[0,'#1a1a2e'],[0.5,'#16213e'],[1,'#0f3460']] },
};

function fillBackground(ctx, w, h) {
    const st = window.appState;
    const bgGradient = st && st.bgGradient;
    const bgColor    = (st && st.bgColor) || '#111111';

    if (bgGradient && GRADIENTS[bgGradient]) {
        const g = GRADIENTS[bgGradient];
        let grad;
        if (g.type === 'linear') {
            grad = ctx.createLinearGradient(0, 0, w, h);
        } else {
            grad = ctx.createRadialGradient(w*0.2, h*0.5, 0, w*0.2, h*0.5, Math.max(w,h)*0.85);
        }
        g.stops.forEach(([off, color]) => grad.addColorStop(off, color));
        ctx.fillStyle = grad;
    } else if (bgGradient) {
        ctx.fillStyle = '#111111';
    } else {
        ctx.fillStyle = bgColor;
    }
    ctx.fillRect(0, 0, w, h);
}

function drawImageCover(ctx, img, x, y, w, h) {
    const ir = img.naturalWidth / img.naturalHeight;
    const tr = w / h;
    let sx, sy, sw, sh;
    if (ir > tr) {
        sh = img.naturalHeight; sw = sh * tr;
        sx = (img.naturalWidth - sw) / 2; sy = 0;
    } else {
        sw = img.naturalWidth; sh = sw / tr;
        sx = 0; sy = (img.naturalHeight - sh) / 2;
    }
    ctx.drawImage(img, sx, sy, sw, sh, x, y, w, h);
}

function elPosPct(el, container) {
    const elRect = el.getBoundingClientRect();
    const contRect = container.getBoundingClientRect();
    const centerX = elRect.left + elRect.width / 2 - contRect.left;
    const centerY = elRect.top + elRect.height / 2 - contRect.top;
    return {
        leftPct: (centerX / contRect.width) * 100,
        topPct:  (centerY / contRect.height) * 100,
    };
}

function syncTexture() {
    if (!texCtx || !THREE) return;
    const container = document.getElementById('phone-canvas');
    if (!container) return;
    const scale = TEX_W / container.clientWidth;

    texCtx.clearRect(0, 0, TEX_W, TEX_H);
    fillBackground(texCtx, TEX_W, TEX_H);

    const bgImg = document.getElementById('canvas-bg');
    const imageVisible = bgImg && bgImg.style.display !== 'none' && bgImg.src && bgImg.complete && bgImg.naturalWidth > 0;
    if (imageVisible) {
        drawImageCover(texCtx, bgImg, 0, 0, TEX_W, TEX_H);
    }

    document.querySelectorAll('#text-overlays > div').forEach(el => {
        const pos = elPosPct(el, container);
        const px = pos.leftPct / 100 * TEX_W;
        const py = pos.topPct / 100 * TEX_H;
        const fontPx = (parseInt(el.style.fontSize) || 16) * scale;
        const weight = el.style.fontWeight === '700' ? 'bold' : 'normal';
        const styleI = el.style.fontStyle === 'italic' ? 'italic' : 'normal';
        texCtx.save();
        texCtx.font = `${styleI} ${weight} ${fontPx}px Arial, sans-serif`;
        texCtx.textAlign = 'center';
        texCtx.textBaseline = 'middle';
        texCtx.shadowColor = 'rgba(0,0,0,0.85)';
        texCtx.shadowBlur = 4 * scale;
        texCtx.fillStyle = el.style.color || '#ffffff';
        texCtx.fillText(el.textContent, px, py);
        texCtx.restore();
    });

    document.querySelectorAll('#sticker-overlays > div').forEach(el => {
        const pos = elPosPct(el, container);
        const px = pos.leftPct / 100 * TEX_W;
        const py = pos.topPct / 100 * TEX_H;
        texCtx.save();
        texCtx.font = `${50 * scale}px sans-serif`;
        texCtx.textAlign = 'center';
        texCtx.textBaseline = 'middle';
        texCtx.shadowColor = 'rgba(0,0,0,0.8)';
        texCtx.shadowBlur = 6 * scale;
        texCtx.fillStyle = '#FFFFFF';
        texCtx.fillText(el.textContent, px, py);
        texCtx.restore();
    });

    if (frontMesh) {
        const newTexture = new THREE.CanvasTexture(texCanvas);
        newTexture.colorSpace = THREE.SRGBColorSpace;
        const newMat = new THREE.MeshStandardMaterial({
            map: newTexture,
            roughness: 0.4,
            metalness: 0.04,
            side: THREE.DoubleSide,
        });
        frontMesh.material = newMat;
        frontMesh.material.needsUpdate = true;
    }

    if (mesh && mesh.material) {
        const st = window.appState;
        if (st && st.bgColor && !st.bgGradient) {
            const color = new THREE.Color(st.bgColor);
            mesh.material.color.set(color);
        } else {
            mesh.material.color.set(0x1c1c22);
        }
        mesh.material.needsUpdate = true;
    }
}

window.force3DSync = function() {
    if (threeReady) syncTexture();
};

function startLoop() {
    if (loopRunning) return;
    loopRunning = true;
    (function animate() {
        if (!loopRunning) return;
        animId = requestAnimationFrame(animate);
        if (autoRotate) rotY += 0.0045;
        if (mesh) {
            mesh.rotation.y += (rotY - mesh.rotation.y) * 0.15;
            mesh.rotation.x += (rotX - mesh.rotation.x) * 0.15;
        }
        syncTexture();
        if (renderer && scene && camera) {
            renderer.render(scene, camera);
        }
    })();
}
function stopLoop() {
    loopRunning = false;
    if (animId) cancelAnimationFrame(animId);
}

async function loadThreeJS() {
    const urls = [
        'https://cdn.jsdelivr.net/npm/three@0.158.0/build/three.module.js',
        'https://unpkg.com/three@0.158.0/build/three.module.js'
    ];
    for (const url of urls) {
        try {
            const module = await import(url);
            return module;
        } catch (_) {}
    }
    throw new Error('Unable to load Three.js');
}

function init3D() {
    if (threeReady) { startLoop(); return Promise.resolve(); }
    if (initPromise) return initPromise;

    initPromise = (async () => {
        const wrap = document.getElementById('case-3d-wrap');
        if (!wrap) return;
        wrap.style.display = 'block';
        let attempts = 0;
        while ((wrap.clientWidth === 0 || wrap.clientHeight === 0) && attempts < 10) {
            await new Promise(r => setTimeout(r, 100));
            attempts++;
        }
        if (wrap.clientWidth === 0 || wrap.clientHeight === 0) {
            wrap.style.width = '300px';
            wrap.style.height = '533px';
        }

        wrap.innerHTML = '<div class="case-3d-loading"><div class="spin"></div><span>Загружаем 3D-просмотр…</span></div>';

        try {
            THREE = await loadThreeJS();
        } catch {
            wrap.innerHTML = '<div class="case-3d-loading"><span>Не удалось загрузить 3D-просмотр.<br>Проверьте соединение с интернетом.</span></div>';
            initPromise = null;
            threeReady = false;
            return;
        }

        wrap.innerHTML = '<div class="case-3d-hint">Зажмите и двигайте, чтобы повернуть</div>';

        const width  = wrap.clientWidth;
        const height = wrap.clientHeight;

        scene = new THREE.Scene();
        camera = new THREE.PerspectiveCamera(32, width / height, 0.1, 100);
        camera.position.set(0, 0, 6.4);

        renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        renderer.setSize(width, height);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        wrap.appendChild(renderer.domElement);

        scene.add(new THREE.AmbientLight(0x404050, 1.4));
        const keyLight = new THREE.DirectionalLight(0xfff4dd, 1.6);
        keyLight.position.set(3, 4, 5);
        scene.add(keyLight);
        const rimLight = new THREE.DirectionalLight(0xC8A96E, 1.1);
        rimLight.position.set(-4, 1, -3);
        scene.add(rimLight);
        const fillLight = new THREE.DirectionalLight(0x6688aa, 0.4);
        fillLight.position.set(0, -3, 2);
        scene.add(fillLight);

        texCanvas = document.createElement('canvas');
        texCanvas.width = TEX_W; texCanvas.height = TEX_H;
        texCtx = texCanvas.getContext('2d');
        fillBackground(texCtx, TEX_W, TEX_H);

        const initialTexture = new THREE.CanvasTexture(texCanvas);
        initialTexture.colorSpace = THREE.SRGBColorSpace;

        function roundedRectShape(w, h, r) {
            const s = new THREE.Shape();
            const x = -w / 2, y = -h / 2;
            s.moveTo(x + r, y);
            s.lineTo(x + w - r, y);
            s.quadraticCurveTo(x + w, y, x + w, y + r);
            s.lineTo(x + w, y + h - r);
            s.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
            s.lineTo(x + r, y + h);
            s.quadraticCurveTo(x, y + h, x, y + h - r);
            s.lineTo(x, y + r);
            s.quadraticCurveTo(x, y, x + r, y);
            return s;
        }

        function fixShapeUVs(geometry, w, h) {
            const pos = geometry.attributes.position;
            const uv  = geometry.attributes.uv;
            for (let i = 0; i < pos.count; i++) {
                const x = pos.getX(i), y = pos.getY(i);
                uv.setXY(i, (x + w / 2) / w, (y + h / 2) / h);
            }
            uv.needsUpdate = true;
        }

        const BODY_W = 1.85, BODY_H = 1.85 * 16/9, BODY_D = 0.2, CORNER_R = 0.22;

        const bodyMat = new THREE.MeshStandardMaterial({ color: 0x1c1c22, roughness: 0.42, metalness: 0.3 });
        const bodyShape = roundedRectShape(BODY_W, BODY_H, CORNER_R);
        const bodyGeo = new THREE.ExtrudeGeometry(bodyShape, {
            depth: BODY_D, bevelEnabled: true,
            bevelThickness: 0.035, bevelSize: 0.03, bevelSegments: 4,
            curveSegments: 16
        });
        bodyGeo.translate(0, 0, -BODY_D / 2);
        mesh = new THREE.Mesh(bodyGeo, bodyMat);
        scene.add(mesh);

        const FACE_PAD = 0.01;
        const frontShape = roundedRectShape(BODY_W + FACE_PAD, BODY_H + FACE_PAD, CORNER_R);
        const frontMat = new THREE.MeshStandardMaterial({
            map: initialTexture,
            roughness: 0.4,
            metalness: 0.04,
            side: THREE.DoubleSide,
        });
        const frontGeo = new THREE.ShapeGeometry(frontShape, 24);
        fixShapeUVs(frontGeo, BODY_W + FACE_PAD, BODY_H + FACE_PAD);
        const frontPlane = new THREE.Mesh(frontGeo, frontMat);
        frontPlane.position.z = BODY_D / 2 + 0.05;
        mesh.add(frontPlane);
        frontMesh = frontPlane;

        const backMat = new THREE.MeshStandardMaterial({ color: 0x141418, roughness: 0.55, metalness: 0.12, side: THREE.DoubleSide });
        const backPlane = new THREE.Mesh(new THREE.ShapeGeometry(frontShape, 24), backMat);
        backPlane.position.z = -BODY_D / 2 - 0.02;
        backPlane.rotation.y = Math.PI;
        mesh.add(backPlane);

        const camModW = 0.62, camModH = 0.62, camModD = 0.07, camModR = 0.16;
        const camShape = roundedRectShape(camModW, camModH, camModR);
        const camGeo = new THREE.ExtrudeGeometry(camShape, {
            depth: camModD, bevelEnabled: true,
            bevelThickness: 0.012, bevelSize: 0.01, bevelSegments: 3, curveSegments: 12
        });
        const camMat = new THREE.MeshStandardMaterial({ color: 0x131316, roughness: 0.35, metalness: 0.5 });
        const camModule = new THREE.Mesh(camGeo, camMat);
        camModule.position.set(
            -BODY_W/2 + camModW/2 + 0.18,
             BODY_H/2 - camModH/2 - 0.18,
             BODY_D/2
        );
        mesh.add(camModule);

        const lensMat = new THREE.MeshStandardMaterial({ color: 0x05050a, roughness: 0.08, metalness: 0.85 });
        const lensRingMat = new THREE.MeshStandardMaterial({ color: 0x35353d, roughness: 0.3, metalness: 0.6 });
        function addLens(offsetX, offsetY, radius) {
            const ring = new THREE.Mesh(new THREE.CircleGeometry(radius + 0.025, 24), lensRingMat);
            ring.position.set(offsetX, offsetY, camModD + 0.001);
            camModule.add(ring);
            const glass = new THREE.Mesh(new THREE.CircleGeometry(radius, 24), lensMat);
            glass.position.set(offsetX, offsetY, camModD + 0.003);
            camModule.add(glass);
        }
        addLens(-0.14,  0.14, 0.115);
        addLens( 0.14, -0.05, 0.115);
        addLens(-0.14, -0.16, 0.07);

        mesh.rotation.x = rotX;
        mesh.rotation.y = rotY;

        wrap.addEventListener('pointerdown', (e) => {
            isDragging = true; autoRotate = false;
            prevX = e.clientX; prevY = e.clientY;
            wrap.setPointerCapture(e.pointerId);
        });
        wrap.addEventListener('pointermove', (e) => {
            if (!isDragging) return;
            const dx = e.clientX - prevX, dy = e.clientY - prevY;
            rotY += dx * 0.008;
            rotX = Math.max(-0.8, Math.min(0.8, rotX + dy * 0.008));
            prevX = e.clientX; prevY = e.clientY;
        });
        wrap.addEventListener('pointerup', () => { isDragging = false; });
        wrap.addEventListener('pointerleave', () => { isDragging = false; });

        window.addEventListener('resize', () => {
            const w = wrap.clientWidth, h = wrap.clientHeight;
            if (!w || !h) return;
            camera.aspect = w / h; camera.updateProjectionMatrix();
            renderer.setSize(w, h);
        });

        threeReady = true;
        startLoop();
    })();

    return initPromise.catch(() => {
        const wrap = document.getElementById('case-3d-wrap');
        if (wrap) {
            wrap.innerHTML = '<div class="case-3d-loading"><span>Не удалось загрузить 3D-просмотр.<br>Проверьте соединение с интернетом.</span></div>';
        }
        initPromise = null;
        threeReady = false;
    });
}

window.toggle3DView = function() {
    const wrap   = document.getElementById('case-3d-wrap');
    const canvas = document.querySelector('.phone-canvas');
    const label  = document.getElementById('btn3dLabel');
    const showing3D = wrap.style.display !== 'none';

    if (showing3D) {
        wrap.style.display = 'none';
        if (canvas) canvas.style.visibility = '';
        label.textContent = 'Смотреть в 3D';
        stopLoop();
    } else {
        if (canvas) canvas.style.visibility = 'hidden';
        wrap.style.display = 'block';
        label.textContent = 'Обычное превью';
        init3D();
    }
};
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
