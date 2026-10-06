# Mosaic FX — Official Web Page

A one-page site for the Mosaic FX trading community, built with Laravel 12 and styled to the **Mosaic FX Design System**. The sections, in order:

- **Plate 01, the cover.** The coin, the MOSAIC FX wordmark and the locked tagline, with loose stained-glass panes floating around them. Below the button, the brand's leaded texture rises out of the dark. Now and then a pane warms to Bright Gold.
- **Plate 02, what lands in the group.** One real setup chart you shared, shown large in a gold-leaded frame with its pair, timeframe, date and outcome, a risk line, three short points and the Telegram button. It appears once a chart is in `public/images/setup/`.
- **Plate 03, who we are.** Text on the left; on the right, a glass pane over the texture linking to the full trade record, with member screenshots above it once real ones are added.
- **Plate 04, how we teach.** The numbers on their own band, the four steps, then Mosaic Academy's three stages (basics, MosaicFX strategies, the apps) joined by gold leading, with the way in through the Telegram group and the Exness affiliate disclosure.
- **Plate 05, the closing frame.** The coin lockup and the one ask: "Join our Telegram Group".

## Run it locally

Requirements: PHP 8.2+, Composer, Node 20+.

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build
php artisan serve
```

Then open http://127.0.0.1:8000. While you're editing CSS or JS, run `npm run dev` alongside `php artisan serve`.

## The design system

- **Tokens.** `resources/css/tokens/*.css` are copied verbatim from the design system's `tokens/`. That covers the ten colours, the type scale, spacing, borders, glass, glows and motion. Change values there, not in `app.css`.
- **Fonts.** Clash Display (Fontshare), Fraunces Italic and IBM Plex Mono (Google Fonts) load from their official foundries, exactly as the design system does. The `<link>` tags are in `resources/views/layouts/app.blade.php`. To self-host instead, add the `.woff2` files and swap the links for `@font-face` rules.
- **The texture.** `resources/views/components/mosaic-texture.blade.php` is a straight port of `MosaicTexture`, with the pane coordinates lifted verbatim. The animated glass (`resources/js/glass/`) reads its panes from that markup, so the static and animated versions always match.
- **The mark.** `public/images/brand/` holds web-sized copies of the supplied coin artwork: scaled only, never redrawn. The favicons are made from it too.
- **Icons.** These are the design system's flagged Lucide substitution, inline in `resources/views/components/icon.blade.php`, and are used only for the paper plane, close and arrows.

## Where to change things

| What | Where |
|---|---|
| **Telegram link** | `.env` → `MOSAIC_TELEGRAM_URL=https://t.me/...` (until it's set, the buttons show a "link coming soon" notice) |
| Wordmark and tagline | `config/mosaic.php` (the tagline is the design system's locked line; don't reword it) |
| **Setup chart** | `public/images/setup/`: one real chart image from a setup you shared, 1600px wide or more, with entry, stop and target marked. Its pair, timeframe, date and outcome go in `config/mosaic.php` → `setup`. Until an image is there, production pages leave the section out. Never use a mocked-up chart. |
| **Trade record** | `.env` → `MOSAIC_TRADE_RECORD_URL`, plus `MOSAIC_TRADE_RECORD_FROM` (e.g. `"March 2024"`) and, once it's closed, `MOSAIC_TRADE_RECORD_TO`. Until the link is set, production pages leave the proof panel out; local pages show a reminder. |
| **Member screenshots** | `public/images/testimonials/`: add real, permitted screenshots (jpg, png, webp…), named `01-…`, `02-…` for order. The first four show above the trade record; if there are more, one frame at a time fades to the next. Files named `placeholder-*` are always skipped. |
| Page text | `resources/views/home.blade.php` |
| Site-level styles | `resources/css/app.css` |
| Loose pane positions | `resources/js/glass/layouts.js` |
| How the glass is lit | `resources/js/glass/shaders.js` |
| How often panes warm up | `scheduleGlints()` in `resources/js/glass/GlassScene.js` |
| Link preview image (Telegram, WhatsApp, X…) | `public/images/og.jpg` (1200×630) |

After changing CSS or JS, run `npm run build` again.

## Handy extras

- Add `?still` to the URL (e.g. `http://127.0.0.1:8000/?still`) to get a settled, motionless frame with a couple of panes caught while warm. It's useful for screenshots and for remaking `og.jpg`.
- Visitors with "reduce motion" switched on get that still frame, and the member frames don't change.
- If a browser can't run WebGL, the static texture shows instead.
- The animation pauses off screen and in hidden tabs, and lowers its resolution on slow devices.

## Static export (Cloudflare Pages)

The site can be exported as plain files: `dist/index.html`, plus everything in `public/` (built JS and CSS, images, favicons). Fonts load from their foundries' CDNs, and the glass runs entirely in the browser, so nothing needs a server.

```bash
npm run build
php artisan site:export --url=https://mosaicfx.pages.dev
```

- `--url` is the address the site will live at. It's used for the canonical link, the link-preview image and asset URLs. It defaults to `APP_URL`.
- `MOSAIC_TELEGRAM_URL` is read when you export and baked into the HTML. If it's unset, the buttons show "Our Telegram link is coming soon." when the export runs with `APP_ENV=production`, or the reminder to set the link when it's `local`.
- `MOSAIC_TRADE_RECORD_URL`, `_FROM` and `_TO` are baked in the same way.
- Re-export after changing the Telegram link, trade record, testimonial images or copy. The footer year is also set at export time.
- `npm run build:static` does both steps, using `APP_URL` as the address.
- `.github/workflows/deploy-cloudflare-pages.yml` does all of this on every push to `main` and uploads `dist/` to Cloudflare Pages. The setup steps are in the next section.

## Deploying to Cloudflare Pages from GitHub

One-time setup:

1. In Cloudflare, go to **My Profile → API Tokens → Create Token**. Use the **Edit Cloudflare Workers** template, or a custom token with **Account → Cloudflare Pages → Edit**. Copy the token.
2. Copy your **Account ID** from the right-hand sidebar of the Cloudflare dashboard (Workers & Pages overview).
3. Create the Pages project once, from your computer:
   ```bash
   npx wrangler login
   npx wrangler pages project create mosaicfx --production-branch=main
   ```
4. In the GitHub repo, go to **Settings → Secrets and variables → Actions**:
   - **Secrets:** `CLOUDFLARE_API_TOKEN` and `CLOUDFLARE_ACCOUNT_ID`.
   - **Variables:** `CLOUDFLARE_PROJECT_NAME` = `mosaicfx`, `MOSAIC_TELEGRAM_URL` = your invite link, and `MOSAIC_TRADE_RECORD_URL` / `MOSAIC_TRADE_RECORD_FROM` (and `MOSAIC_TRADE_RECORD_TO` if the record is closed). Optionally add `SITE_URL` once you use a custom domain; without it, the site URL is `https://<project>.pages.dev`.

After that, every push to `main` builds and deploys. You can also run it by hand from **Actions → Deploy to Cloudflare Pages → Run workflow**.

To deploy from your own machine instead:

```bash
npm run build
php artisan site:export --url=https://mosaicfx.pages.dev
npx wrangler pages deploy dist --project-name=mosaicfx --branch=main
```

## Deploying to a PHP host

1. Build the assets (`npm run build`). `public/build` is git-ignored, so either build on the server or upload that folder.
2. Point the web server's document root at `public/`.
3. In the server's `.env`, set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-domain` and `MOSAIC_TELEGRAM_URL`.
4. Run `php artisan config:cache && php artisan route:cache && php artisan view:cache`. After any later `.env` change, run `php artisan config:cache` again.

No database is needed. Sessions and cache use files.

## Tests

```bash
php artisan test
```
