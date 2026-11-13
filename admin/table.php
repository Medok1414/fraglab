<?php
require '../config.php';

if (!is_admin()) {
    header('Location: ../login.php');
    exit;
}

$tables = require 'tables.php';
$name = $_GET['t'] ?? '';
if (!isset($tables[$name])) {
    die('Неизвестная таблица');
}
$table = $tables[$name];
$fields = $table['fields'];
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

// Список вариантов для выпадающего списка
function field_options(PDO $pdo, array $field): array
{
    if (is_array($field['options'])) {
        return $field['options'];
    }
    return $pdo->query($field['options'])->fetchAll(PDO::FETCH_KEY_PAIR);
}

// Удаление записи
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($name === 'users' && $id === current_user()['id']) {
        flash('Нельзя удалить свою учётную запись');
    } else {
        $pdo->prepare("DELETE FROM $name WHERE id = ?")->execute([$id]);
        flash('Запись удалена');
    }
    header("Location: table.php?t=$name");
    exit;
}

// Добавление или изменение записи
$errors = [];
if (in_array($action, ['add', 'edit'], true) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = [];
    foreach ($fields as $col => $f) {
        $value = trim($_POST[$col] ?? '');
        if ($value === '' && !empty($f['nullable'])) {
            $value = null;
        } elseif ($value === '') {
            $errors[] = 'Заполните поле «' . $f['label'] . '»';
        }
        $values[$col] = $value;
    }

    if (!$errors) {
        $cols = array_keys($values);
        if ($action === 'add') {
            $sql = "INSERT INTO $name (" . implode(', ', $cols) . ') VALUES (' . implode(', ', array_fill(0, count($cols), '?')) . ')';
            $params = array_values($values);
        } else {
            $sql = "UPDATE $name SET " . implode(', ', array_map(fn($c) => "$c = ?", $cols)) . ' WHERE id = ?';
            $params = [...array_values($values), $id];
        }
        try {
            $pdo->prepare($sql)->execute($params);
            flash($action === 'add' ? 'Запись добавлена' : 'Изменения сохранены');
            header("Location: table.php?t=$name");
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Ошибка сохранения: ' . $e->getMessage();
        }
    }
    $record = $values;
}

if ($action === 'edit' && !isset($record)) {
    $stmt = $pdo->prepare("SELECT * FROM $name WHERE id = ?");
    $stmt->execute([$id]);
    $record = $stmt->fetch() ?: die('Запись не найдена');
}

$inAdmin = true;
$title = $table['title'];
require '../includes/header.php';
?>
<nav class="breadcrumbs">
    <a href="index.php">Админ-панель</a> / <a href="table.php?t=<?= $name ?>"><?= $table['title'] ?></a>
    <?= $action === 'add' ? '/ Добавление' : ($action === 'edit' ? '/ Изменение' : '') ?>
</nav>

<div class="admin-tabs">
    <?php foreach ($tables as $key => $t): ?>
        <a href="table.php?t=<?= $key ?>" class="<?= $key === $name ? 'active' : '' ?>"><?= $t['title'] ?></a>
    <?php endforeach; ?>
    <a href="queries.php">Запросы</a>
</div>

<?php if ($action === 'list'): ?>
    <?php $rows = $pdo->query("SELECT * FROM $name ORDER BY id")->fetchAll(); ?>
    <div class="admin-head">
        <h1 class="page-title"><?= $table['title'] ?></h1>
        <?php if ($table['can_add']): ?>
            <a href="table.php?t=<?= $name ?>&action=add" class="btn">+ Добавить</a>
        <?php endif; ?>
    </div>
    <table class="table">
        <thead>
        <tr>
            <?php foreach ($table['list'] as $col): ?>
                <th><?= $fields[$col]['label'] ?? ($col === 'id' ? 'ID' : ($col === 'created_at' ? 'Создан' : $col)) ?></th>
            <?php endforeach; ?>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <?php foreach ($table['list'] as $col): ?>
                    <td><?= e($row[$col]) ?></td>
                <?php endforeach; ?>
                <td class="actions">
                    <a href="table.php?t=<?= $name ?>&action=edit&id=<?= $row['id'] ?>" class="btn btn-small">Изменить</a>
                    <form method="post" action="table.php?t=<?= $name ?>&action=delete&id=<?= $row['id'] ?>">
                        <button class="btn btn-small btn-danger" data-confirm="Удалить запись №<?= $row['id'] ?>?">Удалить</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="form-card form-card-wide">
        <h1><?= $action === 'add' ? 'Добавление' : 'Изменение' ?>: <?= mb_strtolower($table['title']) ?></h1>
        <?php foreach ($errors as $err): ?>
            <div class="alert alert-error"><?= e($err) ?></div>
        <?php endforeach; ?>
        <form method="post">
            <?php foreach ($fields as $col => $f): $value = $record[$col] ?? ''; ?>
                <label><?= $f['label'] ?>
                    <?php if ($f['type'] === 'textarea'): ?>
                        <textarea name="<?= $col ?>" rows="4"><?= e($value) ?></textarea>
                    <?php elseif ($f['type'] === 'select'): ?>
                        <select name="<?= $col ?>">
                            <?php if (!empty($f['nullable'])): ?><option value="">— не выбрано —</option><?php endif; ?>
                            <?php foreach (field_options($pdo, $f) as $key => $label): ?>
                                <option value="<?= e($key) ?>" <?= (string)$key === (string)$value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="<?= $f['type'] ?>" name="<?= $col ?>" value="<?= e($value) ?>">
                    <?php endif; ?>
                </label>
            <?php endforeach; ?>
            <button class="btn">Сохранить</button>
            <a href="table.php?t=<?= $name ?>" class="btn btn-outline">Отмена</a>
        </form>
    </div>
<?php endif; ?>
<?php require '../includes/footer.php'; ?>
