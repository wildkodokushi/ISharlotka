<?php
require_once __DIR__ . '/bootstrap.php';
$pageTitle = 'Вход в личный кабинет — iSharlotka';
$pageDescription = 'Войдите в личный кабинет iSharlotka, чтобы оформить заказ и отслеживать его статус.';
$pageNoIndex = true;
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) { redirect('/catalog.php'); }
require_once __DIR__ . '/config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($login && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ? OR email = ? LIMIT 1");
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            loginUser($user);
            redirect('/catalog.php');
        } else {
            $error = 'Неверный логин или пароль.';
        }
    } else {
        $error = 'Заполните все поля.';
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-wrapper">
    <div class="auth-card fade-in">
        <h2>Добро пожаловать</h2>
        <p class="auth-sub">Войдите, чтобы открыть каталог и оформить заказ</p>
        <?php
 if ($error): ?><div class="alert alert-error">✕ <?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="login">Логин или Email</label>
                <input class="form-control" type="text" name="login" id="login" autocomplete="username"
                    value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Пароль</label>
                <input class="form-control" type="password" name="password" id="password" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full" style="margin-top:0.5rem">Войти</button>
        </form>
        <div class="auth-footer">
            Нет аккаунта? <a href="./register.php">Зарегистрироваться</a>
        </div>
    </div>
</div>
<?php
 require_once __DIR__ . '/includes/footer.php'; ?>
