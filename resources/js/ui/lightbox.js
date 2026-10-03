// Click a member's screenshot to see it full size, in a modal plate over the indigo scrim.
// Arrow keys or the buttons step through the rest.
export function initLightbox() {
    const dialog = document.querySelector('[data-lightbox-dialog]');
    const data = document.querySelector('[data-lightbox-items]');
    if (!dialog || !data || typeof dialog.showModal !== 'function') return;

    let items = [];
    try {
        items = JSON.parse(data.textContent);
    } catch {
        return;
    }
    if (!items.length) return;

    const image = dialog.querySelector('[data-lightbox-image]');
    const counter = dialog.querySelector('[data-lightbox-counter]');
    const prev = dialog.querySelector('[data-lightbox-prev]');
    const next = dialog.querySelector('[data-lightbox-next]');
    const pad = (n) => String(n).padStart(2, '0');
    let index = 0;

    const show = (i) => {
        index = (i + items.length) % items.length;
        const item = items[index];
        image.src = item.src;
        image.alt = item.alt;
        image.width = item.width;
        image.height = item.height;
        counter.textContent = `Fig. ${pad(index + 1)} / ${pad(items.length)}`;
    };

    prev.hidden = items.length < 2;
    next.hidden = items.length < 2;

    // Frames can change which story they show, so read the index at click time.
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-lightbox-index]');
        if (!trigger) return;
        show(Number(trigger.dataset.lightboxIndex));
        dialog.showModal();
        document.documentElement.classList.add('is-locked');
    });

    prev.addEventListener('click', () => show(index - 1));
    next.addEventListener('click', () => show(index + 1));

    dialog.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') show(index - 1);
        if (event.key === 'ArrowRight') show(index + 1);
    });

    // Clicking the scrim (not the plate itself) closes it.
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
    });

    dialog.addEventListener('close', () => document.documentElement.classList.remove('is-locked'));
}
