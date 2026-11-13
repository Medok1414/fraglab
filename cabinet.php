<?php
require 'config.php';
require_login();

$user = current_user();

// Действия в кабинете: завершить тренировку, отписаться или отменить занятие
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recordId = (int)($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'done') {
        $stmt = $pdo->prepare("UPDATE enrollments SET status = 'Завершена' WHERE id = ? AND user_id = ?");
        $stmt->execute([$recordId, $user['id']]);
        flash('Тренировка отмечена как завершённая');
    } elseif (($_POST['action'] ?? '') === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM enrollments WHERE id = ? AND user_id = ?');
        $stmt->execute([$recordId, $user['id']]);
        flash('Вы отписались от тренировки');
    } elseif (($_POST['action'] ?? '') === 'cancel_booking') {
        $stmt = $pdo->prepare('DELETE FROM bookings WHERE id = ? AND user_id = ?');
        $stmt->execute([$recordId, $user['id']]);
        flash('Занятие с тренером отменено');
    }
    header('Location: cabinet.php');
    exit;
}

$stmt = $pdo->prepare('SELECT username, email, created_at FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$profile = $stmt->fetch();

$stmt = $pdo->prepare(
    'SELECT e.id, e.status, e.enrolled_at, t.id AS training_id, t.title, t.duration_min, g.name AS game
     FROM enrollments e
     JOIN trainings t ON t.id = e.training_id
     JOIN games g ON g.id = t.game_id
     WHERE e.user_id = ?
     ORDER BY e.status, e.enrolled_at DESC'
);
$stmt->execute([$user['id']]);
$items = $stmt->fetchAll();

// Предстоящие занятия с тренерами
$stmt = $pdo->prepare(
    'SELECT b.id, b.starts_at, c.id AS coach_id, c.full_name, c.nickname, g.name AS game
     FROM bookings b
     JOIN coaches c ON c.id = b.coach_id
     JOIN games g ON g.id = c.game_id
     WHERE b.user_id = ? AND b.starts_at > NOW()
     ORDER BY b.starts_at'
);
$stmt->execute([$user['id']]);
$bookings = $stmt->fetchAll();

$done = count(array_filter($items, fn($i) => $i['status'] === 'Завершена'));
$minutes = array_sum(array_map(fn($i) => $i['status'] === 'Завершена' ? $i['duration_min'] : 0, $items));
$percent = $items ? round($done / count($items) * 100) : 0;

$title = 'Личный кабинет';
require 'includes/header.php';
?>
<h1 class="page-title">Личный кабинет</h1>

<p class="muted" style="margin-bottom: 16px"><?= e($profile['username']) ?>, <?= e($profile['email']) ?></p>
<div class="summary">
    <div><b><?= count($items) ?></b><span>записей на тренировки</span></div>
    <div><b><?= $done ?></b><span>завершено</span></div>
    <div><b><?= $minutes ?></b><span>минут тренировок</span></div>
</div>

<div class="progress"><div style="width: <?= $percent ?>%"></div></div>
<p class="muted">Прогресс: <?= $percent ?>% тренировок завершено</p>

<h2 class="section-title">Мои тренировки</h2>
<?php if (!$items): ?>
    <p class="empty">Вы ещё не записаны ни на одну тренировку. <a href="trainings.php">Перейти в каталог</a></p>
<?php else: ?>
    <table class="table">
        <thead>
        <tr><th>Тренировка</th><th>Игра</th><th>Длительность</th><th>Дата записи</th><th>Статус</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($items as $i): ?>
            <tr>
                <td><a href="training.php?id=<?= $i['training_id'] ?>"><?= e($i['title']) ?></a></td>
                <td><?= e($i['game']) ?></td>
                <td><?= $i['duration_min'] ?> мин</td>
                <td><?= date('d.m.Y', strtotime($i['enrolled_at'])) ?></td>
                <td><span class="status <?= $i['status'] === 'Завершена' ? 'status-done' : '' ?>"><?= e($i['status']) ?></span></td>
                <td class="actions">
                    <form method="post">
                        <input type="hidden" name="id" value="<?= $i['id'] ?>">
                        <?php if ($i['status'] !== 'Завершена'): ?>
                            <button name="action" value="done" class="btn btn-small">Завершить</button>
                        <?php endif; ?>
                        <button name="action" value="delete" class="btn btn-small btn-danger" data-confirm="Отписаться от тренировки?">Отписаться</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h2 class="section-title">Занятия с тренерами</h2>
<?php if (!$bookings): ?>
    <p class="empty">Предстоящих занятий нет. Выберите время на странице <a href="coaches.php">тренера</a>.</p>
<?php else: ?>
    <table class="table">
        <thead>
        <tr><th>Тренер</th><th>Игра</th><th>Дата и время</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($bookings as $b): ?>
            <tr>
                <td><a href="coach.php?id=<?= $b['coach_id'] ?>"><?= e($b['full_name']) ?>, «<?= e($b['nickname']) ?>»</a></td>
                <td><?= e($b['game']) ?></td>
                <td><?= date('d.m.Y в H:i', strtotime($b['starts_at'])) ?></td>
                <td class="actions">
                    <form method="post">
                        <input type="hidden" name="id" value="<?= $b['id'] ?>">
                        <button name="action" value="cancel_booking" class="btn btn-small btn-danger" data-confirm="Отменить занятие?">Отменить</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php require 'includes/footer.php'; ?>
