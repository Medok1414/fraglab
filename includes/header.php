<?php
// Шапка сайта. Перед подключением задаётся $title — заголовок страницы
$user = current_user();
$page = basename($_SERVER['SCRIPT_NAME']);
$base = isset($inAdmin) ? '../' : '';
$menu = [
    'index.php'     => 'Главная',
    'trainings.php' => 'Тренировки',
    'coaches.php'   => 'Тренеры',
    'help.php'      => 'Помощь',
    'contacts.php'  => 'Контакты',
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'FragLab') ?> — FragLab</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;600&family=Unbounded:wght@700&display=swap">
    <link rel="stylesheet" href="<?= $base ?>assets/style.css">
</head>
<body>
<header class="header">
    <div class="container header-inner">
        <a href="<?= $base ?>index.php" class="logo">FragLab</a>
        <button class="burger" id="burger" aria-label="Меню">☰</button>
        <nav class="nav" id="nav">
            <?php foreach ($menu as $file => $label): ?>
                <a href="<?= $base . $file ?>" class="<?= $page === $file && !isset($inAdmin) ? 'active' : '' ?>"><?= $label ?></a>
            <?php endforeach; ?>
            <?php if ($user): ?>
                <?php if (is_admin()): ?>
                    <a href="<?= $base ?>admin/index.php" class="<?= isset($inAdmin) ? 'active' : '' ?>">Админ-панель</a>
                <?php endif; ?>
                <a href="<?= $base ?>cabinet.php" class="<?= $page === 'cabinet.php' ? 'active' : '' ?>">Кабинет (<?= e($user['username']) ?>)</a>
                <a href="<?= $base ?>logout.php" class="nav-muted">Выйти</a>
            <?php else: ?>
                <a href="<?= $base ?>login.php" class="<?= $page === 'login.php' ? 'active' : '' ?>">Вход</a>
                <a href="<?= $base ?>register.php" class="btn btn-small">Регистрация</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<?php if (empty($fullWidth)): ?>
<main class="container main">
<?php else: ?>
<main class="main-full">
<?php endif; ?>
<?php if ($msg = flash()): ?>
    <div class="alert alert-success <?= empty($fullWidth) ? '' : 'container' ?>"><?= e($msg) ?></div>
<?php endif; ?>
