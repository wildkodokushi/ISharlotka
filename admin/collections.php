<?php
require_once dirname(__DIR__) . '/bootstrap.php';
$pageTitle = 'Коллекции — iSharlotka Admin';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
require_once dirname(__DIR__) . '/config/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id   = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if (!$name) $errors[] = 'Введите название коллекции';
        if (!$errors) {
            if ($id) {
                $pdo->prepare("UPDATE collections SET name=?, description=? WHERE id_collection=?")
                    ->execute([$name, $desc, $id]);
            } else {
                $pdo->prepare("INSERT INTO collections (name, description) VALUES (?,?)")
                    ->execute([$name, $desc]);
            }
            redirect('/admin/collections.php');
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM collections WHERE id_collection=?")->execute([$id]);
        redirect('/admin/collections.php');
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $edit = $pdo->prepare("SELECT * FROM collections WHERE id_collection=?");
    $edit->execute([(int)$_GET['edit']]);
    $edit = $edit->fetch();
}

$collections = $pdo->query("SELECT col.*, COUNT(c.id_case) as cnt
    FROM collections col
    LEFT JOIN cases_catalog c ON c.collection_id = col.id_collection
    GROUP BY col.id_collection ORDER BY col.name")->fetchAll();

require_once __DIR__ . '/header.php';
?>

<div class="admin-page-header">
    <h1>Коллекции</h1>
</div>

<div style="display:grid;grid-template-columns:1fr 380px;gap:2rem;align-items:start">

    <!-- Список -->
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Название</th><th>Описание</th><th>Чехлов</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($collections as $col): ?>
                <tr>
                    <td style="color:var(--cream)"><?= htmlspecialchars($col['name']) ?></td>
                    <td style="color:var(--text-muted);font-size:0.85rem"><?= htmlspecialchars(mb_substr($col['description'],0,60)) ?><?= mb_strlen($col['description'])>60?'…':'' ?></td>
                    <td><?= $col['cnt'] ?></td>
                    <td>
                        <a href="?edit=<?= $col['id_collection'] ?>" class="btn btn-ghost btn-sm">Изменить</a>
                        <form method="post" style="display:inline" onsubmit="return confirm('Удалить коллекцию?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $col['id_collection'] ?>">
                            <button class="btn btn-ghost btn-sm" style="color:#aa6a6a">Удалить</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Форма -->
    <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:10px;padding:1.5rem">
        <h3 style="font-family:var(--font-display);color:var(--cream);margin-bottom:1.25rem;font-size:1.1rem">
            <?= $edit ? 'Редактировать коллекцию' : 'Новая коллекция' ?>
        </h3>
        <?php if ($errors): ?>
            <div style="background:rgba(170,60,60,0.15);border:1px solid #aa3c3c;border-radius:6px;padding:0.75rem 1rem;margin-bottom:1rem;font-size:0.85rem;color:#dd8888">
                <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
            </div>
        <?php endif; ?>
        <form method="post" style="display:flex;flex-direction:column;gap:1rem">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= $edit['id_collection'] ?? 0 ?>">
            <div class="form-group">
                <label class="form-label">Название *</label>
                <input type="text" name="name" class="form-input"
                       value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Описание</label>
                <textarea name="description" class="form-input" rows="3"><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>
            </div>
            <button class="btn btn-primary"><?= $edit ? 'Сохранить' : 'Добавить' ?></button>
            <?php if ($edit): ?>
                <a href="<?= BASE_URL ?>/admin/collections.php" class="btn btn-ghost" style="text-align:center">Отмена</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
