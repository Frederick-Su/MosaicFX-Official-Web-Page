// GLSL for the stained-glass scene. Everything is in CSS pixels with y pointing down, so the
// numbers line up with what you measure in the browser. Colours are the design system's
// locked swatches; the gloss and backlight overlays reproduce the MosaicTexture gradients.

export const MAX_VERTS = 10;

const PRECISION = /* glsl */ `
#ifdef GL_FRAGMENT_PRECISION_HIGH
precision highp float;
#else
precision mediump float;
#endif
`;

const COMMON = /* glsl */ `
uniform vec2 uView;        // canvas size, CSS px
uniform float uTime;       // seconds
uniform vec2 uLight;       // the one warm backlight, upper left (CSS px)

const vec3 INDIGO = vec3(0.10588, 0.06275, 0.18824);   // #1B1030 Void Indigo
const vec3 GOLD = vec3(0.84706, 0.69412, 0.36078);     // #D8B15C Champagne Gold
const vec3 BRIGHT = vec3(0.94902, 0.78431, 0.47451);   // #F2C879 Bright Gold
const vec3 OFFWHITE = vec3(0.96078, 0.94118, 0.90196); // #F5F0E6 Off-white

float hash12(vec2 p) {
    vec3 p3 = fract(vec3(p.xyx) * 0.1031);
    p3 += dot(p3, p3.yzx + 33.33);
    return fract((p3.x + p3.y) * p3.z);
}
`;

export const BG_VERT = /* glsl */ `
attribute vec2 aPos;
uniform vec2 uView;
varying vec2 vCss;

void main() {
    vCss = vec2((aPos.x * 0.5 + 0.5) * uView.x, (0.5 - aPos.y * 0.5) * uView.y);
    gl_Position = vec4(aPos, 0.0, 1.0);
}
`;

export const BG_FRAG = /* glsl */ `
${PRECISION}
${COMMON}
uniform float uBacklight;  // strength of the warm light on the indigo ground
varying vec2 vCss;

void main() {
    vec2 l = (vCss - uLight) / uView.y;
    vec3 col = mix(INDIGO, BRIGHT, uBacklight * exp(-dot(l, l) * 2.2));

    // Dither so the dark ground never bands.
    col += (hash12(gl_FragCoord.xy + fract(uTime) * 97.0) - 0.5) * (1.5 / 255.0);
    gl_FragColor = vec4(col, 1.0);
}
`;

export const PIECE_VERT = /* glsl */ `
attribute vec2 aCorner;
uniform vec2 uView;
uniform vec4 uRect;        // x, y, w, h in CSS px
varying vec2 vCss;

void main() {
    vec2 css = uRect.xy + aCorner * uRect.zw;
    vCss = css;
    gl_Position = vec4(css.x / uView.x * 2.0 - 1.0, 1.0 - css.y / uView.y * 2.0, 0.0, 1.0);
}
`;

// One shader draws every pane: the fitted texture panes and the loose ones floating free.
export const PIECE_FRAG = /* glsl */ `
${PRECISION}
#define MAXV ${MAX_VERTS}
${COMMON}
varying vec2 vCss;

uniform vec4 uRect;          // the quad being drawn (x, y, w, h)
uniform vec2 uV[MAXV + 1];   // polygon, closed and padded with the first vertex
uniform float uLeadOn[MAXV]; // 1.0 where an edge carries gold leading
uniform vec2 uCenter;
uniform float uRadius;
uniform vec3 uColor;         // the pane's colour, straight from the texture
uniform vec4 uFrame;         // the box the gloss and backlight are laid over (x, y, w, h)
uniform vec4 uP;             // x: depth (0 near, 1 far), y: opacity, z: glint start, w: kind (0 loose, 1 fitted)
uniform float uLead;         // came width: the whole width for a loose pane, half a seam for a fitted one
uniform vec2 uSweep;         // direction the warm light passes across
uniform float uGlintDur;
uniform vec4 uFade;          // fitted panes: x fade start y, y fully visible y, z bottom fade start y, w intensity
uniform vec2 uFadeCurve;     // x: centre of the curve, y: how far the fade line rises at the edges (px)

// Signed distance to the polygon (negative inside), plus the distance to the nearest leaded edge.
float sdPoly(vec2 p, out float dLead) {
    float d = 1e10;
    float dl = 1e10;
    float s = 1.0;
    for (int i = 0; i < MAXV; i++) {
        vec2 a = uV[i];
        vec2 b = uV[i + 1];
        vec2 e = b - a;
        vec2 w = p - a;
        float ee = dot(e, e);
        if (ee > 1e-4) {
            vec2 bq = w - e * clamp(dot(w, e) / ee, 0.0, 1.0);
            float dd = dot(bq, bq);
            d = min(d, dd);
            if (uLeadOn[i] > 0.5) dl = min(dl, dd);
        }
        bvec3 c = bvec3(p.y >= a.y, p.y < b.y, e.x * w.y > e.y * w.x);
        if (all(c) || all(not(c))) s = -s;
    }
    dLead = sqrt(dl);
    return s * sqrt(d);
}

void main() {
    vec2 p = vCss;
    float dLead;
    float d = sdPoly(p, dLead);
    float depth = uP.x;
    float fitted = step(0.5, uP.w);
    float loose = 1.0 - fitted;

    // A glint is the light warming behind the glass: it comes up, holds, and cools.
    float gt = (uTime - uP.z) / uGlintDur;
    float gOn = step(0.0, gt) * step(gt, 1.0);
    float warm = gOn * smoothstep(0.0, 0.33, gt) * (1.0 - smoothstep(0.55, 1.0, gt));

    // Loose panes wear their lead came all round, so they reach half a came past the edge.
    float aa = 0.7 + depth * 1.8;
    float reach = loose * uLead * 0.5;
    float cov = smoothstep(reach + aa, reach - aa, d);

    // Fitted panes come out of the dark seams first: the gold leading appears, then the glass
    // below it lights up — panes in shadow, never a coloured haze.
    float fill = 1.0;
    float seam = 1.0;
    if (fitted > 0.5) {
        float bend = (p.x - uFadeCurve.x) / (uView.x * 0.5);
        bend *= bend * uFadeCurve.y;
        float top = uFade.x - bend;
        float full = uFade.y - bend * 0.6;
        float bottom = 1.0 - smoothstep(uFade.z, uView.y, p.y);
        fill = smoothstep(top + (full - top) * 0.25, full, p.y);
        fill *= fill * bottom;
        seam = smoothstep(top - 40.0, top + (full - top) * 0.35, p.y) * bottom;
    }

    vec3 col = vec3(0.0);
    float alpha = 0.0;

    if (cov > 0.002 && seam > 0.002) {
        vec3 glass = uColor;

        // Warming: the light comes up behind the pane, strongest at its upper-left corner…
        vec2 local = (p - uCenter) / max(uRadius, 1.0);
        float lit = 1.0 - smoothstep(0.0, 1.7, length(local - vec2(-0.45, -0.5)));
        glass = mix(glass, BRIGHT, warm * (0.1 + 0.2 * lit));
        // …and a soft band of light passes across the glass.
        float sw = dot(local, uSweep);
        float pos = mix(-1.4, 1.4, clamp(gt, 0.0, 1.0));
        glass = mix(glass, BRIGHT, warm * 0.26 * exp(-pow((sw - pos) / 0.3, 2.0)));

        // The texture's two overlays: gloss on the upper-left faces, then one warm backlight.
        // Loose panes take them more lightly so each stays a flat, jewel-toned piece.
        float overlay = mix(0.6, 1.0, fitted);
        vec2 f = (p - uFrame.xy) / uFrame.zw;
        float tg = clamp(dot(f, vec2(0.7, 1.0)) / 1.49, 0.0, 1.0);
        float tk = (tg - 0.38) / 0.62;
        vec3 gC = tg < 0.38 ? OFFWHITE : mix(OFFWHITE, INDIGO, tk);
        float gA = tg < 0.38 ? mix(0.30, 0.05, tg / 0.38) : mix(0.05, 0.28, tk);
        float sb = length((f - vec2(0.42, 0.34)) / 0.72);
        float bk = clamp((sb - 0.46) / 0.54, 0.0, 1.0);
        vec3 bC = sb < 0.46 ? BRIGHT : mix(BRIGHT, INDIGO, bk);
        float bA = (sb < 0.46 ? mix(0.42, 0.12, sb / 0.46) : mix(0.12, 0.55, bk)) * (1.0 - warm * 0.5);

        glass = mix(mix(glass, gC, gA * overlay), bC, bA * overlay);

        // Gold leading — inside the seams of fitted panes, all the way round a loose one.
        float leadDist = mix(abs(d), dLead, fitted);
        float leadHalf = mix(uLead * 0.5, uLead, fitted);
        float lead = smoothstep(leadHalf + 0.6, leadHalf - 0.6, leadDist) * 0.92;
        vec3 leadCol = mix(GOLD, BRIGHT, warm);
        leadCol = mix(mix(leadCol, gC, gA * overlay * 0.5), bC, bA * overlay * 0.5);

        // Far loose panes sit back in the dark.
        glass = mix(glass, INDIGO, depth * 0.5);
        leadCol = mix(leadCol, INDIGO, depth * 0.45);

        // Leading over glass ("over" in premultiplied terms), each with its own fade.
        float strength = cov * uP.y * mix(0.94 - depth * 0.2, uFade.w, fitted);
        float glassA = fill;
        float leadA = lead * seam;
        col = (leadCol * leadA + glass * glassA * (1.0 - leadA)) * strength;
        alpha = (leadA + glassA * (1.0 - leadA)) * strength;
    }

    // Gold glow, never a grey shadow: faint round a loose pane, blooming while it warms.
    float od = max(d - reach, 0.0);
    float halo = loose * ((1.0 - cov) * 0.05 * exp(-od / 9.0) + warm * warm * 0.24 * exp(-od / (8.0 + 12.0 * warm)));

    // A warming seam in the fitted glass spills a little light either side.
    float spill = fitted * warm * 0.14 * exp(-dLead / 6.0) * seam * uFade.w;

    // Glows fade out before the edge of the quad, so no box ever shows.
    vec2 edge = min(p - uRect.xy, uRect.xy + uRect.zw - p);
    float inQuad = smoothstep(0.0, 12.0, min(edge.x, edge.y));
    col += BRIGHT * (halo * (1.0 - depth * 0.6) + spill) * uP.y * inQuad;

    gl_FragColor = vec4(col, alpha);
}
`;
