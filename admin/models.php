<?php
require_once dirname(__DIR__) . '/bootstrap.php';
$pageTitle = 'Модели устройств — iSharlotka Admin';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
require_once dirname(__DIR__) . '/config/db.php';

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM device_models WHERE id_model=?")->execute([(int)$_GET['delete']]);
    redirect('/admin/models.php?deleted=1');
}
$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firm  = trim($_POST['firm'] ?? '');
    $model = trim($_POST['model_name'] ?? '');
    $editId = (int)($_POST['edit_id'] ?? 0);
    if (!$firm || !$model) { $error = 'Заполните все поля.'; }
    elseif ($editId) {
        $pdo->prepare("UPDATE device_models SET firm=?,model_name=? WHERE id_model=?")->execute([$firm,$model,$editId]);
        $success = 'Модель обновлена.';
    } else {
        $pdo->prepare("INSERT INTO device_models (firm,model_name) VALUES (?,?)")->execute([$firm,$model]);
        $success = 'Модель добавлена.';
    }
}
$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM device_models WHERE id_model=?");
    $stmt->execute([(int)$_GET['edit']]);
    $editItem = $stmt->fetch();
}
$models = $pdo->query("SELECT * FROM device_models ORDER BY firm, model_name")->fetchAll();

require_once __DIR__ . '/header.php';
?>
<div class="admin-page-header"><h1>Модели устройств</h1></div>
<?php
 if (isset($_GET['deleted'])): ?><div class="alert alert-success">✓ Удалено.</div><?php endif; ?>
<?php
 if ($success): ?><div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php
 if ($error): ?><div class="alert alert-error">✕ <?= htmlspecialchars($error) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:2rem;align-items:start">
    <div class="admin-form-card">
        <h3 style="font-family:var(--font-display);font-size:1.2rem;color:var(--cream);margin-bottom:1.25rem">
            <?= $editItem ? 'Редактировать' : 'Добавить модель' ?>
        </h3>
        <form method="POST" action="">
            <?php
 if ($editItem): ?><input type="hidden" name="edit_id" value="<?= $editItem['id_model'] ?>"><?php endif; ?>
            <div class="form-group">
                <label class="form-label">Производитель</label>
                <input class="form-control" type="text" name="firm"
                    value="<?= htmlspecialchars($editItem['firm'] ?? '') ?>" placeholder="Apple, Samsung..." required>
            </div>
            <div class="form-group">
                <label class="form-label">Модель</label>
                <input class="form-control" type="text" name="model_name"
                    value="<?= htmlspecialchars($editItem['model_name'] ?? '') ?>" placeholder="iPhone 15 Pro..." required>
            </div>
            <div style="display:flex;gap:0.75rem">
                <button type="submit" class="btn btn-primary btn-sm">Сохранить</button>
                <?php
 if ($editItem): ?><a href="<?= BASE_URL ?>/admin/models.php" class="btn btn-ghost btn-sm">Отмена</a><?php endif; ?>
            </div>
        </form>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>ID</th><th>Производитель</th><th>Модель</th><th></th></tr></thead>
            <tbody>
                <?php
 foreach ($models as $m): ?>
                    <tr>
                        <td><?= $m['id_model'] ?></td>
                        <td style="color:var(--gold)"><?= htmlspecialchars($m['firm']) ?></td>
                        <td style="color:var(--cream)"><?= htmlspecialchars($m['model_name']) ?></td>
                        <td>
                            <div class="table-actions">
                                <a href="<?= BASE_URL ?>/admin/models.php?edit=<?= $m['id_model'] ?>" class="btn btn-outline btn-sm">Изменить</a>
                                <a href="<?= BASE_URL ?>/admin/models.php?delete=<?= $m['id_model'] ?>" class="btn btn-danger btn-sm delete-btn"
                                   data-href="<?= BASE_URL ?>/admin/models.php?delete=<?= $m['id_model'] ?>">Удалить</a>
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
