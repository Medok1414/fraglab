<?php
require 'config.php';

$games = $pdo->query('SELECT id, name FROM games ORDER BY name')->fetchAll();
$levels = ['Новичок', 'Средний', 'Продвинутый'];

// Параметры фильтра из адресной строки
$game  = (int)($_GET['game'] ?? 0);
$level = $_GET['level'] ?? '';
$q     = trim($_GET['q'] ?? '');
$max   = (int)($_GET['max'] ?? 0);

// Запрос собирается из условий, значения передаются через параметры
$where = [];
$params = [];
if ($game) {
    $where[] = 't.game_id = ?';
    $params[] = $game;
}
if (in_array($level, $levels, true)) {
    $where[] = 't.level = ?';
    $params[] = $level;
}
if ($q !== '') {
    $where[] = '(t.title LIKE ? OR t.description LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($max > 0) {
    $where[] = 't.duration_min <= ?';
    $params[] = $max;
}

$sql = 'SELECT t.*, g.name AS game, g.color, g.hour_price, c.nickname AS coach
        FROM trainings t
        JOIN games g ON g.id = t.game_id
        LEFT JOIN coaches c ON c.id = t.coach_id';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY g.name, t.duration_min';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$trainings = $stmt->fetchAll();

$title = 'Тренировки';
require 'includes/header.php';
?>
<h1 class="page-title">Тренировки</h1>

<form class="filter" method="get">
    <input type="text" name="q" placeholder="Поиск: например, аим" value="<?= e($q) ?>">
    <select name="game">
        <option value="0">Все игры</option>
        <?php foreach ($games as $g): ?>
            <option value="<?= $g['id'] ?>" <?= $game === (int)$g['id'] ? 'selected' : '' ?>><?= e($g['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="level">
        <option value="">Любой уровень</option>
        <?php foreach ($levels as $l): ?>
            <option <?= $level === $l ? 'selected' : '' ?>><?= $l ?></option>
        <?php endforeach; ?>
    </select>
    <select name="max">
        <option value="0">Любая длительность</option>
        <?php foreach ([20, 30, 45, 60] as $m): ?>
            <option value="<?= $m ?>" <?= $max === $m ? 'selected' : '' ?>>до <?= $m ?> мин</option>
        <?php endforeach; ?>
    </select>
    <button class="btn">Найти</button>
    <a href="trainings.php" class="btn btn-outline">Сбросить</a>
</form>

<p class="muted" style="margin-bottom: 12px">Найдено: <?= plural(count($trainings), 'тренировка', 'тренировки', 'тренировок') ?></p>
<div class="board">
    <?php include 'includes/board_head.php'; ?>
    <?php foreach ($trainings as $t) include 'includes/card.php'; ?>
</div>
<?php if (!$trainings): ?>
    <p class="empty">Под эти условия тренировок нет. Попробуйте убрать часть фильтров.</p>
<?php endif; ?>
<?php require 'includes/footer.php'; ?>
