/*
 * MosaicFX landing page behaviour (no dependencies).
 * 1. Testimony viewer: open from any panel or "See all", prev/next, Escape, arrow keys, focus kept inside.
 * 2. "Spot the setup" charts: drag the slider to reveal our marked plan; each chart nudges itself once
 *    when it scrolls into view so visitors notice it can be dragged (skipped for reduced motion).
 * Everything else (scroll reveals, glints, light sweeps, story rail) is pure CSS.
 */
(function () {
  'use strict';

  var CAPTIONS = ["Live open gold trades", "Gold · +2,029 in September", "US500 + USTEC · +700", "6,017 from one gold setup", "USTEC · 1,621 in one day", "Starting small · 0.01 lots", "Profit 41,507 · withdrew 42,044", "Profit 62,793 · withdrew 190,615", "Profit 18,408", "Profit 12,112 · all withdrawn", "Profit 12,083", "Profit 9,801", "Profit 8,062 · all withdrawn", "Two gold trades · +535"];

  /* ---------- 1. testimony viewer ---------- */
  var viewer = document.getElementById('viewer');
  var shots = viewer ? Array.prototype.slice.call(viewer.querySelectorAll('[data-shot]')) : [];
  var count = viewer ? viewer.querySelector('.vcount') : null;
  var current = -1;
  var returnFocus = null;
  var total = shots.length || CAPTIONS.length;

  function show(k) {
    if (!viewer || !total) return;
    k = ((k % total) + total) % total;
    if (current < 0) {
      returnFocus = document.activeElement;
      viewer.hidden = false;
      setTimeout(function () {
        var x = viewer.querySelector('.vdialog .x');
        if (x) x.focus();
      }, 30);
    }
    current = k;
    shots.forEach(function (el) {
      var on = Number(el.getAttribute('data-shot')) === k;
      el.hidden = !on;
      if (on) {
        // load this screenshot now, and warm up its neighbours so stepping through feels instant
        [k, k + 1, k - 1].forEach(function (n) {
          var s = shots[((n % total) + total) % total];
          var img = s && s.querySelector('img');
          if (img && img.loading === 'lazy') img.loading = 'eager';
        });
      }
    });
    if (count) count.textContent = (k + 1) + ' of ' + total + ' · ' + (CAPTIONS[k] || '');
  }

  function close() {
    if (!viewer || current < 0) return;
    viewer.hidden = true;
    current = -1;
    var r = returnFocus; returnFocus = null;
    setTimeout(function () { if (r && r.focus) r.focus(); }, 30);
  }

  document.addEventListener('click', function (e) {
    var opener = e.target.closest('[data-open]');
    if (opener) {
      e.preventDefault();
      show(Number(opener.getAttribute('data-open')));
      return;
    }
    var ctl = e.target.closest('[data-viewer]');
    if (ctl) {
      e.preventDefault();
      var a = ctl.getAttribute('data-viewer');
      if (a === 'close') close();
      else if (a === 'prev') show(current - 1);
      else if (a === 'next') show(current + 1);
    }
  });

  document.addEventListener('keydown', function (e) {
    if (current < 0) return;
    if (e.key === 'Escape') { e.preventDefault(); close(); }
    else if (e.key === 'ArrowLeft') { e.preventDefault(); show(current - 1); }
    else if (e.key === 'ArrowRight') { e.preventDefault(); show(current + 1); }
    else if (e.key === 'Tab') {
      var f = Array.prototype.slice.call(viewer.querySelectorAll('.vdialog button'));
      if (!f.length) return;
      var i = f.indexOf(document.activeElement);
      if (e.shiftKey && i <= 0) { e.preventDefault(); f[f.length - 1].focus(); }
      else if (!e.shiftKey && (i === -1 || i === f.length - 1)) { e.preventDefault(); f[0].focus(); }
    }
  });

  /* ---------- 2. reveal charts ---------- */
  var charts = Array.prototype.slice.call(document.querySelectorAll('.chart'));
  var revealed = charts.map(function () { return false; });

  function setPos(i, v, reveal) {
    var chart = charts[i];
    if (!chart) return;
    var val = Math.round(v * 100) / 100;
    chart.style.setProperty('--pos', val + '%');
    var input = chart.querySelector('input[type="range"]');
    if (input && document.activeElement !== input) input.value = val;
    if (reveal && !revealed[i]) {
      revealed[i] = true;
      var fig = chart.closest('.setup');
      if (fig) fig.classList.add('revealed');
    }
  }

  charts.forEach(function (chart, i) {
    var input = chart.querySelector('input[type="range"]');
    if (!input) return;
    input.addEventListener('input', function () { setPos(i, Number(input.value), true); });
  });

  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!reduce && 'IntersectionObserver' in window && charts.length) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        io.unobserve(entry.target);
        var i = charts.indexOf(entry.target);
        if (i < 0) return;
        var t0 = performance.now() + 400;
        var step = function (t) {
          if (revealed[i]) return;
          var k = Math.max(0, Math.min(1, (t - t0) / 1400));
          setPos(i, 100 - 24 * Math.sin(Math.PI * k), false);
          if (k < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
      });
    }, { threshold: 0.6 });
    charts.forEach(function (c) { io.observe(c); });
  }
})();
