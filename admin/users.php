<?php
require_once dirname(__DIR__) . '/bootstrap.php';
$pageTitle = 'Пользователи — iSharlotka Admin';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
require_once dirname(__DIR__) . '/config/db.php';

if (isset($_GET['delete'])) {
    $del = (int)$_GET['delete'];
    if ($del != $_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM users WHERE id_user=?")->execute([$del]);
    }
    redirect('/admin/users.php?deleted=1');
}

$users = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id=u.id_user) AS orders_count
    FROM users u ORDER BY u.created_at DESC")->fetchAll();

require_once __DIR__ . '/header.php';
?>
<div class="admin-page-header"><h1>Пользователи</h1></div>
<?php
 if (isset($_GET['deleted'])): ?><div class="alert alert-success">✓ Пользователь удалён.</div><?php endif; ?>

<div class="table-wrapper">
    <table class="data-table">
        <thead><tr><th>ID</th><th>Логин</th><th>Имя</th><th>Email</th><th>Роль</th><th>Заказы</th><th>Регистрация</th><th></th></tr></thead>
        <tbody>
            <?php
 foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id_user'] ?></td>
                    <td style="color:var(--cream)">@<?= htmlspecialchars($u['login']) ?></td>
                    <td><?= htmlspecialchars($u['fullname'] ?? '—') ?></td>
                    <td style="font-size:0.8rem"><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="order-status <?= $u['role']==='admin'?'status-processing':'status-completed' ?>"><?= $u['role'] === 'admin' ? 'Администратор' : 'Покупатель' ?></span></td>
                    <td><?= $u['orders_count'] ?></td>
                    <td><?= date('d.m.Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <?php
 if ($u['id_user'] != $_SESSION['user_id'] && $u['role'] !== 'admin'): ?>
                            <a href="<?= BASE_URL ?>/admin/users.php?delete=<?= $u['id_user'] ?>" class="btn btn-danger btn-sm delete-btn"
                               data-href="<?= BASE_URL ?>/admin/users.php?delete=<?= $u['id_user'] ?>">Удалить</a>
                        <?php
 else: ?>
                            <span style="font-size:0.75rem;color:var(--text-muted)">—</span>
                        <?php
 endif; ?>
                    </td>
                </tr>
            <?php
 endforeach; ?>
        </tbody>
    </table>
</div>
<?php
 require_once __DIR__ . '/footer.php'; ?>
