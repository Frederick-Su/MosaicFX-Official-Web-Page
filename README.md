# MosaicFX — Official Landing Page

The MosaicFX landing page (design **v5 story**), built on Laravel. It's a single page with no database, no login and no build step:

- `resources/views/landing.blade.php` — the page markup
- `public/css/mosaicfx.css` — every style, animation and mosaic texture
- `public/js/mosaicfx.js` — the testimony viewer and the "Spot the setup" drag charts (no libraries)
- `public/images/` — hero window, gold dome, letter, mosaic textures and the 14 testimony screenshots
- `routes/web.php` + `app/Http/Controllers/LandingController.php` — serves the page at `/`
- `public/index.html` — the same page saved as static HTML for Cloudflare (made by `php artisan site:export`)

Fonts (Fraunces and IBM Plex Mono) load from Google Fonts. All buttons link to the Telegram group `https://t.me/MosaicFX`, and the Instagram links go to `https://www.instagram.com/mosaic_fx/`.

---

## 1. What you need

| Tool | Version | Check with |
|---|---|---|
| PHP | 8.3 or newer | `php -v` |
| Composer | 2.x | `composer -V` |

Node/npm is **not** needed.

PHP extensions Laravel needs (usually already on): `ctype`, `fileinfo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `curl`. A database extension isn't needed.

---

## 2. Run it on your computer

Open a terminal in the unzipped folder, then:

```bash
# 1. Install Laravel and its packages (creates the vendor/ folder)
composer install

# 2. Create your local settings file from the template
cp .env.example .env          # Windows PowerShell: copy .env.example .env

# 3. Generate the app key (written into .env, never committed)
php artisan key:generate

# 4. Start the site
php artisan serve
```

Open **http://127.0.0.1:8000** in your browser.

Optional check: `php artisan test` should show 3 passing tests.

---

## 3. Push it to GitHub

The repo is `https://github.com/Frederick-Su/MosaicFX-Official-Web-Page`.

```bash
git init
git add .
git commit -m "MosaicFX landing page (v5 story)"
git branch -M main
git remote add origin https://github.com/Frederick-Su/MosaicFX-Official-Web-Page.git
git push -u origin main
```

`.env` and `vendor/` are already in `.gitignore`, so no secrets or installed packages get pushed. If the repo already has commits (for example a README made on GitHub), run `git pull origin main --allow-unrelated-histories` before pushing.

---

## 4. Put it live on Cloudflare Workers (recommended, free)

Cloudflare Workers can't run PHP, so the page is also saved as plain HTML in `public/index.html`. Cloudflare serves the `public/` folder exactly as it is, set up by `wrangler.jsonc`.

**In the Cloudflare "Set up your application" screen:**

| Field | Value |
|---|---|
| Project name | `mosaicfx-official-web-page` (must match `name` in `wrangler.jsonc`) |
| Build command | leave empty |
| Deploy command | `npx wrangler deploy` |
| Non-production branch deploy command | `npx wrangler versions upload` (the default) |
| Path | `/` (leave as is) |
| Variables | none needed |

Every push to `main` then redeploys the site on its own.

**After every page edit** (view, CSS or JS), refresh the static copy before you push:

```bash
php artisan site:export --url=https://your-domain.com
git add . && git commit -m "Update page" && git push
```

`--url` is the address the site lives on (your `*.workers.dev` link or your own domain). It makes the share preview on Telegram, WhatsApp and Instagram show the picture. Without it the page still works everywhere.

Files Cloudflare uses: `wrangler.jsonc`, `public/index.html`, `public/_headers` (cache rules) and `public/.assetsignore` (keeps `index.php` and `.htaccess` off the live site).

---

## 5. Or put it live on a PHP host

1. Upload the project, or `git clone` it on the server.
2. On the server run:
   ```bash
   composer install --no-dev --optimize-autoloader
   cp .env.example .env
   php artisan key:generate
   ```
3. Edit `.env` on the server: set `APP_ENV=production`, `APP_DEBUG=false` and `APP_URL=https://your-domain.com`.
4. Speed it up:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
5. Point the domain's **document root to the `public/` folder** (not the project root).
6. Make sure the web server can write to `storage/` and `bootstrap/cache/`.

No database, queue worker or cron job is needed.

---

## 6. Editing the page

| To change… | Edit |
|---|---|
| Text, links, sections | `resources/views/landing.blade.php` |
| Colours, spacing, animations | `public/css/mosaicfx.css` |
| Testimony captions in the viewer | the `CAPTIONS` list at the top of `public/js/mosaicfx.js` |
| A testimony screenshot | replace the file in `public/images/testimonials/` (keep the same name) |

Testimony order: `testimonial-01` to `testimonial-06` are the six panels in chapter IV. All 14 appear in the full-screen viewer, in number order.

Two Blade rules for the view file:
- Write `@@` to show a single `@` (for example `@@mosaic_fx`).
- Image paths use `{{ asset('images/...') }}`.

The CSS and JS links carry a version stamp that changes whenever you edit those files, so visitors always get the newest version.

Hosting on Cloudflare? Run `php artisan site:export --url=https://your-domain.com` after editing, so `public/index.html` picks up your changes.

---

## 7. Behaviour notes

- **Scroll animations** (sections fading in, the chapter rail, light sweeps on the mosaic) use modern CSS scroll timelines. Browsers without that feature simply show everything in place.
- **Reduced motion:** visitors who turn on "reduce motion" on their device get the page without animations.
- **Testimony viewer:** opens from any panel or "See all 14 testimony samples". Prev/next, arrow keys and Escape all work, and focus stays inside the viewer while it's open.
- **Charts:** drag the slider to reveal the marked plan. Each chart nudges once when it scrolls into view, so visitors see it can be dragged.

Trading carries a high risk of loss. Everything on this site is for education only and is not financial advice.
