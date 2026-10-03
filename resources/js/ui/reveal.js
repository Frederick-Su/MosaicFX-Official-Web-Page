// Sections fade in as they scroll into view — a calm fade, never a slide. Elements opt in
// with [data-reveal]; an optional data-reveal-delay (ms) staggers siblings.
export function initReveal() {
    const items = document.querySelectorAll('[data-reveal]');
    if (!items.length) return;

    if (!('IntersectionObserver' in window)) {
        items.forEach((el) => el.classList.add('is-in'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-in');
                observer.unobserve(entry.target);
            });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.12 },
    );

    items.forEach((el) => {
        if (el.dataset.revealDelay) el.style.setProperty('--reveal-delay', `${el.dataset.revealDelay}ms`);
        observer.observe(el);
    });
}

// The hero's words fade back as the page scrolls past them.
export function initHeroScroll() {
    const hero = document.querySelector('[data-hero]');
    if (!hero) return;

    let queued = false;
    const update = () => {
        queued = false;
        const progress = Math.min(Math.max(window.scrollY / Math.max(hero.offsetHeight, 1), 0), 1);
        hero.style.setProperty('--hero-progress', progress.toFixed(4));
    };
    window.addEventListener(
        'scroll',
        () => {
            if (queued) return;
            queued = true;
            requestAnimationFrame(update);
        },
        { passive: true },
    );
    update();
}
