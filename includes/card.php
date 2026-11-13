<?php
// Строка таблицы тренировок. Ожидает массив $t с полями тренировки, игры и тренера
$levelNum = ['Новичок' => 1, 'Средний' => 2, 'Продвинутый' => 3][$t['level']];
?>
<a href="training.php?id=<?= $t['id'] ?>" class="board-row" style="--game: <?= e($t['color']) ?>">
    <span class="row-game"><?= e($t['game']) ?></span>
    <span class="row-title">
        <?= e($t['title']) ?>
        <?php if (!empty($t['coach'])): ?><small>Тренер <?= e($t['coach']) ?></small><?php endif; ?>
    </span>
    <span class="level"><span class="bars l<?= $levelNum ?>"><i></i><i></i><i></i></span><?= e($t['level']) ?></span>
    <span class="row-time"><?= $t['duration_min'] ?> мин</span>
    <span class="row-price"><?= rub(training_price((int)$t['hour_price'], (int)$t['duration_min'])) ?></span>
</a>
