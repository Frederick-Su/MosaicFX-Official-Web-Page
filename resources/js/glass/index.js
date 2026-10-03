import { GlassScene } from './GlassScene.js';
import { readPanes } from './geometry.js';
import { heroScene, joinScene } from './layouts.js';

const SCENES = { hero: heroScene, join: joinScene };

/** Position of an element inside a container, ignoring CSS transforms. */
function layoutRect(el, container) {
    let x = 0;
    let y = 0;
    let node = el;
    while (node && node !== container) {
        x += node.offsetLeft;
        y += node.offsetTop;
        node = node.offsetParent;
    }
    if (node !== container) {
        const a = el.getBoundingClientRect();
        const b = container.getBoundingClientRect();
        return { x: a.left - b.left, y: a.top - b.top, w: a.width, h: a.height };
    }
    return { x, y, w: el.offsetWidth, h: el.offsetHeight };
}

function union(rects) {
    if (!rects.length) return null;
    const x0 = Math.min(...rects.map((r) => r.x));
    const y0 = Math.min(...rects.map((r) => r.y));
    const x1 = Math.max(...rects.map((r) => r.x + r.w));
    const y1 = Math.max(...rects.map((r) => r.y + r.h));
    return { x: x0, y: y0, w: x1 - x0, h: y1 - y0 };
}

export function initGlass() {
    const canvases = document.querySelectorAll('canvas[data-glass]');
    if (!canvases.length) return;

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    // ?still renders one settled frame (handy for screenshots); ?still=12 picks the moment.
    const params = new URLSearchParams(window.location.search);
    const stillAt = params.has('still') ? Number(params.get('still')) || 8 : null;

    canvases.forEach((canvas) => {
        const scene = SCENES[canvas.dataset.glass];
        const section = canvas.parentElement;
        if (!scene || !section) return;

        const glass = new GlassScene(canvas, scene, {
            reducedMotion,
            stillAt,
            // The panes come from the section's own static texture, so the two always match.
            panes: readPanes(section.querySelector('.mosaic-texture')),
            zones: () => {
                const keepOut = [...section.querySelectorAll('[data-glass-keepout]')].map((el) => layoutRect(el, section));
                const floor = section.querySelector('[data-glass-floor]');
                return {
                    keepOut,
                    focus: union(keepOut),
                    floorTop: floor ? layoutRect(floor, section).y + floor.offsetHeight + 28 : undefined,
                };
            },
        });

        if (glass.init()) {
            section.classList.add('has-glass');
            // Fonts change the size of the headline, so lay the glass out again once they're in.
            document.fonts?.ready.then(() => {
                glass.build();
                if (!glass.running) glass.renderStatic();
            });
        }
    });
}
