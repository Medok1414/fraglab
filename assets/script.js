// Мобильное меню
const burger = document.getElementById('burger');
const nav = document.getElementById('nav');
burger.addEventListener('click', () => nav.classList.toggle('open'));

// Раскрывающиеся вопросы на странице «Помощь»
document.querySelectorAll('.faq-question').forEach(question => {
    question.addEventListener('click', () => question.parentElement.classList.toggle('open'));
});

// Показ и скрытие пароля в формах входа и регистрации
document.querySelectorAll('.password-toggle').forEach(button => {
    button.addEventListener('click', () => {
        const input = button.previousElementSibling;
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        button.textContent = show ? 'Скрыть' : 'Показать';
        button.setAttribute('aria-pressed', show);
        input.focus();
    });
});

// Подтверждение удаления
document.querySelectorAll('[data-confirm]').forEach(button => {
    button.addEventListener('click', event => {
        if (!confirm(button.dataset.confirm)) {
            event.preventDefault();
        }
    });
});
