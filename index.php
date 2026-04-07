<?php
require_once __DIR__ . '/bootstrap.php';
$pageTitle = 'iSharlotka — Авторские чехлы для телефона';
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero-section">
    <div class="hero-bg"></div>
    <div class="hero-content fade-in">
        <span class="hero-eyebrow">Авторские чехлы ручной работы</span>
        <h1 class="hero-title">
            Твой чехол —<br>
            <em>твоя история</em>
        </h1>
        <p class="hero-sub">
            Каждый чехол создаётся вручную. Уникальные принты, авторские дизайны,<br>
            полная персонализация под твоё устройство.
        </p>
        <div class="hero-cta">
            <?php
 if (isLoggedIn()): ?>
                <a href="<?= BASE_URL ?>/catalog.php" class="btn btn-primary btn-lg">Смотреть каталог</a>
                <a href="<?= BASE_URL ?>/cart.php" class="btn btn-outline btn-lg">Корзина</a>
            <?php
 else: ?>
                <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-lg">Начать покупки</a>
                <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline btn-lg">Войти</a>
            <?php
 endif; ?>
        </div>
    </div>
    <div class="hero-scroll">
        Листайте вниз
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="6 9 12 15 18 9"/></svg>
    </div>
</section>

<section class="features-section">
    <div class="features-grid stagger">
        <div class="feature-card">
            <div class="feature-icon">✦</div>
            <h3>Авторский дизайн</h3>
            <p>Каждый принт разработан нашими художниками. Ничего массового — только уникальное.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">◈</div>
            <h3>Персонализация</h3>
            <p>Добавь свой текст, надпись или выбери цвет. Сделай чехол по-настоящему своим.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">⬡</div>
            <h3>Премиум материалы</h3>
            <p>Силикон, кожа, прозрачный TPU — только качественные материалы с долгим сроком службы.</p>
        </div>
    </div>
</section>

<?php
 require_once __DIR__ . '/includes/footer.php'; ?>
