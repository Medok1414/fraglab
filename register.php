<?php
require 'config.php';

$errors = [];
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Проверка введённых данных
    if (mb_strlen($username) < 3) {
        $errors[] = 'Логин должен содержать не менее 3 символов.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email.';
    }
    if (mb_strlen($password) < 6) {
        $errors[] = 'Пароль должен содержать не менее 6 символов.';
    }
    if ($password !== $password2) {
        $errors[] = 'Пароли не совпадают.';
    }

    // Проверка уникальности логина и почты
    $stmt = $pdo->prepare('SELECT username, email FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    foreach ($stmt->fetchAll() as $row) {
        if ($row['username'] === $username) {
            $errors[] = 'Пользователь с таким логином уже существует.';
        }
        if ($row['email'] === $email) {
            $errors[] = 'Пользователь с таким email уже существует.';
        }
    }

    if (!$errors) {
        // Пароль хранится только в виде хеша
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
        $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT)]);

        $_SESSION['user'] = ['id' => (int)$pdo->lastInsertId(), 'username' => $username, 'role' => 'user'];
        flash('Регистрация прошла успешно. Добро пожаловать, ' . $username . '!');
        header('Location: cabinet.php');
        exit;
    }
}

$title = 'Регистрация';
require 'includes/header.php';
?>
<div class="form-card">
    <h1>Регистрация</h1>
    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= e($err) ?></div>
    <?php endforeach; ?>
    <form method="post">
        <label>Логин
            <input type="text" name="username" value="<?= e($username) ?>" required>
        </label>
        <label>Email
            <input type="email" name="email" value="<?= e($email) ?>" required>
        </label>
        <label>Пароль
            <span class="password-field">
                <input type="password" name="password" required>
                <button type="button" class="password-toggle" aria-pressed="false">Показать</button>
            </span>
        </label>
        <label>Повторите пароль
            <span class="password-field">
                <input type="password" name="password2" required>
                <button type="button" class="password-toggle" aria-pressed="false">Показать</button>
            </span>
        </label>
        <button class="btn btn-wide">Зарегистрироваться</button>
    </form>
    <p class="muted">Уже есть аккаунт? <a href="login.php">Войти</a></p>
</div>
<?php require 'includes/footer.php'; ?>
