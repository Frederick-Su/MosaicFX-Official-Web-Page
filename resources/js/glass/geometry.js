// Small geometry helpers for the stained-glass scene.

/** Seeded random numbers, so the scene moves the same way on every visit. */
export function rng(seed) {
    let a = seed >>> 0;
    return () => {
        a = (a + 0x6d2b79f5) | 0;
        let t = Math.imul(a ^ (a >>> 15), 1 | a);
        t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
        return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
}

export const lerp = (a, b, t) => a + (b - a) * t;
export const clamp = (v, min, max) => Math.min(max, Math.max(min, v));

/** Area-weighted centroid. */
export function centroid(poly) {
    let a = 0;
    let cx = 0;
    let cy = 0;
    for (let i = 0; i < poly.length; i++) {
        const [x0, y0] = poly[i];
        const [x1, y1] = poly[(i + 1) % poly.length];
        const f = x0 * y1 - x1 * y0;
        a += f;
        cx += (x0 + x1) * f;
        cy += (y0 + y1) * f;
    }
    if (Math.abs(a) < 1e-6) {
        const n = poly.length;
        return [poly.reduce((s, p) => s + p[0], 0) / n, poly.reduce((s, p) => s + p[1], 0) / n];
    }
    return [cx / (3 * a), cy / (3 * a)];
}

/** Hex colour (#RRGGBB) to 0–1 floats. */
export function hexToRgb(hex) {
    const n = parseInt(hex.replace('#', ''), 16);
    return [((n >> 16) & 255) / 255, ((n >> 8) & 255) / 255, (n & 255) / 255];
}

/**
 * The panes of the brand texture, read straight from a rendered <x-mosaic-texture> SVG,
 * so the animated glass and the static texture can never drift apart.
 */
export function readPanes(svg) {
    if (!svg) return [];
    return [...svg.querySelectorAll('[data-panes] polygon')].map((polygon) => ({
        points: polygon
            .getAttribute('points')
            .trim()
            .split(/\s+/)
            .map((pair) => pair.split(',').map(Number)),
        color: hexToRgb(polygon.getAttribute('fill')),
    }));
}
