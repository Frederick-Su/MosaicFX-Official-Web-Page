// Where the glass goes. Loose panes are positioned as fractions of the canvas and sized in
// "units" (roughly 1% of the canvas). `pane` picks which pane of the brand texture each one
// is — the shapes and colours come straight from the texture, only scaled and turned.

// Landscape: follows the sketch — larger panes framing the title, small far ones for depth.
const LANDSCAPE = [
    { x: 0.12, y: 0.21, r: 6.0, rot: 0.35, depth: 0.0, pane: 1 },
    { x: 0.245, y: 0.11, r: 3.0, rot: -0.3, depth: 0.35, pane: 2 },
    { x: 0.355, y: 0.15, r: 4.4, rot: 0.08, depth: 0.22, pane: 9 },
    { x: 0.645, y: 0.13, r: 3.6, rot: -0.5, depth: 0.3, pane: 8 },
    { x: 0.87, y: 0.21, r: 6.2, rot: -0.4, depth: 0.0, pane: 6 },
    { x: 0.04, y: 0.47, r: 6.2, rot: 1.2, depth: 0.12, pane: 10 },
    { x: 0.165, y: 0.6, r: 4.8, rot: 0.15, depth: 0.0, pane: 12 },
    { x: 0.965, y: 0.44, r: 5.6, rot: 1.0, depth: 0.15, pane: 0 },
    { x: 0.82, y: 0.57, r: 6.4, rot: 0.1, depth: 0.0, pane: 14 },
    { x: 0.29, y: 0.29, r: 1.9, rot: 0.8, depth: 0.85, pane: 3 },
    { x: 0.71, y: 0.28, r: 2.3, rot: -0.9, depth: 0.7, pane: 16 },
    { x: 0.27, y: 0.78, r: 2.0, rot: 2.1, depth: 0.75, pane: 7 },
    { x: 0.74, y: 0.76, r: 1.8, rot: -1.7, depth: 0.9, pane: 13 },
    { x: 0.53, y: 0.085, r: 1.4, rot: 0.4, depth: 0.95, pane: 18 },
    { x: 0.93, y: 0.7, r: 2.2, rot: 0.9, depth: 0.8, pane: 11 },
];

// Portrait (phones): panes above and below the title instead of beside it.
const PORTRAIT = [
    { x: 0.13, y: 0.12, r: 7.5, rot: 0.4, depth: 0.0, pane: 1 },
    { x: 0.62, y: 0.09, r: 6.5, rot: 0.1, depth: 0.25, pane: 9 },
    { x: 0.97, y: 0.21, r: 7.0, rot: -0.5, depth: 0.0, pane: 6 },
    { x: 0.03, y: 0.3, r: 5.5, rot: 1.2, depth: 0.2, pane: 10 },
    { x: 0.42, y: 0.2, r: 2.6, rot: 0.7, depth: 0.8, pane: 3 },
    { x: 0.79, y: 0.31, r: 2.4, rot: -1.2, depth: 0.75, pane: 16 },
    { x: 0.06, y: 0.64, r: 5.0, rot: 0.25, depth: 0.1, pane: 12 },
    { x: 0.96, y: 0.62, r: 6.0, rot: 1.0, depth: 0.05, pane: 14 },
    { x: 0.3, y: 0.72, r: 2.0, rot: 2.0, depth: 0.85, pane: 7 },
    { x: 0.72, y: 0.73, r: 1.9, rot: -1.5, depth: 0.9, pane: 13 },
];

const isPortrait = (view) => view.w / view.h < 0.9;

export const heroScene = {
    seed: 7,
    backlight: 0.11, // the warm light on the indigo ground
    light: [0.16, 0.08], // where it comes from, as a fraction of the canvas: upper left
    shards(view) {
        const portrait = isPortrait(view);
        const unit = portrait ? Math.min(view.w * 1.8, view.h) / 100 : Math.min(view.w, view.h * 1.6) / 100;
        return (portrait ? PORTRAIT : LANDSCAPE).map((s) => ({
            ...s,
            x: s.x * view.w,
            y: s.y * view.h,
            r: s.r * unit,
        }));
    },
    texture(view, zones) {
        const portrait = isPortrait(view);
        const top = zones.floorTop ?? view.h * 0.74;
        // The fade line dips under the button and rises toward the sides, like the arcs in the sketch.
        const rise = Math.min(view.h * (portrait ? 0.07 : 0.16), top * 0.5);
        return {
            intensity: 0.85, // hero frames only
            fadeStart: top,
            fadeFull: top + (view.h - top) * 0.7,
            bottomFade: view.h * 0.9,
            rise,
        };
    },
};

export const joinScene = {
    seed: 23,
    introOnVisible: true,
    backlight: 0, // the texture carries its own backlight; the ground stays plain indigo so the section edges vanish
    light: [0.18, 0.1],
    shards: null,
    texture(view) {
        return {
            intensity: 0.5, // the default — reads as glass
            fadeStart: view.h * 0.1,
            fadeFull: view.h * 0.42,
            bottomFade: view.h * 0.7,
            rise: 0,
        };
    },
};
