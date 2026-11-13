<?php
require '../config.php';

// Страница доступна только администратору
if (!is_admin()) {
    header('Location: ../login.php');
    exit;
}

// Каждое представление создано в БД (см. database/fraglab.sql)
$views = [
    'v_cs2_trainings'       => 'Тренировки по дисциплине «CS2»',
    'v_beginner_trainings'  => 'Тренировки для уровня «Новичок»',
    'v_training_popularity' => 'Популярность тренировок (количество записей)',
    'v_search_aim'          => 'Поиск тренировок по ключевому слову «аим»',
    'v_short_trainings'     => 'Короткие тренировки (до 30 минут)',
];

$columnNames = [
    'id' => 'ID', 'title' => 'Название', 'level' => 'Уровень', 'duration_min' => 'Минут',
    'game' => 'Игра', 'enrollments_count' => 'Записей',
];

$inAdmin = true;
$title = 'Запросы';
require '../includes/header.php';
?>
<nav class="breadcrumbs"><a href="index.php">Админ-панель</a> / Запросы</nav>
<h1 class="page-title">Таблицы представлений</h1>
<?php $n = 1; foreach ($views as $view => $caption): ?>
    <?php $rows = $pdo->query("SELECT * FROM $view")->fetchAll(); ?>
    <section class="query">
        <h2><?= $n++ ?>. <?= e($caption) ?></h2>
        <code>SELECT * FROM <?= $view ?></code>
        <table class="table">
            <thead>
            <tr>
                <?php foreach (array_keys($rows[0] ?? []) as $col): ?>
                    <th><?= $columnNames[$col] ?? e($col) ?></th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr><?php foreach ($row as $value): ?><td><?= e($value) ?></td><?php endforeach; ?></tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?>
                <tr><td>Нет данных</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>
<?php endforeach; ?>
<?php require '../includes/footer.php'; ?>
