<?php
require_once dirname(__DIR__) . '/bootstrap.php';
$pageTitle = 'Отзывы — iSharlotka Admin';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
require_once dirname(__DIR__) . '/config/db.php';

// Действия
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'approve' && $id) {
        $pdo->prepare("UPDATE reviews SET status='approved' WHERE id_review=?")->execute([$id]);
    } elseif ($action === 'reject' && $id) {
        $pdo->prepare("UPDATE reviews SET status='rejected' WHERE id_review=?")->execute([$id]);
    } elseif ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM reviews WHERE id_review=?")->execute([$id]);
    }
    redirect('/admin/reviews.php');
}

$filter = $_GET['status'] ?? 'pending';
$validFilters = ['pending','approved','rejected','all'];
if (!in_array($filter, $validFilters)) $filter = 'pending';

$where = $filter !== 'all' ? "WHERE r.status='$filter'" : '';
$reviews = $pdo->query("SELECT r.*, u.fullname, u.login, c.title as case_title
    FROM reviews r
    JOIN users u ON r.user_id = u.id_user
    JOIN cases_catalog c ON r.case_id = c.id_case
    $where ORDER BY r.created_at DESC")->fetchAll();

$counts = $pdo->query("SELECT status, COUNT(*) as n FROM reviews GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

require_once __DIR__ . '/header.php';
?>

<div class="admin-page-header">
    <h1>Отзывы</h1>
</div>

<!-- Фильтр -->
<div style="display:flex;gap:0.5rem;margin-bottom:1.5rem;flex-wrap:wrap">
    <?php foreach (['pending'=>'Ожидают','approved'=>'Одобренные','rejected'=>'Отклонённые','all'=>'Все'] as $k=>$v): ?>
    <a href="?status=<?= $k ?>" class="btn <?= $filter===$k?'btn-primary':'btn-ghost' ?> btn-sm">
        <?= $v ?> <?php if (isset($counts[$k])): ?><span style="opacity:.7">(<?= $counts[$k] ?>)</span><?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>

<?php if (!$reviews): ?>
    <div style="color:var(--text-muted);padding:2rem 0">Нет отзывов в этой категории.</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:1rem">
<?php foreach ($reviews as $r): ?>
<div style="background:var(--card-bg);border:1px solid var(--border);border-radius:10px;padding:1.25rem 1.5rem">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:0.75rem">
            <div style="width:36px;height:36px;border-radius:50%;background:var(--border);display:flex;align-items:center;justify-content:center;font-family:var(--font-display);color:var(--gold);font-size:1rem">
                <?= mb_strtoupper(mb_substr($r['fullname'] ?: $r['login'], 0, 1)) ?>
            </div>
            <div>
                <div style="color:var(--cream);font-size:0.9rem"><?= htmlspecialchars($r['fullname'] ?: $r['login']) ?></div>
                <div style="color:var(--text-muted);font-size:0.78rem">на чехол: <strong><?= htmlspecialchars($r['case_title']) ?></strong></div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:0.5rem">
            <div style="display:flex;gap:2px">
                <?php for ($s=1;$s<=5;$s++): ?>
                    <span style="color:<?= $s<=$r['rating']?'var(--gold)':'var(--border)' ?>;font-size:1rem">★</span>
                <?php endfor; ?>
            </div>
            <span style="font-size:0.75rem;color:var(--text-muted)"><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></span>
        </div>
    </div>
    <p style="color:var(--cream);font-size:0.9rem;margin:0.75rem 0;line-height:1.6"><?= htmlspecialchars($r['text']) ?></p>
    <div style="display:flex;gap:0.5rem;align-items:center">
        <?php if ($r['status'] === 'pending'): ?>
            <form method="post" style="display:inline">
                <input type="hidden" name="id" value="<?= $r['id_review'] ?>">
                <input type="hidden" name="action" value="approve">
                <button class="btn btn-primary btn-sm">✓ Одобрить</button>
            </form>
            <form method="post" style="display:inline">
                <input type="hidden" name="id" value="<?= $r['id_review'] ?>">
                <input type="hidden" name="action" value="reject">
                <button class="btn btn-ghost btn-sm">✕ Отклонить</button>
            </form>
        <?php elseif ($r['status'] === 'approved'): ?>
            <span style="color:#6aaa6a;font-size:0.8rem">✓ Одобрен</span>
            <form method="post" style="display:inline">
                <input type="hidden" name="id" value="<?= $r['id_review'] ?>">
                <input type="hidden" name="action" value="reject">
                <button class="btn btn-ghost btn-sm">Снять</button>
            </form>
        <?php else: ?>
            <span style="color:#aa6a6a;font-size:0.8rem">✕ Отклонён</span>
            <form method="post" style="display:inline">
                <input type="hidden" name="id" value="<?= $r['id_review'] ?>">
                <input type="hidden" name="action" value="approve">
                <button class="btn btn-ghost btn-sm">Одобрить</button>
            </form>
        <?php endif; ?>
        <form method="post" style="display:inline;margin-left:auto" onsubmit="return confirm('Удалить отзыв?')">
            <input type="hidden" name="id" value="<?= $r['id_review'] ?>">
            <input type="hidden" name="action" value="delete">
            <button class="btn btn-ghost btn-sm" style="color:#aa6a6a">Удалить</button>
        </form>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
