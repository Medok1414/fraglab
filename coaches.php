<?php
require 'config.php';

$coaches = $pdo->query(
    'SELECT c.*, g.name AS game, g.color, COUNT(t.id) AS trainings_count
     FROM coaches c
     JOIN games g ON g.id = c.game_id
     LEFT JOIN trainings t ON t.coach_id = c.id
     GROUP BY c.id ORDER BY c.experience_years DESC'
)->fetchAll();

$title = 'Тренеры';
require 'includes/header.php';
?>
<h1 class="page-title">Тренеры</h1>
<div class="coaches">
    <?php foreach ($coaches as $c): ?>
        <div class="coach" style="--game: <?= e($c['color']) ?>">
            <div class="coach-nick"><?= e(mb_substr($c['nickname'], 0, 2)) ?></div>
            <div>
                <h3><a href="coach.php?id=<?= $c['id'] ?>"><?= e($c['full_name']) ?>, «<?= e($c['nickname']) ?>»</a></h3>
                <p><?= e($c['bio']) ?></p>
                <a href="coach.php?id=<?= $c['id'] ?>" class="btn btn-small btn-outline" style="margin-top: 12px">Записаться на занятие</a>
            </div>
            <dl>
                <dt>Игра</dt><dd><?= e($c['game']) ?></dd>
                <dt>Опыт</dt><dd><?= plural((int)$c['experience_years'], 'год', 'года', 'лет') ?></dd>
                <dt>Проведено</dt><dd><?= plural(sessions_done((int)$c['experience_years']), 'тренировка', 'тренировки', 'тренировок') ?></dd>
                <dt>Программ</dt><dd><?= $c['trainings_count'] ?> на сайте</dd>
            </dl>
        </div>
    <?php endforeach; ?>
</div>
<?php require 'includes/footer.php'; ?>
