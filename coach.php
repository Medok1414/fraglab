<?php
require 'config.php';

// Расписание: ближайшие 7 дней, начиная с завтрашнего, и фиксированное время занятий
const SLOT_TIMES = ['12:00', '14:00', '16:00', '18:00', '20:00'];
const DAYS_AHEAD = 7;
const WEEKDAYS = [1 => 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    'SELECT c.*, g.name AS game, g.color, g.hour_price FROM coaches c JOIN games g ON g.id = c.game_id WHERE c.id = ?'
);
$stmt->execute([$id]);
$coach = $stmt->fetch();

if (!$coach) {
    http_response_code(404);
    $title = 'Не найдено';
    require 'includes/header.php';
    echo '<p class="empty">Тренер не найден. <a href="coaches.php">Вернуться к списку тренеров</a></p>';
    require 'includes/footer.php';
    exit;
}

// Все слоты расписания в формате «Y-m-d H:i:s»
$days = [];
for ($i = 1; $i <= DAYS_AHEAD; $i++) {
    $days[] = date('Y-m-d', strtotime("+$i day"));
}
$slots = [];
foreach ($days as $day) {
    foreach (SLOT_TIMES as $time) {
        $slots[] = "$day $time:00";
    }
}

$user = current_user();

// Запись на выбранное время
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_login();
    $slot = $_POST['slot'] ?? '';
    if (!in_array($slot, $slots, true)) {
        flash('Это время недоступно для записи');
    } else {
        // Проверка, что у пользователя нет другого занятия в это же время
        $stmt = $pdo->prepare('SELECT 1 FROM bookings WHERE user_id = ? AND starts_at = ?');
        $stmt->execute([$user['id'], $slot]);
        if ($stmt->fetch()) {
            flash('У вас уже есть занятие в это время');
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO bookings (user_id, coach_id, starts_at) VALUES (?, ?, ?)');
                $stmt->execute([$user['id'], $id, $slot]);
                flash('Вы записаны к тренеру ' . $coach['nickname'] . ' на ' . date('d.m в H:i', strtotime($slot)));
            } catch (PDOException $e) {
                // Уникальный ключ (coach_id, starts_at): время уже занял кто-то другой
                flash('Это время уже занято, выберите другое');
            }
        }
    }
    header("Location: coach.php?id=$id");
    exit;
}

// Занятые слоты тренера на неделю: время => id пользователя
$stmt = $pdo->prepare('SELECT starts_at, user_id FROM bookings WHERE coach_id = ? AND starts_at BETWEEN ? AND ?');
$stmt->execute([$id, $slots[0], end($slots)]);
$busy = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$stmt = $pdo->prepare(
    'SELECT t.*, g.name AS game, g.color, g.hour_price, NULL AS coach
     FROM trainings t JOIN games g ON g.id = t.game_id
     WHERE t.coach_id = ? ORDER BY t.duration_min'
);
$stmt->execute([$id]);
$trainings = $stmt->fetchAll();

$title = $coach['full_name'];
require 'includes/header.php';
?>
<nav class="breadcrumbs">
    <a href="index.php">Главная</a> / <a href="coaches.php">Тренеры</a> / <?= e($coach['full_name']) ?>
</nav>

<div class="coach-head" style="--game: <?= e($coach['color']) ?>">
    <div class="coach-nick"><?= e(mb_substr($coach['nickname'], 0, 2)) ?></div>
    <div>
        <h1 class="page-title" style="margin-bottom: 8px"><?= e($coach['full_name']) ?>, «<?= e($coach['nickname']) ?>»</h1>
        <p><?= e($coach['bio']) ?></p>
        <p class="muted"><?= e($coach['game']) ?>, опыт <?= plural((int)$coach['experience_years'], 'год', 'года', 'лет') ?>, проведено тренировок: <?= sessions_done((int)$coach['experience_years']) ?></p>
    </div>
</div>

<h2 class="section-title">Записаться на занятие</h2>
<p class="muted" style="margin-bottom: 16px">Индивидуальное занятие длится час и стоит <b><?= rub((int)$coach['hour_price']) ?></b>. Выберите свободное время на ближайшую неделю.</p>
<div class="schedule">
    <table>
        <thead>
        <tr>
            <th></th>
            <?php foreach (SLOT_TIMES as $time): ?><th><?= $time ?></th><?php endforeach; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($days as $day): ?>
            <tr>
                <th><?= WEEKDAYS[date('N', strtotime($day))] ?>, <?= date('d.m', strtotime($day)) ?></th>
                <?php foreach (SLOT_TIMES as $time): $slot = "$day $time:00"; ?>
                    <td>
                        <?php if (isset($busy[$slot]) && $user && (int)$busy[$slot] === $user['id']): ?>
                            <span class="slot slot-mine">Вы записаны</span>
                        <?php elseif (isset($busy[$slot])): ?>
                            <span class="slot slot-busy">Занято</span>
                        <?php elseif ($user): ?>
                            <form method="post">
                                <button class="slot slot-free" name="slot" value="<?= $slot ?>">Записаться</button>
                            </form>
                        <?php else: ?>
                            <a href="login.php" class="slot slot-free">Свободно</a>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php if (!$user): ?>
    <p class="muted" style="margin-top: 12px">Чтобы записаться, <a href="login.php">войдите в аккаунт</a>.</p>
<?php endif; ?>

<h2 class="section-title">Тренировки тренера</h2>
<div class="board">
    <?php include 'includes/board_head.php'; ?>
    <?php foreach ($trainings as $t) include 'includes/card.php'; ?>
</div>
<?php require 'includes/footer.php'; ?>
