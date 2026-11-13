<?php
require '../config.php';

// Доступ только для администратора
if (!is_admin()) {
    header('Location: ../login.php');
    exit;
}

$tables = require 'tables.php';
$counts = [];
foreach ($tables as $name => $t) {
    $counts[$name] = $pdo->query("SELECT COUNT(*) FROM $name")->fetchColumn();
}
$counts['enrollments'] = $pdo->query('SELECT COUNT(*) FROM enrollments')->fetchColumn();

$inAdmin = true;
$title = 'Админ-панель';
require '../includes/header.php';
?>
<h1 class="page-title">Административная панель</h1>
<div class="admin-counts">
    <?php foreach ($tables as $name => $t): ?>
        <a href="table.php?t=<?= $name ?>">
            <span><?= $t['title'] ?></span>
            <b><?= $counts[$name] ?></b>
        </a>
    <?php endforeach; ?>
    <a href="queries.php"><span>Запросы (таблицы представлений)</span><b>5</b></a>
</div>
<p class="muted" style="margin-top: 16px">Всего записей на тренировки: <?= $counts['enrollments'] ?></p>
<?php require '../includes/footer.php'; ?>
