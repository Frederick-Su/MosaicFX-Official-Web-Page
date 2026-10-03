// Until the Telegram link is set (MOSAIC_TELEGRAM_URL in .env), the buttons stay clickable
// and show a short notice instead of going nowhere.
export function initTelegramPlaceholder() {
    const buttons = document.querySelectorAll('[data-telegram-placeholder]');
    const toast = document.querySelector('[data-toast]');
    if (!buttons.length || !toast) return;

    const message = toast.querySelector('[data-toast-message]');
    let timer;
    buttons.forEach((button) =>
        button.addEventListener('click', (event) => {
            event.preventDefault();
            message.textContent = button.dataset.telegramPlaceholder;
            toast.hidden = false;
            requestAnimationFrame(() => toast.classList.add('is-shown'));
            clearTimeout(timer);
            timer = setTimeout(() => toast.classList.remove('is-shown'), 3600);
        }),
    );
}
