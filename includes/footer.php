</main>
<footer class="footer">
    <div class="container footer-inner">
        <div>
            <div class="logo">FragLab</div>
            <p>Платформа для киберспортивных тренировок</p>
            <p class="footer-copy">© <?= date('Y') ?> FragLab. Учебный проект.</p>
        </div>
        <dl class="footer-contacts">
            <dt>Почта</dt>
            <dd><a href="mailto:<?= SUPPORT_EMAIL ?>"><?= SUPPORT_EMAIL ?></a></dd>
            <dt>Telegram поддержки</dt>
            <dd><a href="https://t.me/<?= SUPPORT_TG ?>" target="_blank" rel="noopener">@<?= SUPPORT_TG ?></a></dd>
            <dt>Телефон</dt>
            <dd><a href="tel:<?= str_replace(' ', '', SUPPORT_PHONE) ?>"><?= SUPPORT_PHONE ?></a></dd>
            <dt>Адрес</dt>
            <dd><?= OFFICE_ADDRESS ?></dd>
        </dl>
    </div>
</footer>
<script src="<?= isset($inAdmin) ? '../' : '' ?>assets/script.js"></script>
</body>
</html>
