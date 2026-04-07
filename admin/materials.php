<?php
require_once dirname(__DIR__) . '/bootstrap.php';
$pageTitle = 'Материалы — iSharlotka Admin';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
require_once dirname(__DIR__) . '/config/db.php';

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM materials WHERE id_material=?")->execute([(int)$_GET['delete']]);
    redirect('/admin/materials.php?deleted=1');
}
$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['material_name'] ?? '');
    $editId = (int)($_POST['edit_id'] ?? 0);
    if (!$name) { $error = 'Введите название.'; }
    elseif ($editId) {
        $pdo->prepare("UPDATE materials SET material_name=? WHERE id_material=?")->execute([$name,$editId]);
        $success = 'Материал обновлён.';
    } else {
        $pdo->prepare("INSERT INTO materials (material_name) VALUES (?)")->execute([$name]);
        $success = 'Материал добавлен.';
    }
}
$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM materials WHERE id_material=?");
    $stmt->execute([(int)$_GET['edit']]);
    $editItem = $stmt->fetch();
}
$materials = $pdo->query("SELECT * FROM materials ORDER BY material_name")->fetchAll();

require_once __DIR__ . '/header.php';
?>
<div class="admin-page-header"><h1>Материалы чехлов</h1></div>
<?php
 if (isset($_GET['deleted'])): ?><div class="alert alert-success">✓ Удалено.</div><?php endif; ?>
<?php
 if ($success): ?><div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php
 if ($error): ?><div class="alert alert-error">✕ <?= htmlspecialchars($error) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:2rem;align-items:start">
    <div class="admin-form-card">
        <h3 style="font-family:var(--font-display);font-size:1.2rem;color:var(--cream);margin-bottom:1.25rem">
            <?= $editItem ? 'Редактировать' : 'Добавить материал' ?>
        </h3>
        <form method="POST" action="">
            <?php
 if ($editItem): ?><input type="hidden" name="edit_id" value="<?= $editItem['id_material'] ?>"><?php endif; ?>
            <div class="form-group">
                <label class="form-label">Название</label>
                <input class="form-control" type="text" name="material_name"
                    value="<?= htmlspecialchars($editItem['material_name'] ?? '') ?>" required>
            </div>
            <div style="display:flex;gap:0.75rem">
                <button type="submit" class="btn btn-primary btn-sm">Сохранить</button>
                <?php
 if ($editItem): ?><a href="<?= BASE_URL ?>/admin/materials.php" class="btn btn-ghost btn-sm">Отмена</a><?php endif; ?>
            </div>
        </form>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>ID</th><th>Название</th><th></th></tr></thead>
            <tbody>
                <?php
 foreach ($materials as $m): ?>
                    <tr>
                        <td><?= $m['id_material'] ?></td>
                        <td style="color:var(--cream)"><?= htmlspecialchars($m['material_name']) ?></td>
                        <td>
                            <div class="table-actions">
                                <a href="<?= BASE_URL ?>/admin/materials.php?edit=<?= $m['id_material'] ?>" class="btn btn-outline btn-sm">Изменить</a>
                                <a href="<?= BASE_URL ?>/admin/materials.php?delete=<?= $m['id_material'] ?>" class="btn btn-danger btn-sm delete-btn"
                                   data-href="<?= BASE_URL ?>/admin/materials.php?delete=<?= $m['id_material'] ?>">Удалить</a>
                            </div>
                        </td>
                    </tr>
                <?php
 endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
 require_once __DIR__ . '/footer.php'; ?>
