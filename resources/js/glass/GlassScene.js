import { BG_FRAG, BG_VERT, MAX_VERTS, PIECE_FRAG, PIECE_VERT } from './shaders.js';
import { centroid, clamp, lerp, rng } from './geometry.js';

const SHARD_GLINT = 1.9; // seconds a loose pane takes to warm up and cool again
const PANE_GLINT = 2.2;
const PLATE = 400; // the brand texture is drawn on a 400 × 400 plate
const INTRO = 0.9; // a backlight coming up: a calm fade, never a slide

const BG_UNIFORMS = ['uView', 'uTime', 'uLight', 'uBacklight'];
const PIECE_UNIFORMS = [
    'uView', 'uTime', 'uLight', 'uRect', 'uV', 'uLeadOn', 'uCenter', 'uRadius', 'uColor',
    'uFrame', 'uP', 'uLead', 'uSweep', 'uGlintDur', 'uFade', 'uFadeCurve',
];

const easeOut = (t) => 1 - Math.pow(1 - t, 3);
const smoothstep = (a, b, x) => {
    const t = clamp((x - a) / (b - a), 0, 1);
    return t * t * (3 - 2 * t);
};

function compileShader(gl, type, source) {
    const shader = gl.createShader(type);
    gl.shaderSource(shader, source);
    gl.compileShader(shader);
    if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS) && !gl.isContextLost()) {
        console.warn('[glass] shader error:', gl.getShaderInfoLog(shader));
        gl.deleteShader(shader);
        return null;
    }
    return shader;
}

function createProgram(gl, vertexSource, fragmentSource, uniformNames, attribute) {
    const vs = compileShader(gl, gl.VERTEX_SHADER, vertexSource);
    const fs = compileShader(gl, gl.FRAGMENT_SHADER, fragmentSource);
    if (!vs || !fs) return null;

    const program = gl.createProgram();
    gl.attachShader(program, vs);
    gl.attachShader(program, fs);
    gl.linkProgram(program);
    if (!gl.getProgramParameter(program, gl.LINK_STATUS) && !gl.isContextLost()) {
        console.warn('[glass] link error:', gl.getProgramInfoLog(program));
        return null;
    }

    const uniforms = {};
    uniformNames.forEach((name) => (uniforms[name] = gl.getUniformLocation(program, name)));
    return { program, uniforms, attribute: gl.getAttribLocation(program, attribute) };
}

/** Smallest nudge that moves a box clear of every rectangle (the headline, the button…). */
function nudgeClear(box, rects, margin) {
    let dx = 0;
    let dy = 0;
    for (let pass = 0; pass < 4; pass++) {
        let moved = false;
        for (const r of rects) {
            const l = r.x - margin;
            const rt = r.x + r.w + margin;
            const t = r.y - margin;
            const b = r.y + r.h + margin;
            const x0 = box.x0 + dx;
            const x1 = box.x1 + dx;
            const y0 = box.y0 + dy;
            const y1 = box.y1 + dy;
            if (x1 <= l || x0 >= rt || y1 <= t || y0 >= b) continue;
            const moves = [[l - x1, 0], [rt - x0, 0], [0, t - y1], [0, b - y0]];
            moves.sort((m, n) => Math.abs(m[0] + m[1]) - Math.abs(n[0] + n[1]));
            dx += moves[0][0];
            dy += moves[0][1];
            moved = true;
        }
        if (!moved) break;
    }
    return [dx, dy];
}

export class GlassScene {
    /**
     * @param {HTMLCanvasElement} canvas
     * @param {object} scene    layout description (see layouts.js)
     * @param {object} options  { panes, zones: () => ({ keepOut, focus, floorTop }), reducedMotion, stillAt, maxDpr }
     */
    constructor(canvas, scene, options = {}) {
        this.canvas = canvas;
        this.scene = scene;
        this.options = { maxDpr: 1.75, reducedMotion: false, stillAt: null, panes: [], zones: () => ({}), ...options };

        // A still frame (reduced motion, or ?still for screenshots) skips the animation loop.
        this.still = this.options.reducedMotion || this.options.stillAt !== null;
        this.view = { w: 0, h: 0 };
        this.quality = 1;
        this.time = 0;
        this.lastNow = null;
        this.introStart = this.still ? -100 : scene.introOnVisible ? null : 0;
        this.running = false;
        this.inView = false;
        this.lost = false;

        this.shards = [];
        this.shardOrder = [];
        this.tiles = [];
        this.zones = {};
        this.fade = [0, 1, 1e5, 1];
        this.fadeCurve = [0, 0];

        this.pointer = { x: 0, y: 0, at: -100 };
        this.light = [0, 0];
        this.parallax = [0, 0];
        this.scroll = 0;
        this.nextShardGlint = 2.4;
        this.nextTileGlint = 1.6;
        this.perf = { frames: 0, time: 0 };

        this.loop = this.loop.bind(this);
        this.updateRunning = this.updateRunning.bind(this);
        this.onPointerMove = this.onPointerMove.bind(this);
        this.onContextLost = this.onContextLost.bind(this);
        this.onContextRestored = this.onContextRestored.bind(this);
    }

    /** Returns false when WebGL isn't available, so the page keeps its static texture. */
    init() {
        if (!this.options.panes.length) return false;

        const attributes = {
            alpha: false,
            antialias: false,
            depth: false,
            stencil: false,
            premultipliedAlpha: true,
            powerPreference: 'default',
        };
        const gl = this.canvas.getContext('webgl2', attributes) || this.canvas.getContext('webgl', attributes);
        if (!gl) return false;

        this.gl = gl;
        if (!this.setupGL()) return false;

        this.canvas.addEventListener('webglcontextlost', this.onContextLost);
        this.canvas.addEventListener('webglcontextrestored', this.onContextRestored);
        document.addEventListener('visibilitychange', this.updateRunning);
        window.addEventListener('pointermove', this.onPointerMove, { passive: true });

        this.resizeObserver = new ResizeObserver(() => this.resize());
        this.resizeObserver.observe(this.canvas);

        this.intersection = new IntersectionObserver(
            ([entry]) => {
                this.inView = entry.isIntersecting;
                if (entry.intersectionRatio >= 0.2 && this.introStart === null) this.introStart = this.time;
                this.updateRunning();
            },
            { threshold: [0, 0.2] },
        );
        this.intersection.observe(this.canvas);

        this.resize();
        return true;
    }

    setupGL() {
        const gl = this.gl;
        this.bg = createProgram(gl, BG_VERT, BG_FRAG, BG_UNIFORMS, 'aPos');
        this.piece = createProgram(gl, PIECE_VERT, PIECE_FRAG, PIECE_UNIFORMS, 'aCorner');
        if (!this.bg || !this.piece) return false;

        this.triangle = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, this.triangle);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 3, -1, -1, 3]), gl.STATIC_DRAW);

        this.quad = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, this.quad);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([0, 0, 1, 0, 0, 1, 1, 1]), gl.STATIC_DRAW);

        gl.disable(gl.DEPTH_TEST);
        gl.blendFunc(gl.ONE, gl.ONE_MINUS_SRC_ALPHA);
        return true;
    }

    // ---------------------------------------------------------------- layout

    resize() {
        const w = this.canvas.clientWidth;
        const h = this.canvas.clientHeight;
        if (!w || !h) return;

        const changed = w !== this.view.w || h !== this.view.h;
        this.view = { w, h };
        this.resizeBuffer();
        if (changed) this.build();
        if (!this.running) this.renderStatic();
    }

    resizeBuffer() {
        const scale = Math.min(window.devicePixelRatio || 1, this.options.maxDpr) * this.quality;
        const bw = Math.max(1, Math.round(this.view.w * scale));
        const bh = Math.max(1, Math.round(this.view.h * scale));
        if (this.canvas.width !== bw || this.canvas.height !== bh) {
            this.canvas.width = bw;
            this.canvas.height = bh;
        }
    }

    build() {
        const { scene, view } = this;
        this.zones = this.options.zones(this.canvas) || {};
        const focus = this.zones.focus;
        this.focus = focus ? [focus.x + focus.w / 2, focus.y + focus.h / 2] : [view.w / 2, view.h / 2];
        this.lightHome = [scene.light[0] * view.w, scene.light[1] * view.h];
        this.light = [...this.lightHome];
        this.shards = scene.shards ? this.buildShards(scene.shards(view, this.zones)) : [];
        this.shardOrder = [...this.shards].sort((a, b) => b.depth - a.depth);
        this.tiles = this.buildTiles(scene.texture(view, this.zones));
    }

    /** Loose panes: real panes of the texture, scaled, turned and set floating around the title. */
    buildShards(specs) {
        const rand = rng(this.scene.seed);
        const keepOut = this.zones.keepOut || [];
        const panes = this.options.panes;

        return specs.map((spec, i) => {
            const pane = panes[spec.pane % panes.length];
            const c = centroid(pane.points);
            const units = pane.points.map(([x, y]) => [x - c[0], y - c[1]]);
            const reach = Math.max(...units.map(([x, y]) => Math.hypot(x, y)));
            const k = spec.r / reach;
            const pts = units.map(([x, y]) => [x * k, y * k]);

            // Measure the pane at rest and slide it clear of the text.
            const cos = Math.cos(spec.rot);
            const sin = Math.sin(spec.rot);
            const box = { x0: Infinity, y0: Infinity, x1: -Infinity, y1: -Infinity };
            pts.forEach(([x, y]) => {
                const px = spec.x + x * cos - y * sin;
                const py = spec.y + x * sin + y * cos;
                box.x0 = Math.min(box.x0, px);
                box.x1 = Math.max(box.x1, px);
                box.y0 = Math.min(box.y0, py);
                box.y1 = Math.max(box.y1, py);
            });
            const [dx, dy] = nudgeClear(box, keepOut, 26);
            const sweepAngle = 0.62 + (rand() - 0.5) * 0.5;

            const leadOn = new Float32Array(MAX_VERTS);
            leadOn.fill(1, 0, pts.length);

            return {
                kind: 0,
                base: pts,
                x: spec.x + dx,
                y: spec.y + dy,
                rot: spec.rot,
                radius: spec.r,
                depth: spec.depth,
                color: pane.color,
                lead: clamp(2.2 * k, 2, 5), // the brand's 2.2 leading, at the pane's scale
                leadOn,
                floatAmp: lerp(3, 6, rand()) * (1 - spec.depth * 0.4),
                floatSpeed: lerp(0.15, 0.3, rand()),
                spin: lerp(0.08, 0.16, rand()),
                phase: rand() * Math.PI * 2,
                sweep: [Math.cos(sweepAngle), Math.sin(sweepAngle)],
                delay: 0.25 + i * 0.06,
                glintStart: -1e4,
                verts: new Float32Array((MAX_VERTS + 1) * 2),
                center: [0, 0],
                rect: [0, 0, 0, 0],
                frame: [0, 0, 1, 1],
                opacity: 0,
                onScreen: true,
            };
        });
    }

    /** The texture itself, fitted to cover the canvas exactly as the static SVG does (slice). */
    buildTiles(cfg) {
        const { w, h } = this.view;
        const scale = Math.max(w / PLATE, h / PLATE);
        const ox = (w - PLATE * scale) / 2;
        const oy = (h - PLATE * scale) / 2;
        const rise = cfg.rise || 0;
        const reach = Math.hypot(w, h);
        const onEdge = (a, b) => (a[0] === b[0] && (a[0] === 0 || a[0] === PLATE)) || (a[1] === b[1] && (a[1] === 0 || a[1] === PLATE));

        this.fade = [cfg.fadeStart, cfg.fadeFull, cfg.bottomFade, cfg.intensity];
        this.fadeCurve = [w / 2, rise];
        const frame = [ox, oy, PLATE * scale, PLATE * scale];
        const lead = clamp(1.1 * scale, 1.1, 3); // half of each seam; the neighbour draws the other half

        const tiles = [];
        this.options.panes.forEach((pane) => {
            const pts = pane.points.map(([x, y]) => [ox + x * scale, oy + y * scale]);
            const xs = pts.map((p) => p[0]);
            const ys = pts.map((p) => p[1]);
            const [x0, x1, y0, y1] = [Math.min(...xs), Math.max(...xs), Math.min(...ys), Math.max(...ys)];
            if (y1 < cfg.fadeStart - rise || y0 > h || x1 < 0 || x0 > w) return;

            // Seams between panes carry leading; the outer edge of the plate doesn't.
            const leadOn = new Float32Array(MAX_VERTS);
            pane.points.forEach((a, i) => {
                const b = pane.points[(i + 1) % pane.points.length];
                leadOn[i] = onEdge(a, b) ? 0 : 1;
            });

            const verts = new Float32Array((MAX_VERTS + 1) * 2);
            for (let i = 0; i <= MAX_VERTS; i++) {
                const [x, y] = pts[i < pts.length ? i : 0];
                verts[i * 2] = x;
                verts[i * 2 + 1] = y;
            }

            const c = centroid(pts);
            const bend = ((c[0] - w / 2) / (w / 2)) ** 2 * rise;
            const presence = smoothstep(cfg.fadeStart - bend, cfg.fadeFull - bend * 0.6, c[1]) ** 2
                * (1 - smoothstep(cfg.bottomFade, h, c[1]));
            const sweepAngle = 0.55 + ((tiles.length * 0.37) % 0.6) - 0.3;

            tiles.push({
                kind: 1,
                verts,
                leadOn,
                center: c,
                radius: Math.max(...pts.map(([x, y]) => Math.hypot(x - c[0], y - c[1]))),
                color: pane.color,
                lead,
                frame,
                depth: 0,
                presence,
                sweep: [Math.cos(sweepAngle), Math.sin(sweepAngle)],
                // The backlight comes up from where the light is.
                delay: 0.1 + (Math.hypot(c[0] - this.lightHome[0], c[1] - this.lightHome[1]) / reach) * 1.2,
                glintStart: -1e4,
                rect: [x0 - 8, y0 - 8, x1 - x0 + 16, y1 - y0 + 16],
                opacity: 0,
                onScreen: true,
            });
        });
        return tiles;
    }

    // ---------------------------------------------------------------- animation

    intro(piece) {
        if (this.introStart === null) return 0;
        return easeOut(clamp((this.time - this.introStart - piece.delay) / INTRO, 0, 1));
    }

    /** Place a loose pane's polygon (turned) into its vertex buffer and work out its boxes. */
    place(piece, cx, cy, angle, margin) {
        const cos = Math.cos(angle);
        const sin = Math.sin(angle);
        const v = piece.verts;
        let x0 = Infinity;
        let y0 = Infinity;
        let x1 = -Infinity;
        let y1 = -Infinity;
        const n = piece.base.length;
        for (let i = 0; i <= MAX_VERTS; i++) {
            const [bx, by] = piece.base[i < n ? i : 0];
            const x = cx + bx * cos - by * sin;
            const y = cy + bx * sin + by * cos;
            v[i * 2] = x;
            v[i * 2 + 1] = y;
            if (i < n) {
                x0 = Math.min(x0, x);
                x1 = Math.max(x1, x);
                y0 = Math.min(y0, y);
                y1 = Math.max(y1, y);
            }
        }
        piece.center[0] = cx;
        piece.center[1] = cy;
        piece.frame[0] = x0;
        piece.frame[1] = y0;
        piece.frame[2] = Math.max(x1 - x0, 1);
        piece.frame[3] = Math.max(y1 - y0, 1);
        piece.rect[0] = x0 - margin;
        piece.rect[1] = y0 - margin;
        piece.rect[2] = x1 - x0 + margin * 2;
        piece.rect[3] = y1 - y0 + margin * 2;
        piece.onScreen = x1 + margin > 0 && x0 - margin < this.view.w && y1 + margin > 0 && y0 - margin < this.view.h;
    }

    update(dt) {
        const t = this.time;
        const { w, h } = this.view;
        const ft = this.options.reducedMotion ? 0 : t;
        const k = 1 - Math.exp(-dt * 2.5);

        // The light stays upper left; the pointer only nudges it, and the panes, a little.
        const rect = this.canvas.getBoundingClientRect();
        const pointerLive = !this.still && t - this.pointer.at < 6;
        const px = pointerLive ? clamp((this.pointer.x - rect.left) / w - 0.5, -0.6, 0.6) : 0;
        const py = pointerLive ? clamp((this.pointer.y - rect.top) / h - 0.5, -0.6, 0.6) : 0;
        this.parallax[0] = lerp(this.parallax[0], px, this.still ? 1 : k);
        this.parallax[1] = lerp(this.parallax[1], py, this.still ? 1 : k);
        this.light[0] = this.lightHome[0] + this.parallax[0] * w * 0.06;
        this.light[1] = this.lightHome[1] + this.parallax[1] * h * 0.06;
        this.scroll = clamp(-rect.top / Math.max(rect.height, 1), 0, 1);

        for (const s of this.shards) {
            const e = this.intro(s);
            const near = 1 - s.depth;
            const cx = s.x + Math.cos(ft * s.floatSpeed * 0.7 + s.phase) * s.floatAmp * 0.6 + this.parallax[0] * 12 * near;
            const cy = s.y + Math.sin(ft * s.floatSpeed + s.phase) * s.floatAmp + this.parallax[1] * 9 * near;
            const angle = s.rot + Math.sin(ft * s.spin + s.phase) * 0.025;
            const glinting = t - s.glintStart < SHARD_GLINT;
            this.place(s, cx, cy, angle, s.lead + (glinting ? 96 : 24));
            s.opacity = e * (1 - this.scroll * 0.8);
        }

        for (const tile of this.tiles) tile.opacity = this.intro(tile);

        if (!this.still && this.introStart !== null) this.scheduleGlints(t);
    }

    scheduleGlints(t) {
        if (t >= this.nextShardGlint) {
            const ready = this.shards.filter((s) => s.depth < 0.6 && s.onScreen && s.opacity > 0.6 && t - s.glintStart > SHARD_GLINT + 2);
            if (ready.length) ready[Math.floor(Math.random() * ready.length)].glintStart = t;
            this.nextShardGlint = t + lerp(2.2, 4.5, Math.random());
        }

        if (t >= this.nextTileGlint) {
            const ready = this.tiles.filter((tile) => tile.presence > 0.35 && tile.opacity > 0.9 && t - tile.glintStart > PANE_GLINT + 1);
            if (ready.length) ready[Math.floor(Math.random() * ready.length)].glintStart = t;
            this.nextTileGlint = t + lerp(1.2, 2.6, Math.random());
        }
    }

    /** For ?still screenshots: catch a couple of panes while their light is up. */
    previewGlints() {
        const t = this.time;
        this.shards
            .filter((s) => s.depth < 0.3 && s.onScreen)
            .slice(0, 2)
            .forEach((s, i) => (s.glintStart = t - SHARD_GLINT * (i ? 0.62 : 0.45)));
        [...this.tiles]
            .filter((tile) => tile.presence > 0.5)
            .sort((a, b) => b.presence - a.presence)
            .filter((_, i) => i % 3 === 1)
            .slice(0, 2)
            .forEach((tile, i) => (tile.glintStart = t - PANE_GLINT * (0.45 + i * 0.1)));
    }

    // ---------------------------------------------------------------- drawing

    render() {
        const gl = this.gl;
        if (!gl || this.lost) return;
        const { w, h } = this.view;
        gl.viewport(0, 0, this.canvas.width, this.canvas.height);

        // The indigo ground with its one warm light.
        gl.disable(gl.BLEND);
        const bg = this.bg;
        gl.useProgram(bg.program);
        gl.uniform2f(bg.uniforms.uView, w, h);
        gl.uniform1f(bg.uniforms.uTime, this.time);
        gl.uniform2f(bg.uniforms.uLight, this.light[0], this.light[1]);
        gl.uniform1f(bg.uniforms.uBacklight, this.scene.backlight);
        gl.bindBuffer(gl.ARRAY_BUFFER, this.triangle);
        gl.enableVertexAttribArray(bg.attribute);
        gl.vertexAttribPointer(bg.attribute, 2, gl.FLOAT, false, 0, 0);
        gl.drawArrays(gl.TRIANGLES, 0, 3);

        // The glass: the fitted texture first, then loose panes from far to near.
        gl.enable(gl.BLEND);
        const { program, uniforms: u, attribute } = this.piece;
        gl.useProgram(program);
        gl.uniform2f(u.uView, w, h);
        gl.uniform1f(u.uTime, this.time);
        gl.uniform2f(u.uLight, this.light[0], this.light[1]);
        gl.uniform4f(u.uFade, this.fade[0], this.fade[1], this.fade[2], this.fade[3]);
        gl.uniform2f(u.uFadeCurve, this.fadeCurve[0], this.fadeCurve[1]);
        gl.bindBuffer(gl.ARRAY_BUFFER, this.quad);
        gl.enableVertexAttribArray(attribute);
        gl.vertexAttribPointer(attribute, 2, gl.FLOAT, false, 0, 0);

        const draw = (p, glintDuration) => {
            if (p.opacity < 0.004 || !p.onScreen) return;
            gl.uniform4f(u.uRect, p.rect[0], p.rect[1], p.rect[2], p.rect[3]);
            gl.uniform2fv(u.uV, p.verts);
            gl.uniform1fv(u.uLeadOn, p.leadOn);
            gl.uniform2f(u.uCenter, p.center[0], p.center[1]);
            gl.uniform1f(u.uRadius, p.radius);
            gl.uniform3fv(u.uColor, p.color);
            gl.uniform4f(u.uFrame, p.frame[0], p.frame[1], p.frame[2], p.frame[3]);
            gl.uniform4f(u.uP, p.depth, p.opacity, p.glintStart, p.kind);
            gl.uniform1f(u.uLead, p.lead);
            gl.uniform2f(u.uSweep, p.sweep[0], p.sweep[1]);
            gl.uniform1f(u.uGlintDur, glintDuration);
            gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
        };

        for (const tile of this.tiles) draw(tile, PANE_GLINT);
        for (const shard of this.shardOrder) draw(shard, SHARD_GLINT);
    }

    renderStatic() {
        if (!this.gl || this.lost || !this.view.w) return;
        if (this.still) this.time = this.options.stillAt ?? 30;
        this.update(0);
        if (this.options.stillAt !== null) {
            this.previewGlints();
            this.update(0);
        }
        this.render();
    }

    // ---------------------------------------------------------------- lifecycle

    loop(now) {
        if (!this.running) return;
        this.raf = requestAnimationFrame(this.loop);
        const dt = this.lastNow === null ? 1 / 60 : Math.min((now - this.lastNow) / 1000, 0.1);
        this.lastNow = now;
        this.time += dt;
        this.update(dt);
        this.render();
        this.trackPerformance(dt);
    }

    updateRunning() {
        const shouldRun = this.inView && !document.hidden && !this.still && !this.lost;
        if (shouldRun && !this.running) {
            this.running = true;
            this.lastNow = null;
            this.raf = requestAnimationFrame(this.loop);
        } else if (!shouldRun && this.running) {
            this.running = false;
            cancelAnimationFrame(this.raf);
        }
        if (!this.running && this.inView) this.renderStatic();
    }

    /** If frames are slow, render at a lower resolution. */
    trackPerformance(dt) {
        if (this.time < 2 || this.quality <= 0.55) return;
        this.perf.frames++;
        this.perf.time += dt;
        if (this.perf.time >= 2) {
            if (this.perf.time / this.perf.frames > 1 / 45) {
                this.quality = Math.max(0.55, this.quality * 0.8);
                this.resizeBuffer();
            }
            this.perf.frames = 0;
            this.perf.time = 0;
        }
    }

    onPointerMove(event) {
        if (event.pointerType === 'touch') return;
        this.pointer.x = event.clientX;
        this.pointer.y = event.clientY;
        this.pointer.at = this.time;
    }

    onContextLost(event) {
        event.preventDefault();
        this.lost = true;
        this.updateRunning();
    }

    onContextRestored() {
        this.lost = false;
        if (!this.setupGL()) return;
        this.resizeBuffer();
        this.updateRunning();
    }

    destroy() {
        this.running = false;
        cancelAnimationFrame(this.raf);
        this.resizeObserver?.disconnect();
        this.intersection?.disconnect();
        document.removeEventListener('visibilitychange', this.updateRunning);
        window.removeEventListener('pointermove', this.onPointerMove);
        this.canvas.removeEventListener('webglcontextlost', this.onContextLost);
        this.canvas.removeEventListener('webglcontextrestored', this.onContextRestored);
    }
}
