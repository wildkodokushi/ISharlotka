<?php
require_once __DIR__ . '/bootstrap.php';
$pageTitle = 'Конструктор чехлов — iSharlotka';
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

$materials = $pdo->query("SELECT * FROM materials ORDER BY material_name")->fetchAll();
$models    = $pdo->query("SELECT * FROM device_models ORDER BY firm, model_name")->fetchAll();
// Group models by firm
$firms = [];
foreach ($models as $m) { $firms[$m['firm']][] = $m; }

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
                            <?= ($case && $case['firm']===$firm) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($firm) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select class="filter-select" id="model-select" style="flex:2">
                    <?php foreach ($models as $m): ?>
                        <option value="<?= $m['id_model'] ?>"
                            <?= ($case && $case['model_id']==$m['id_model']) ? 'selected' : '' ?>>
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
                            <?= ($case && $case['material_id']==$m['id_material']) ? 'selected' : '' ?>>
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
}
function setCustomColor(val) {
    state.bgColor = val; state.bgGradient = null;
    document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active','checked'));
    applyBg(); hidePlaceholder();
}
function setBgFromPicker(val) {
    state.bgColor = val; state.bgGradient = null; applyBg(); hidePlaceholder();
}
function setTemplate(bg) {
    state.bgGradient = bg; state.bgColor = null;
    applyBg(); hidePlaceholder();
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
    reader.onload = e => {
        const img = document.getElementById('canvas-bg');
        img.src = e.target.result;
        img.style.display = 'block';
        state.hasImage = true;
        hidePlaceholder();
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
    if (!confirm('Очистить весь дизайн?')) return;
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
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
