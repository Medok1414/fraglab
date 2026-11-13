<?php
// Описание таблиц для админ-панели: какие поля выводить и как их редактировать.
// type: text | textarea | number | color | select (options — SQL-запрос или массив)

return [
    'games' => [
        'title'   => 'Игры',
        'list'    => ['id', 'name', 'genre', 'hour_price'],
        'can_add' => true,
        'fields'  => [
            'name'        => ['label' => 'Название', 'type' => 'text'],
            'genre'       => ['label' => 'Жанр', 'type' => 'text'],
            'description' => ['label' => 'Описание', 'type' => 'textarea'],
            'color'       => ['label' => 'Цвет', 'type' => 'color'],
            'hour_price'  => ['label' => 'Цена часа, ₽', 'type' => 'number'],
        ],
    ],
    'coaches' => [
        'title'   => 'Тренеры',
        'list'    => ['id', 'full_name', 'nickname', 'experience_years'],
        'can_add' => true,
        'fields'  => [
            'full_name'        => ['label' => 'ФИО', 'type' => 'text'],
            'nickname'         => ['label' => 'Никнейм', 'type' => 'text'],
            'game_id'          => ['label' => 'Игра', 'type' => 'select', 'options' => 'SELECT id, name FROM games ORDER BY name'],
            'experience_years' => ['label' => 'Опыт (лет)', 'type' => 'number'],
            'bio'              => ['label' => 'О тренере', 'type' => 'textarea'],
        ],
    ],
    'trainings' => [
        'title'   => 'Тренировки',
        'list'    => ['id', 'title', 'level', 'duration_min'],
        'can_add' => true,
        'fields'  => [
            'title'        => ['label' => 'Название', 'type' => 'text'],
            'game_id'      => ['label' => 'Игра', 'type' => 'select', 'options' => 'SELECT id, name FROM games ORDER BY name'],
            'coach_id'     => ['label' => 'Тренер', 'type' => 'select', 'options' => 'SELECT id, CONCAT(full_name, " (", nickname, ")") FROM coaches ORDER BY full_name', 'nullable' => true],
            'level'        => ['label' => 'Уровень', 'type' => 'select', 'options' => ['Новичок' => 'Новичок', 'Средний' => 'Средний', 'Продвинутый' => 'Продвинутый']],
            'duration_min' => ['label' => 'Длительность (мин)', 'type' => 'number'],
            'description'  => ['label' => 'Описание', 'type' => 'textarea'],
        ],
    ],
    'bookings' => [
        'title'   => 'Занятия',
        'list'    => ['id', 'user_id', 'coach_id', 'starts_at'],
        'can_add' => true,
        'fields'  => [
            'user_id'   => ['label' => 'Пользователь', 'type' => 'select', 'options' => 'SELECT id, username FROM users ORDER BY username'],
            'coach_id'  => ['label' => 'Тренер', 'type' => 'select', 'options' => 'SELECT id, CONCAT(full_name, " (", nickname, ")") FROM coaches ORDER BY full_name'],
            'starts_at' => ['label' => 'Дата и время (ГГГГ-ММ-ДД ЧЧ:ММ:СС)', 'type' => 'text'],
        ],
    ],
    'users' => [
        'title'   => 'Пользователи',
        'list'    => ['id', 'username', 'email', 'role', 'created_at'],
        'can_add' => false,
        'fields'  => [
            'username' => ['label' => 'Логин', 'type' => 'text'],
            'email'    => ['label' => 'Email', 'type' => 'text'],
            'role'     => ['label' => 'Роль', 'type' => 'select', 'options' => ['user' => 'Пользователь', 'admin' => 'Администратор']],
        ],
    ],
];
