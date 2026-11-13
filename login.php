<?php
require 'config.php';

$error = '';
$username = trim($_POST['username'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Сравнение введённого пароля с хешем из БД
    if ($user && password_verify($_POST['password'] ?? '', $user['password_hash'])) {
        $_SESSION['user'] = ['id' => (int)$user['id'], 'username' => $user['username'], 'role' => $user['role']];
        flash('Вы вошли как ' . $user['username']);
        header('Location: ' . ($user['role'] === 'admin' ? 'admin/index.php' : 'cabinet.php'));
        exit;
    }
    $error = 'Неверный логин или пароль.';
}

$title = 'Вход';
require 'includes/header.php';
?>
<div class="form-card">
    <h1>Вход в аккаунт</h1>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <label>Логин
            <input type="text" name="username" value="<?= e($username) ?>" required>
        </label>
        <label>Пароль
            <span class="password-field">
                <input type="password" name="password" required>
                <button type="button" class="password-toggle" aria-pressed="false">Показать</button>
            </span>
        </label>
        <button class="btn btn-wide">Войти</button>
    </form>
    <p class="muted">Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
</div>
<?php require 'includes/footer.php'; ?>
