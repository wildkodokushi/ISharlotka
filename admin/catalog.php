<?php
require_once dirname(__DIR__) . '/bootstrap.php';
$pageTitle = 'Каталог — iSharlotka Admin';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
require_once dirname(__DIR__) . '/config/db.php';

// Delete
if (isset($_GET['delete'])) {
    $del = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM cases_catalog WHERE id_case=?")->execute([$del]);
    redirect('/admin/catalog.php?deleted=1');
}

$search = trim($_GET['search'] ?? '');
$params = [];
$where = '1=1';
if ($search) { $where .= ' AND (c.title LIKE ? OR c.collection LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }

$cases = $pdo->prepare("SELECT c.*, m.material_name, d.firm, d.model_name
    FROM cases_catalog c
    LEFT JOIN materials m ON c.material_id=m.id_material
    LEFT JOIN device_models d ON c.model_id=d.id_model
    WHERE $where ORDER BY c.created_at DESC");
$cases->execute($params);
$cases = $cases->fetchAll();

require_once __DIR__ . '/header.php';
?>

<div class="admin-page-header">
    <h1>Каталог чехлов</h1>
    <a href="<?= BASE_URL ?>/admin/add_case.php" class="btn btn-primary">+ Добавить чехол</a>
</div>

<?php
 if (isset($_GET['deleted'])): ?><div class="alert alert-success">✓ Чехол удалён.</div><?php endif; ?>
<?php
 if (isset($_GET['saved'])): ?><div class="alert alert-success">✓ Чехол сохранён.</div><?php endif; ?>

<form method="GET" style="margin-bottom:1.5rem">
    <div class="search-box" style="max-width:360px">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Поиск по названию...">
    </div>
</form>

<div class="table-wrapper">
    <table class="data-table">
        <thead>
            <tr><th>ID</th><th>Название</th><th>Коллекция</th><th>Устройство</th><th>Материал</th><th>Цена</th><th>Остаток</th><th></th></tr>
        </thead>
        <tbody>
            <?php
 foreach ($cases as $c): ?>
                <tr>
                    <td><?= $c['id_case'] ?></td>
                    <td style="color:var(--cream);font-weight:500"><?= htmlspecialchars($c['title']) ?></td>
                    <td><?= htmlspecialchars($c['collection'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($c['firm'].' '.($c['model_name'] ?? '—')) ?></td>
                    <td><?= htmlspecialchars($c['material_name'] ?? '—') ?></td>
                    <td style="color:var(--gold)"><?= number_format($c['price'],0,'.',' ') ?> ₽</td>
                    <td><?= $c['count'] ?> шт.</td>
                    <td>
                        <div class="table-actions">
                            <a href="<?= BASE_URL ?>/admin/add_case.php?edit=<?= $c['id_case'] ?>" class="btn btn-outline btn-sm">Изменить</a>
                            <a href="<?= BASE_URL ?>/admin/catalog.php?delete=<?= $c['id_case'] ?>" class="btn btn-danger btn-sm delete-btn"
                               data-href="<?= BASE_URL ?>/admin/catalog.php?delete=<?= $c['id_case'] ?>">Удалить</a>
                        </div>
                    </td>
                </tr>
            <?php
 endforeach; ?>
        </tbody>
    </table>
</div>

<?php
 require_once __DIR__ . '/footer.php'; ?>
