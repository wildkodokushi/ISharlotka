<?php
require_once dirname(__DIR__) . '/bootstrap.php';
$pageTitle = 'Добавить/редактировать чехол — Admin';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
require_once dirname(__DIR__) . '/config/db.php';

$uploadDir = dirname(__DIR__) . '/uploads/cases';
if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }

$editId = (int)($_GET['edit'] ?? 0);
$case = null;
if ($editId) {
    $stmt = $pdo->prepare("SELECT * FROM cases_catalog WHERE id_case=?");
    $stmt->execute([$editId]);
    $case = $stmt->fetch();
    if (!$case) { redirect('/admin/catalog.php'); }
}

$materials = $pdo->query("SELECT * FROM materials ORDER BY material_name")->fetchAll();
$collections_list = $pdo->query("SELECT * FROM collections ORDER BY name")->fetchAll();
$models    = $pdo->query("SELECT * FROM device_models ORDER BY firm, model_name")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title'       => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'price'       => (float)($_POST['price'] ?? 0),
        'count'       => (int)($_POST['count'] ?? 0),
        'collection_id' => (int)($_POST['collection_id'] ?? 0),
        'inscription' => trim($_POST['inscription'] ?? ''),
        'sticker'     => isset($_POST['sticker']) ? 1 : 0,
        'has_3d'      => 0, // больше не используется отдельным флагом — 3D теперь доступно для любого чехла в конструкторе
        'color'       => trim($_POST['color'] ?? ''),
        'material_id' => (int)($_POST['material_id'] ?? 0) ?: null,
        'model_id'    => (int)($_POST['model_id'] ?? 0) ?: null,
    ];
    if (!$data['title']) $errors[] = 'Введите название.';
    if ($data['price'] <= 0) $errors[] = 'Укажите цену.';

    // Handle image upload
    $imageName = $case['image'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
            $errors[] = 'Допустимые форматы: jpg, png, webp.';
        } else {
            $newName = uniqid('case_').'.'.$ext;
            $dest = $uploadDir . '/'.$newName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                if ($imageName && file_exists($uploadDir . '/'.$imageName)) {
                    unlink($uploadDir . '/'.$imageName);
                }
                $imageName = $newName;
            } else {
                $errors[] = 'Ошибка загрузки файла.';
            }
        }
    }

    if (empty($errors)) {
        if ($editId) {
            $stmt = $pdo->prepare("UPDATE cases_catalog SET title=?,description=?,price=?,count=?,collection_id=?,inscription=?,sticker=?,color=?,material_id=?,model_id=?,has_3d=?,image=? WHERE id_case=?");
            $stmt->execute([$data['title'],$data['description'],$data['price'],$data['count'],$data['collection_id'],$data['inscription'],$data['sticker'],$data['color'],$data['material_id'],$data['model_id'],$data['has_3d'],$imageName,$editId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO cases_catalog (title,description,price,count,collection_id,inscription,sticker,color,material_id,model_id,has_3d,image) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$data['title'],$data['description'],$data['price'],$data['count'],$data['collection_id'],$data['inscription'],$data['sticker'],$data['color'],$data['material_id'],$data['model_id'],$data['has_3d'],$imageName]);
        }
        redirect('/admin/catalog.php?saved=1');
    }
    $case = array_merge($case ?? [], $data, ['image'=>$imageName]);
}

require_once __DIR__ . '/header.php';
?>

<div class="admin-page-header">
    <h1><?= $editId ? 'Редактировать чехол' : 'Новый чехол' ?></h1>
    <a href="<?= BASE_URL ?>/admin/catalog.php" class="btn btn-ghost">← Назад</a>
</div>

<?php
 if ($errors): ?>
    <div class="alert alert-error">✕ <?= implode('<br>✕ ', array_map('htmlspecialchars', $errors)) ?></div>
<?php
 endif; ?>

<form method="POST" action="" enctype="multipart/form-data">
    <div class="admin-form-card">
        <div class="form-row">
            <div class="form-group" style="grid-column:1/-1">
                <label class="form-label">Название *</label>
                <input class="form-control" type="text" name="title" value="<?= htmlspecialchars($case['title'] ?? '') ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Описание</label>
            <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($case['description'] ?? '') ?></textarea>
        </div>

        <div class="form-row-3">
            <div class="form-group">
                <label class="form-label">Цена (₽) *</label>
                <input class="form-control" type="number" name="price" step="0.01" min="0" value="<?= htmlspecialchars($case['price'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Количество</label>
                <input class="form-control" type="number" name="count" min="0" value="<?= htmlspecialchars($case['count'] ?? 0) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Цвет</label>
                <input class="form-control" type="text" name="color" value="<?= htmlspecialchars($case['color'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Коллекция (выбрать из списка)</label>
                <select name="collection_id" class="form-select">
                            <option value="0">— Без коллекции —</option>
                            <?php foreach ($collections_list as $col_opt): ?>
                            <option value="<?= $col_opt['id_collection'] ?>"
                                <?= ($case['collection_id'] ?? 0) == $col_opt['id_collection'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($col_opt['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>">
            </div>
            <div class="form-group">
                <label class="form-label">Надпись</label>
                <input class="form-control" type="text" name="inscription" value="<?= htmlspecialchars($case['inscription'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Материал</label>
                <select class="form-control" name="material_id">
                    <option value="">— Выберите —</option>
                    <?php
 foreach ($materials as $m): ?>
                        <option value="<?= $m['id_material'] ?>" <?= ($case['material_id'] ?? null)==$m['id_material']?'selected':'' ?>>
                            <?= htmlspecialchars($m['material_name']) ?>
                        </option>
                    <?php
 endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Модель устройства</label>
                <select class="form-control" name="model_id">
                    <option value="">— Выберите —</option>
                    <?php
 foreach ($models as $m): ?>
                        <option value="<?= $m['id_model'] ?>" <?= ($case['model_id'] ?? null)==$m['id_model']?'selected':'' ?>>
                            <?= htmlspecialchars($m['firm'].' '.$m['model_name']) ?>
                        </option>
                    <?php
 endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <div class="form-check">
                <input type="checkbox" name="sticker" id="sticker" <?= ($case['sticker'] ?? 0)?'checked':'' ?>>
                <label class="form-label" for="sticker" style="text-transform:none;letter-spacing:0">Включить стикер в комплект</label>
            </div>
        </div>


        <div class="form-section-title">Изображение</div>
        <?php
 if (!empty($case['image']) && file_exists($uploadDir . '/'.$case['image'])): ?>
            <div style="margin-bottom:1rem">
                <img id="image-preview" src="<?= BASE_URL ?>/uploads/cases/<?= htmlspecialchars($case['image']) ?>" alt="" style="max-width:180px;border-radius:8px;border:1px solid var(--border)">
            </div>
        <?php
 else: ?>
            <img id="image-preview" src="" alt="" style="display:none;max-width:180px;border-radius:8px;border:1px solid var(--border);margin-bottom:1rem">
        <?php
 endif; ?>
        <div class="form-group">
            <label class="form-label">Загрузить изображение (jpg, png, webp)</label>
            <input class="form-control" type="file" name="image" id="image-upload" accept="image/*">
        </div>

        <div style="display:flex;gap:1rem;margin-top:1.5rem">
            <button type="submit" class="btn btn-primary">Сохранить</button>
            <a href="<?= BASE_URL ?>/admin/catalog.php" class="btn btn-ghost">Отмена</a>
        </div>
    </div>
</form>

<?php
 require_once __DIR__ . '/footer.php'; ?>
