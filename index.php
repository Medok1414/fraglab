<?php
require 'config.php';

$games = $pdo->query(
    'SELECT g.*, COUNT(t.id) AS trainings_count
     FROM games g LEFT JOIN trainings t ON t.game_id = g.id
     GROUP BY g.id ORDER BY g.name'
)->fetchAll();

// Последние добавленные тренировки
$latest = $pdo->query(
    'SELECT t.*, g.name AS game, g.color, g.hour_price, c.nickname AS coach
     FROM trainings t
     JOIN games g ON g.id = t.game_id
     LEFT JOIN coaches c ON c.id = t.coach_id
     ORDER BY t.created_at DESC, t.id DESC LIMIT 6'
)->fetchAll();

// Счётчики для первого экрана
$trainingsTotal = (int)$pdo->query('SELECT COUNT(*) FROM trainings')->fetchColumn();
$coachesTotal = (int)$pdo->query('SELECT COUNT(*) FROM coaches')->fetchColumn();

$title = 'Главная';
$fullWidth = true;
require 'includes/header.php';
?>
<section class="hero">
    <div class="container hero-inner">
        <div class="hero-text">
            <h1>Тренируйся по программе тренера</h1>
            <p>Готовые тренировки по CS2, Dota 2, Valorant и Hunt: Showdown. Записывайся к тренерам на удобное время и отмечай прогресс в личном кабинете.</p>
            <div class="hero-actions">
                <a href="trainings.php" class="btn">Выбрать тренировку</a>
                <a href="coaches.php" class="btn btn-outline">Найти тренера</a>
            </div>
        </div>
        <!-- Декоративная мишень — знак бренда -->
        <div class="hero-mark" aria-hidden="true"></div>
    </div>
    <div class="container">
        <div class="hero-stats">
            <span><?= plural(count($games), 'дисциплина', 'дисциплины', 'дисциплин') ?></span>
            <span><?= plural($trainingsTotal, 'тренировка', 'тренировки', 'тренировок') ?></span>
            <span><?= plural($coachesTotal, 'тренер', 'тренера', 'тренеров') ?></span>
        </div>
    </div>
</section>

<div class="container main">
    <h2 class="section-title">Дисциплины</h2>
    <div class="games">
        <?php foreach ($games as $g): ?>
            <a href="trainings.php?game=<?= $g['id'] ?>" class="game" style="--game: <?= e($g['color']) ?>">
                <h3><?= e($g['name']) ?></h3>
                <span><?= e($g['genre']) ?>, <?= plural((int)$g['trainings_count'], 'тренировка', 'тренировки', 'тренировок') ?></span>
                <span class="game-price"><?= rub((int)$g['hour_price']) ?> за час</span>
            </a>
        <?php endforeach; ?>
    </div>

    <h2 class="section-title">Новые тренировки</h2>
    <div class="board">
        <?php include 'includes/board_head.php'; ?>
        <?php foreach ($latest as $t) include 'includes/card.php'; ?>
    </div>
    <p style="margin-top: 20px"><a href="trainings.php">Все тренировки</a></p>
</div>
<?php require 'includes/footer.php'; ?>
