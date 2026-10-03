// The member-proof panel shows four screenshots at a time. When there are more, one frame at a
// time fades to the next story — another pane in place. It holds still while the visitor is
// looking (hover or focus), when it's off screen, and for anyone who prefers reduced motion.
const HOLD = 5200; // ms each change waits
const FADE = 600; // the brand's slowest transition: a backlight coming up

export function initProof() {
    const panel = document.querySelector('[data-proof]');
    const data = document.querySelector('[data-lightbox-items]');
    if (!panel || !data) return;

    let items = [];
    try {
        items = JSON.parse(data.textContent);
    } catch {
        return;
    }

    const slots = [...panel.querySelectorAll('[data-proof-slot]')];
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduced || /[?&]still\b/.test(window.location.search) || items.length <= slots.length) return;

    const shown = slots.map((slot) => Number(slot.querySelector('[data-lightbox-index]').dataset.lightboxIndex));
    let queue = items.map((_, i) => i).filter((i) => !shown.includes(i));
    let turn = 0;
    let visible = false;
    let paused = false;

    const label = (i) => `Fig. ${String(i + 1).padStart(2, '0')}`;

    const swap = (slot, position) => {
        if (!queue.length) queue = items.map((_, i) => i).filter((i) => !shown.includes(i));
        const next = queue.shift();
        const item = items[next];
        const button = slot.querySelector('[data-lightbox-index]');
        const img = slot.querySelector('img');
        const fig = slot.querySelector('[data-proof-fig]');

        const incoming = new Image();
        incoming.src = item.src;
        incoming.decode().catch(() => {}).finally(() => {
            slot.classList.add('is-changing');
            setTimeout(() => {
                img.src = item.src;
                img.alt = item.alt;
                img.width = item.width;
                img.height = item.height;
                button.dataset.lightboxIndex = String(next);
                button.setAttribute('aria-label', `View ${item.alt.toLowerCase()} full size`);
                if (fig) fig.textContent = label(next);
                queue.push(shown[position]);
                shown[position] = next;
                slot.classList.remove('is-changing');
            }, FADE);
        });
    };

    setInterval(() => {
        if (!visible || paused || document.hidden) return;
        const position = turn % slots.length;
        turn++;
        swap(slots[position], position);
    }, HOLD);

    new IntersectionObserver(([entry]) => (visible = entry.isIntersecting)).observe(panel);
    panel.addEventListener('pointerenter', () => (paused = true));
    panel.addEventListener('pointerleave', () => (paused = false));
    panel.addEventListener('focusin', () => (paused = true));
    panel.addEventListener('focusout', () => (paused = false));
}
