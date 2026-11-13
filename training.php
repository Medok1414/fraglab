<?php
require 'config.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    'SELECT t.*, g.name AS game, g.color, g.hour_price, c.full_name AS coach_name, c.nickname AS coach
     FROM trainings t
     JOIN games g ON g.id = t.game_id
     LEFT JOIN coaches c ON c.id = t.coach_id
     WHERE t.id = ?'
);
$stmt->execute([$id]);
$training = $stmt->fetch();

if (!$training) {
    http_response_code(404);
    $title = 'Не найдено';
    require 'includes/header.php';
    echo '<p class="empty">Тренировка не найдена. <a href="trainings.php">Вернуться в каталог</a></p>';
    require 'includes/footer.php';
    exit;
}

$user = current_user();

// Запись на тренировку
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $stmt = $pdo->prepare('INSERT IGNORE INTO enrollments (user_id, training_id) VALUES (?, ?)');
    $stmt->execute([$user['id'], $id]);
    flash('Вы записались на тренировку «' . $training['title'] . '»');
    header('Location: cabinet.php');
    exit;
}

$enrolled = false;
if ($user) {
    $stmt = $pdo->prepare('SELECT 1 FROM enrollments WHERE user_id = ? AND training_id = ?');
    $stmt->execute([$user['id'], $id]);
    $enrolled = (bool)$stmt->fetch();
}

// Другие тренировки по той же игре
$stmt = $pdo->prepare(
    'SELECT t.*, g.name AS game, g.color, g.hour_price, c.nickname AS coach
     FROM trainings t
     JOIN games g ON g.id = t.game_id
     LEFT JOIN coaches c ON c.id = t.coach_id
     WHERE t.game_id = ? AND t.id <> ?
     ORDER BY t.duration_min'
);
$stmt->execute([$training['game_id'], $id]);
$related = $stmt->fetchAll();

$title = $training['title'];
require 'includes/header.php';
?>
<nav class="breadcrumbs">
    <a href="index.php">Главная</a> / <a href="trainings.php">Тренировки</a> / <?= e($training['title']) ?>
</nav>

<?php $levelNum = ['Новичок' => 1, 'Средний' => 2, 'Продвинутый' => 3][$training['level']]; ?>
<article class="detail" style="--game: <?= e($training['color']) ?>">
    <div class="detail-text">
        <h1><?= e($training['title']) ?></h1>
        <p><?= nl2br(e($training['description'])) ?></p>
    </div>
    <aside class="facts">
        <dl>
            <dt>Игра</dt><dd><?= e($training['game']) ?></dd>
            <dt>Уровень</dt><dd class="level"><span class="bars l<?= $levelNum ?>"><i></i><i></i><i></i></span><?= e($training['level']) ?></dd>
            <dt>Время</dt><dd><?= $training['duration_min'] ?> мин</dd>
            <dt>Цена</dt><dd><?= rub(training_price((int)$training['hour_price'], (int)$training['duration_min'])) ?></dd>
            <?php if ($training['coach_name']): ?>
                <dt>Тренер</dt><dd><a href="coach.php?id=<?= $training['coach_id'] ?>"><?= e($training['coach_name']) ?></a></dd>
            <?php endif; ?>
        </dl>
        <?php if (!$user): ?>
            <a href="login.php" class="btn btn-wide">Войти и записаться</a>
        <?php elseif ($enrolled): ?>
            <p class="alert alert-success" style="color: var(--graphite)">Вы записаны. <a href="cabinet.php">Открыть кабинет</a></p>
        <?php else: ?>
            <form method="post">
                <button class="btn btn-wide">Записаться</button>
            </form>
        <?php endif; ?>
        <?php if ($training['coach_id']): ?>
            <a href="coach.php?id=<?= $training['coach_id'] ?>" class="btn btn-outline btn-wide facts-coach">Занятие с тренером <?= e($training['coach']) ?></a>
        <?php endif; ?>
    </aside>
</article>

<?php if ($related): ?>
    <h2 class="section-title">Ещё по <?= e($training['game']) ?></h2>
    <div class="board">
        <?php foreach ($related as $t) include 'includes/card.php'; ?>
    </div>
<?php endif; ?>
<?php require 'includes/footer.php'; ?>
