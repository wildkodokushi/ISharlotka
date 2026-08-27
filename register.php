<?php
require_once __DIR__ . '/bootstrap.php';
$pageTitle = 'Регистрация — iSharlotka';
$pageDescription = 'Зарегистрируйтесь в iSharlotka и получите доступ к каталогу авторских чехлов и онлайн-конструктору.';
$pageNoIndex = true;
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) { redirect('/catalog.php'); }
require_once __DIR__ . '/config/db.php';

$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $pass     = $_POST['password'] ?? '';
    $pass2    = $_POST['password2'] ?? '';
    if (!$login || !$email || !$pass) {
        $error = 'Заполните все обязательные поля.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Некорректный email.';
    } elseif (strlen($pass) < 6) {
        $error = 'Пароль должен содержать не менее 6 символов.';
    } elseif ($pass !== $pass2) {
        $error = 'Пароли не совпадают.';
    } else {
        $stmt = $pdo->prepare("SELECT id_user FROM users WHERE login=? OR email=? LIMIT 1");
        $stmt->execute([$login, $email]);
        if ($stmt->fetch()) {
            $error = 'Пользователь с таким логином или email уже существует.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (login, email, password, fullname) VALUES (?,?,?,?)");
            $stmt->execute([$login, $email, $hash, $fullname]);
            $userId = $pdo->lastInsertId();
            $user = ['id_user'=>$userId,'login'=>$login,'fullname'=>$fullname,'role'=>'user'];
            loginUser($user);
            redirect('/catalog.php');
        }
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-wrapper">
    <div class="auth-card fade-in">
        <h2>Создать аккаунт</h2>
        <p class="auth-sub">Регистрация занимает меньше минуты</p>
        <?php
 if ($error): ?><div class="alert alert-error">✕ <?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="fullname">Имя</label>
                <input class="form-control" type="text" name="fullname" id="fullname" autocomplete="name"
                    value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="login">Логин <span style="color:var(--error)">*</span></label>
                <input class="form-control" type="text" name="login" id="login" autocomplete="username"
                    value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Email <span style="color:var(--error)">*</span></label>
                <input class="form-control" type="email" name="email" id="email" autocomplete="email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Пароль <span style="color:var(--error)">*</span></label>
                <input class="form-control" type="password" name="password" id="password" autocomplete="new-password" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="password2">Повторите пароль</label>
                <input class="form-control" type="password" name="password2" id="password2" autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary btn-full" style="margin-top:0.5rem">Зарегистрироваться</button>
        </form>
        <div class="auth-footer">
            Уже есть аккаунт? <a href="./login.php">Войти</a>
        </div>
    </div>
</div>
<?php
 require_once __DIR__ . '/includes/footer.php'; ?>
