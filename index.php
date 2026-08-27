<?php
require_once __DIR__ . '/bootstrap.php';
$pageTitle       = 'iSharlotka — Авторские чехлы для телефона с онлайн-конструктором';
$pageDescription = 'Интернет-магазин уникальных чехлов для iPhone, Samsung и Xiaomi. Создай свой дизайн в онлайн-конструкторе: цвет, фото, надписи и стикеры. Доставка 24 часа.';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

$featured    = $pdo->query("SELECT c.*, col.name as col_name
    FROM cases_catalog c
    LEFT JOIN collections col ON c.collection_id = col.id_collection
    ORDER BY c.created_at DESC LIMIT 4")->fetchAll();

$collections = $pdo->query("SELECT col.*, COUNT(c.id_case) as cnt
    FROM collections col
    LEFT JOIN cases_catalog c ON c.collection_id = col.id_collection
    GROUP BY col.id_collection ORDER BY cnt DESC LIMIT 6")->fetchAll();

$reviews = $pdo->query("SELECT r.*, u.fullname, u.login, c.title as case_title
    FROM reviews r
    JOIN users u ON r.user_id = u.id_user
    JOIN cases_catalog c ON r.case_id = c.id_case
    WHERE r.status = 'approved' ORDER BY r.created_at DESC LIMIT 3")->fetchAll();

$totalCases   = $pdo->query("SELECT COUNT(*) FROM cases_catalog")->fetchColumn();
$totalOrders  = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='completed'")->fetchColumn();
$totalReviews = $pdo->query("SELECT COUNT(*) FROM reviews WHERE status='approved'")->fetchColumn();

require_once __DIR__ . '/includes/header.php';
?>

<!-- ── HERO ──────────────────────────────────────────────── -->
<section class="hero-section">
    <div class="hero-bg"></div>
    <div class="hero-content fade-in">
        <span class="hero-eyebrow">Авторские чехлы ручной работы</span>
        <h1 class="hero-title">
            Твой чехол —<br><em>твоя история</em>
        </h1>
        <p class="hero-sub">
            Уникальные принты, полная персонализация через конструктор
            и доставка за 24 часа. Ни один чехол не повторяется.
        </p>
        <div class="hero-cta">
            <?php if (isLoggedIn()): ?>
                <a href="<?= BASE_URL ?>/catalog.php" class="btn btn-primary btn-lg">Смотреть каталог</a>
                <a href="<?= BASE_URL ?>/cart.php" class="btn btn-outline btn-lg">Корзина</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-lg">Начать покупки</a>
                <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline btn-lg">Войти</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ── STATS ─────────────────────────────────────────────── -->
<section class="home-stats">
    <div class="home-stats-grid">
        <div class="home-stat-item">
            <div class="home-stat-num"><?= $totalCases ?>+</div>
            <div class="home-stat-label">Уникальных дизайнов</div>
        </div>
        <div class="home-stat-item">
            <div class="home-stat-num"><?= $totalOrders ?>+</div>
            <div class="home-stat-label">Выполненных заказов</div>
        </div>
        <div class="home-stat-item">
            <div class="home-stat-num"><?= $totalReviews ?>+</div>
            <div class="home-stat-label">Довольных покупателей</div>
        </div>
        <div class="home-stat-item">
            <div class="home-stat-num">24ч</div>
            <div class="home-stat-label">Производство</div>
        </div>
    </div>
</section>

<!-- ── WHAT WE DO ─────────────────────────────────────────── -->
<section class="home-about-section">
    <div class="home-about-inner">
        <div class="home-about-text">
            <span class="section-eyebrow">Что такое iSharlotka</span>
            <h2 class="home-about-title">Магазин авторских<br>чехлов для телефона</h2>
            <p class="home-about-desc">
                iSharlotka — это специализированный интернет-магазин, где каждый чехол создан вручную
                независимыми дизайнерами. Здесь нет безликого масс-маркета — только уникальные принты,
                подобранные по твоей модели устройства.
            </p>
            <p class="home-about-desc">
                Не нашёл нужный дизайн? Создай свой в <strong>онлайн-конструкторе</strong>:
                выбери цвет корпуса, загрузи своё фото, добавь надпись и стикеры —
                прямо в браузере, без установки приложений.
            </p>
            <?php if (!isLoggedIn()): ?>
            <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary" style="margin-top:1.5rem">
                Зарегистрироваться бесплатно
            </a>
            <?php endif; ?>
        </div>
        <div class="home-about-features">
            <div class="home-feature-item">
                <div class="home-feature-icon">✦</div>
                <div>
                    <div class="home-feature-title">Авторский дизайн</div>
                    <div class="home-feature-desc">Каждый принт разработан нашими художниками — ни один чехол не повторяется</div>
                </div>
            </div>
            <div class="home-feature-item">
                <div class="home-feature-icon">◈</div>
                <div>
                    <div class="home-feature-title">Онлайн-конструктор</div>
                    <div class="home-feature-desc">Создай чехол самостоятельно: цвет, фото, надписи и стикеры прямо в браузере</div>
                </div>
            </div>
            <div class="home-feature-item">
                <div class="home-feature-icon">⬡</div>
                <div>
                    <div class="home-feature-title">Премиум материалы</div>
                    <div class="home-feature-desc">Силикон, натуральная кожа, прозрачный TPU — выбирай под свой стиль</div>
                </div>
            </div>
            <div class="home-feature-item">
                <div class="home-feature-icon">◻</div>
                <div>
                    <div class="home-feature-title">Быстрая доставка</div>
                    <div class="home-feature-desc">Производство 24 часа. Оплата при получении — риск нулевой</div>
                </div>
            </div>
            <div class="home-feature-item">
                <div class="home-feature-icon">○</div>
                <div>
                    <div class="home-feature-title">Apple, Samsung, Xiaomi</div>
                    <div class="home-feature-desc">Широкий выбор моделей. Нет своей — напишите, добавим под заказ</div>
                </div>
            </div>
            <div class="home-feature-item">
                <div class="home-feature-icon">◇</div>
                <div>
                    <div class="home-feature-title">Гарантия 90 дней</div>
                    <div class="home-feature-desc">Обмен или возврат в течение 14 дней без лишних вопросов</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── HOW IT WORKS ───────────────────────────────────────── -->
<section class="how-section">
    <div class="container">
        <div class="section-header">
            <span class="section-eyebrow">Просто и быстро</span>
            <h2 class="section-title">Как это работает</h2>
            <p class="section-sub">Четыре шага до уникального чехла</p>
        </div>
        <div class="how-steps">
            <div class="how-step">
                <div class="how-step-num">01</div>
                <div class="how-step-body">
                    <h4>Выбери чехол</h4>
                    <p>Зайди в каталог, отфильтруй по модели телефона и материалу. Найди дизайн, который откликается.</p>
                </div>
            </div>
            <div class="how-step-arrow">→</div>
            <div class="how-step">
                <div class="how-step-num">02</div>
                <div class="how-step-body">
                    <h4>Персонализируй</h4>
                    <p>Нажми «В конструктор» и настрой чехол: цвет корпуса, надпись, фото, стикеры — всё твоё.</p>
                </div>
            </div>
            <div class="how-step-arrow">→</div>
            <div class="how-step">
                <div class="how-step-num">03</div>
                <div class="how-step-body">
                    <h4>Оформи заказ</h4>
                    <p>Добавь в корзину, подтверди заказ. Оплата при получении — деньги отдаёшь только когда держишь чехол.</p>
                </div>
            </div>
            <div class="how-step-arrow">→</div>
            <div class="how-step">
                <div class="how-step-num">04</div>
                <div class="how-step-body">
                    <h4>Получи за 24 ч</h4>
                    <p>Мы производим каждый чехол вручную. Твой заказ готов в течение суток.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── COLLECTIONS ────────────────────────────────────────── -->
<?php if ($collections): ?>
<section class="home-collections-section">
    <div class="container">
        <div class="section-header">
            <span class="section-eyebrow">Наши серии</span>
            <h2 class="section-title">Коллекции</h2>
            <p class="section-sub">Найди свой стиль среди наших серий</p>
        </div>
        <div class="home-collections-grid">
            <?php foreach ($collections as $col): ?>
            <a href="<?= BASE_URL ?>/catalog.php?collection=<?= urlencode($col['id_collection']) ?>" class="home-col-card">
                <div class="home-col-icon">◈</div>
                <div class="home-col-name"><?= htmlspecialchars($col['name']) ?></div>
                <div class="home-col-count"><?= $col['cnt'] ?> <?= $col['cnt'] == 1 ? 'чехол' : ($col['cnt'] < 5 ? 'чехла' : 'чехлов') ?></div>
                <?php if ($col['description']): ?>
                    <div class="home-col-desc"><?= htmlspecialchars($col['description']) ?></div>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── FEATURED CASES ─────────────────────────────────────── -->
<?php if ($featured): ?>
<section class="home-featured-section">
    <div class="container">
        <div class="section-header">
            <span class="section-eyebrow">Только что добавили</span>
            <h2 class="section-title">Новинки каталога</h2>
            <p class="section-sub">Свежие дизайны, только что появившиеся в магазине</p>
        </div>
        <div class="home-cases-grid stagger">
            <?php foreach ($featured as $c): ?>
            <a href="<?= BASE_URL ?>/product.php?id=<?= $c['id_case'] ?>" class="home-case-card">
                <div class="home-case-img">
                    <?php if ($c['image'] && file_exists(__DIR__.'/uploads/cases/'.$c['image'])): ?>
                        <img src="<?= BASE_URL ?>/uploads/cases/<?= htmlspecialchars($c['image']) ?>" alt="<?= htmlspecialchars($c['title']) ?>" loading="lazy">
                    <?php else: ?>
                        <span class="home-case-placeholder">◈</span>
                    <?php endif; ?>
                    <?php if ($c['col_name']): ?>
                        <span class="case-collection-badge"><?= htmlspecialchars($c['col_name']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="home-case-info">
                    <div class="home-case-title"><?= htmlspecialchars($c['title']) ?></div>
                    <div class="home-case-price"><?= number_format($c['price'],0,'.',' ') ?> ₽</div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php if (isLoggedIn()): ?>
            <div style="text-align:center;margin-top:2.5rem">
                <a href="<?= BASE_URL ?>/catalog.php" class="btn btn-outline btn-lg">Весь каталог →</a>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- ── REVIEWS ────────────────────────────────────────────── -->
<?php if ($reviews): ?>
<section class="home-reviews-section">
    <div class="container">
        <div class="section-header">
            <span class="section-eyebrow">Что говорят покупатели</span>
            <h2 class="section-title">Отзывы</h2>
            <p class="section-sub">Реальные покупатели о нашей работе</p>
        </div>
        <div class="home-reviews-grid">
            <?php foreach ($reviews as $r): ?>
            <div class="home-review-card">
                <div class="home-review-header">
                    <div class="home-review-avatar"><?= mb_strtoupper(mb_substr($r['fullname'] ?: $r['login'], 0, 1)) ?></div>
                    <div>
                        <div class="home-review-name"><?= htmlspecialchars($r['fullname'] ?: $r['login']) ?></div>
                        <div class="home-review-case">на: <?= htmlspecialchars($r['case_title']) ?></div>
                    </div>
                    <div class="home-review-stars">
                        <?php for ($s=1;$s<=5;$s++): ?>
                            <span style="color:<?= $s<=$r['rating']?'var(--gold)':'rgba(255,255,255,0.25)' ?>">★</span>
                        <?php endfor; ?>
                    </div>
                </div>
                <p class="home-review-text"><?= htmlspecialchars($r['text']) ?></p>
                <div class="home-review-date"><?= date('d.m.Y', strtotime($r['created_at'])) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── CTA ───────────────────────────────────────────────── -->
<section class="home-cta-banner">
    <div class="home-cta-inner">
        <span class="section-eyebrow">Не жди — начни прямо сейчас</span>
        <h2>Готов создать<br>свой чехол?</h2>
        <p>Зарегистрируйся и получи доступ к полному каталогу и конструктору</p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;margin-top:2rem">
            <?php if (isLoggedIn()): ?>
                <a href="<?= BASE_URL ?>/catalog.php" class="btn btn-primary btn-lg">Перейти в каталог</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-lg">Создать аккаунт</a>
                <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline btn-lg">Уже есть аккаунт</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
