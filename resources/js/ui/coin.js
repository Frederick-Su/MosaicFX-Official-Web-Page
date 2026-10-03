// The hero coin responds to the pointer as if it were being pressed: the side under the cursor
// sinks away, and the gold catches the light right where you touch it. The CSS does the drawing;
// this only reports where the pointer is (--mx/--my, 0–1) and how far to tilt.
const MAX_TILT = 14; // degrees

export function initCoin() {
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    document.querySelectorAll('[data-coin]').forEach((coin) => {
        let frame = 0;
        let last = null;

        const apply = () => {
            frame = 0;
            if (!last) return;
            const rect = coin.getBoundingClientRect();
            const mx = Math.min(Math.max((last.x - rect.left) / rect.width, 0), 1);
            const my = Math.min(Math.max((last.y - rect.top) / rect.height, 0), 1);
            coin.style.setProperty('--mx', mx.toFixed(3));
            coin.style.setProperty('--my', my.toFixed(3));
            if (!reduced) {
                // Pressing the right edge pushes it back (rotateY+), pressing the top pushes it back (rotateX+).
                coin.style.setProperty('--tilt-y', `${((mx - 0.5) * 2 * MAX_TILT).toFixed(2)}deg`);
                coin.style.setProperty('--tilt-x', `${((0.5 - my) * 2 * MAX_TILT).toFixed(2)}deg`);
            }
        };

        const track = (event) => {
            last = { x: event.clientX, y: event.clientY };
            if (!frame) frame = requestAnimationFrame(apply);
        };

        const press = (event) => {
            coin.classList.add('is-active');
            coin.style.setProperty('--lit', '1');
            track(event);
        };

        const release = () => {
            last = null;
            coin.classList.remove('is-active');
            coin.style.setProperty('--lit', '0');
            coin.style.setProperty('--tilt-x', '0deg');
            coin.style.setProperty('--tilt-y', '0deg');
        };

        coin.addEventListener('pointerenter', (event) => event.pointerType === 'mouse' && press(event));
        coin.addEventListener('pointerdown', press);
        coin.addEventListener('pointermove', (event) => {
            if (event.pointerType === 'mouse' || coin.classList.contains('is-active')) track(event);
        });
        coin.addEventListener('pointerleave', release);
        coin.addEventListener('pointercancel', release);
        coin.addEventListener('pointerup', (event) => event.pointerType !== 'mouse' && release());
    });
}
