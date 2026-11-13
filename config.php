<?php
// Общие настройки: подключение к БД, сессия и вспомогательные функции

session_start();
date_default_timezone_set('Europe/Moscow');

const DB_HOST = 'localhost';
const DB_NAME = 'fraglab';
const DB_USER = 'root';
const DB_PASS = '';

// Контакты поддержки: выводятся в подвале и на странице «Контакты»
const SUPPORT_EMAIL = 'support@fraglab.ru';
const SUPPORT_TG = 'Medok_1414';
const SUPPORT_PHONE = '8 800 555 35 35';
const OFFICE_ADDRESS = 'г. Москва, ул. Нежинская, д. 7';

// Ключ «JavaScript API и HTTP Геокодер» из кабинета https://developer.tech.yandex.ru/
const YANDEX_MAPS_KEY = '';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Ошибка подключения к базе данных: ' . $e->getMessage());
}

// Экранирование вывода (защита от XSS)
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Число со склонением: plural(4, 'тренировка', 'тренировки', 'тренировок') → «4 тренировки»
function plural(int $n, string $one, string $few, string $many): string
{
    $mod10 = $n % 10;
    $mod100 = $n % 100;
    if ($mod10 === 1 && $mod100 !== 11) {
        $word = $one;
    } elseif ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 12 || $mod100 > 14)) {
        $word = $few;
    } else {
        $word = $many;
    }
    return "$n $word";
}

// Сколько тренировок тренер провёл за всё время: 12 в месяц × 12 месяцев × стаж в годах
const SESSIONS_PER_MONTH = 12;

function sessions_done(int $experienceYears): int
{
    return SESSIONS_PER_MONTH * 12 * $experienceYears;
}

// Цена тренировки: стоимость часа по игре × длительность, округление до 50 ₽
function training_price(int $hourPrice, int $minutes): int
{
    return (int)(round($hourPrice * $minutes / 60 / 50) * 50);
}

// 1500 → «1 500 ₽»
function rub(int $amount): string
{
    return number_format($amount, 0, '', ' ') . ' ₽';
}

// Текущий пользователь или null
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'admin';
}

// Переход на страницу входа, если пользователь не авторизован
function require_login(): void
{
    if (!current_user()) {
        header('Location: login.php');
        exit;
    }
}

// Сообщение, которое покажется один раз на следующей странице
function flash(?string $text = null): ?string
{
    if ($text !== null) {
        $_SESSION['flash'] = $text;
        return null;
    }
    $msg = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $msg;
}
