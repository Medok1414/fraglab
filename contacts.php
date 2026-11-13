<?php
require 'config.php';

$title = 'Контакты';
require 'includes/header.php';
?>
<h1 class="page-title">Контакты</h1>
<div class="contacts">
    <div class="contacts-info">
        <p><b>Адрес</b> <?= OFFICE_ADDRESS ?></p>
        <p><b>Email</b> <a href="mailto:<?= SUPPORT_EMAIL ?>"><?= SUPPORT_EMAIL ?></a></p>
        <p><b>Telegram поддержки</b> <a href="https://t.me/<?= SUPPORT_TG ?>" target="_blank" rel="noopener">@<?= SUPPORT_TG ?></a></p>
        <p><b>Телефон</b> <a href="tel:<?= str_replace(' ', '', SUPPORT_PHONE) ?>"><?= SUPPORT_PHONE ?></a></p>
        <p><b>Время работы</b> пн–пт, 10:00–19:00</p>
    </div>
    <!-- Карта подключается через API Яндекс Карт 2.1 -->
    <div id="map" class="map"></div>
</div>

<script src="https://api-maps.yandex.ru/2.1/?apikey=<?= urlencode(YANDEX_MAPS_KEY) ?>&lang=ru_RU"></script>
<script>
    // Функция выполнится, когда библиотека API будет загружена
    ymaps.ready(() => {
        const office = [55.7146, 37.4746];
        const map = new ymaps.Map('map', {
            center: office,
            zoom: 16,
            controls: ['zoomControl', 'fullscreenControl']
        });
        const placemark = new ymaps.Placemark(office, {
            balloonContentHeader: 'FragLab',
            balloonContentBody: 'ул. Нежинская, д. 7',
            hintContent: 'FragLab — платформа для киберспортивных тренировок'
        }, {
            preset: 'islands#redDotIcon'
        });
        map.geoObjects.add(placemark);
        placemark.balloon.open();
    });
</script>
<?php require 'includes/footer.php'; ?>
