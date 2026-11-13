-- База данных веб-приложения «FragLab — платформа для киберспортивных тренировок»
-- Импорт: phpMyAdmin → Импорт → выбрать этот файл (база создаётся автоматически)

DROP DATABASE IF EXISTS fraglab;
CREATE DATABASE fraglab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fraglab;

-- Дисциплины (игры)
CREATE TABLE games (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50)  NOT NULL UNIQUE,
    genre       VARCHAR(50)  NOT NULL,
    description TEXT         NOT NULL,
    color       VARCHAR(7)   NOT NULL DEFAULT '#7C3AED',
    -- Стоимость часа занятий: чем популярнее игра, тем выше спрос и цена
    hour_price  INT          NOT NULL DEFAULT 1000
) ENGINE=InnoDB;

-- Тренеры
CREATE TABLE coaches (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    full_name        VARCHAR(100) NOT NULL,
    nickname         VARCHAR(50)  NOT NULL,
    game_id          INT          NOT NULL,
    experience_years TINYINT      NOT NULL DEFAULT 1,
    bio              TEXT         NOT NULL,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Пользователи
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Тренировки (программы)
CREATE TABLE trainings (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(150) NOT NULL,
    game_id      INT          NOT NULL,
    coach_id     INT          NULL,
    level        ENUM('Новичок','Средний','Продвинутый') NOT NULL DEFAULT 'Новичок',
    duration_min SMALLINT     NOT NULL,
    description  TEXT         NOT NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id)  REFERENCES games(id)   ON DELETE CASCADE,
    FOREIGN KEY (coach_id) REFERENCES coaches(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Записи пользователей на тренировки
CREATE TABLE enrollments (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT      NOT NULL,
    training_id INT      NOT NULL,
    status      ENUM('В процессе','Завершена') NOT NULL DEFAULT 'В процессе',
    enrolled_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_training (user_id, training_id),
    FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE CASCADE,
    FOREIGN KEY (training_id) REFERENCES trainings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Индивидуальные занятия с тренером по времени
CREATE TABLE bookings (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT      NOT NULL,
    coach_id   INT      NOT NULL,
    starts_at  DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_coach_time (coach_id, starts_at),
    UNIQUE KEY uq_user_time (user_id, starts_at),
    FOREIGN KEY (user_id)  REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (coach_id) REFERENCES coaches(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Тестовые данные
-- ---------------------------------------------------------------
INSERT INTO games (name, genre, description, color, hour_price) VALUES
('CS2', 'Тактический шутер', 'Командный шутер 5 на 5. Важны стрельба, раскидка гранат и командная тактика.', '#F59E0B', 1500),
('Dota 2', 'MOBA', 'Командная стратегия 5 на 5. Важны знание героев, фарм и командные драки.', '#EF4444', 1300),
('Valorant', 'Тактический шутер', 'Шутер с уникальными способностями агентов. Важны аим и использование умений.', '#EC4899', 1100),
('Hunt: Showdown', 'PvPvE-шутер', 'Охота на монстров в болотах Луизианы. Важны звук, позиционка и точный выстрел.', '#7C9A3D', 800);

INSERT INTO coaches (full_name, nickname, game_id, experience_years, bio) VALUES
('Андрей Соколов', 'Sokol', 1, 6, 'Бывший игрок полупрофессиональной сцены, специализируется на стрельбе и позиционке.'),
('Мария Ким', 'Kimchi', 2, 5, 'Тренер по драфту и макроигре, работала с любительскими командами.'),
('Илья Петров', 'Pyro', 3, 4, 'Помогает поставить аим и разобраться в агентах-дуэлянтах.'),
('Дмитрий Орлов', 'Orel', 4, 3, 'Учит слушать карту, выбирать позицию и выносить баунти без потерь.'),
('Егор Лебедев', 'Lebed', 1, 2, 'Тренер по раскидке гранат и заходам на плент.');

-- Пароли: admin / admin123, player / user123
INSERT INTO users (username, email, password_hash, role) VALUES
('admin',  'admin@fraglab.ru',  '$2y$10$ZPIydYSmhnc/jc7QRdxdx.reIlMqFxtnFQ22NvMpZjzoghJ6rWEF6', 'admin'),
('player', 'player@fraglab.ru', '$2y$10$gDQ4Xl/HM.SMocHODS2hD.ZUbF4J.SFspMJ1BQSX4F7lGCCXxyk.u', 'user');

-- Тренировки
INSERT INTO trainings (title, game_id, coach_id, level, duration_min, description) VALUES
-- Sokol, CS2, 6 лет
('Разминка аима перед игрой',             1, 1, 'Новичок',     20, 'Короткая разминка перед матчем: флики, трекинг и стрельба по ботам. Помогает войти в игру с первого раунда.'),
('Спрей-контроль: основы стрельбы очередью', 1, 1, 'Новичок',  25, 'Разбираем отдачу на стене и ботах.'),
('Кроссхейр-плейсмент',                   1, 1, 'Средний',     25, 'Держим прицел на уровне головы и там, где появится соперник. Меньше лишних движений мышью в перестрелке.'),
('Контрстрейф и стрельба в движении',     1, 1, 'Средний',     20, 'Как мгновенно останавливаться перед выстрелом и не терять точность на пиках.'),
('Пики: шолдер, джиггл и вайд-пик',       1, 1, 'Продвинутый', 30, 'Когда байтить шолдером, когда джигглить за инфой, а когда выходить вайд-пиком на дуэль. Репик и смена угла после фрага.'),
('AWP: флики, квикскоуп',                  1, 1, 'Продвинутый', 45, 'Флики и квикскоуп с авика, удержание мида и смена позиции после фрага, чтобы не словить трейд.'),
-- Kimchi, Dota 2, 5 лет
('Ластхит и денай: основы фарма',         2, 2, 'Новичок',     25, 'Тренируем добивание крипов в демо-режиме, считаем крипов за 10 минут.'),
('Разбор реплеев',                        2, 2, 'Новичок',     40, 'Учимся находить ошибки в своих матчах: смерти, пропущенные тайминги, лишние перемещения.'),
('Тайминги рун и стаков',                 2, 2, 'Средний',     25, 'Когда идти за руной, как стакать лагеря и не терять фарм на линии.'),
('Вардинг и деварды: контроль карты',     2, 2, 'Средний',     30, 'Где ставить варды на каждой стадии игры, как девардить и читать ротации соперника.'),
('Драфт: контрпики и ласт-пик',           2, 2, 'Продвинутый', 45, 'Собираем драфт под тайминги команды, прячем роли за флекс-пиками и закрываем ласт-пиком слабые места.'),
-- Pyro, Valorant, 4 года
('Разбор твоей игры с тренером',          3, 3, 'Новичок',     40, 'Тренер смотрит запись твоего матча и разбирает ошибки: позицию, использование способностей и решения в раундах.'),
('Кроссхейр-плейсмент',                   3, 3, 'Средний',     20, 'Держим прицел там, где появится соперник, и учимся префаерить стандартные углы на картах пула.'),
('Дуэлянт: энтри, спейс и трейды',        3, 3, 'Средний',     35, 'Как заходить первым, создавать спейс для команды и не умирать без трейда.'),
('Ретейк и пост-плент: лайнапы и сетапы', 3, 3, 'Продвинутый', 50, 'Лайнапы под пост-плент, сетапы на ретейк и распределение ролей на всех картах актуального пула.'),
-- Orel, Hunt: Showdown, 3 года
('Гайд на звук: что шумит на карте',      4, 4, 'Новичок',     35, 'Разбираем всё, что издаёт звук: вороны, утки, собаки, цепи, битое стекло, вода и двери. Учимся по звуку понимать, где враг, на каком расстоянии и этаже.'),
('Прицел и пики: стреляем первым',        4, 4, 'Средний',     30, 'Позиционирование прицела у углов и окон, пики из-за укрытий и аим на средней и дальней дистанции.'),
('Не сиди в логове 3 часа: выход с наградой', 4, 4, 'Продвинутый', 50, 'Агрессивный выход с наградой и тактика активной защиты: пушим сами, ловим фланг и не даём команде соперника забрать баунти.'),
-- Lebed, CS2, 2 года
('Раскидка гранат: смоки, флешки и молики', 1, 5, 'Средний',   40, 'Раскидка на всех картах маппула: Mirage, Inferno, Nuke, Ancient, Anubis, Dust2 и Train. Практика на локальном сервере.'),
('Заход на плент: раскидка и трейды',     1, 5, 'Продвинутый', 60, 'Тайминги раскидки под заход, трейды и установка бомбы. Игра в пост-плент.');

INSERT INTO enrollments (user_id, training_id, status) VALUES
(2, 1, 'Завершена'),
(2, 2, 'В процессе'),
(2, 12, 'В процессе');

-- Занятия с тренерами: завтра и послезавтра
INSERT INTO bookings (user_id, coach_id, starts_at) VALUES
(2, 1, TIMESTAMP(CURDATE() + INTERVAL 1 DAY, '14:00:00')),
(2, 3, TIMESTAMP(CURDATE() + INTERVAL 2 DAY, '18:00:00'));

-- ---------------------------------------------------------------
-- Представления (выводятся в админ-панели на странице «Запросы»)
-- ---------------------------------------------------------------

-- 1. Тренировки по дисциплине CS2
CREATE VIEW v_cs2_trainings AS
SELECT t.id, t.title, t.level, t.duration_min
FROM trainings t JOIN games g ON g.id = t.game_id
WHERE g.name = 'CS2';

-- 2. Тренировки для новичков
CREATE VIEW v_beginner_trainings AS
SELECT t.id, t.title, g.name AS game, t.duration_min
FROM trainings t JOIN games g ON g.id = t.game_id
WHERE t.level = 'Новичок';

-- 3. Популярность тренировок (количество записей)
CREATE VIEW v_training_popularity AS
SELECT t.id, t.title, COUNT(e.id) AS enrollments_count
FROM trainings t LEFT JOIN enrollments e ON e.training_id = t.id
GROUP BY t.id, t.title
ORDER BY enrollments_count DESC, t.title;

-- 4. Поиск по ключевому слову «аим»
CREATE VIEW v_search_aim AS
SELECT t.id, t.title, g.name AS game, t.level
FROM trainings t JOIN games g ON g.id = t.game_id
WHERE t.title LIKE '%аим%' OR t.description LIKE '%аим%';

-- 5. Короткие тренировки (до 30 минут)
CREATE VIEW v_short_trainings AS
SELECT t.id, t.title, g.name AS game, t.duration_min
FROM trainings t JOIN games g ON g.id = t.game_id
WHERE t.duration_min <= 30
ORDER BY t.duration_min;
