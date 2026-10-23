<?php

use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Artisan;

/*
 * php artisan site:export [--url=https://your-domain.com]
 *
 * Renders the landing page into public/index.html so it can be hosted as a plain static site
 * (Cloudflare Workers / Pages, Netlify, GitHub Pages). Run it again after every page edit and commit the result.
 * Without --url, all links are root-relative ("/images/..."), which works on any domain.
 */
Artisan::command('site:export {--url= : Public site URL used for the canonical and social-share links}', function () {
    $html = view('landing', ['assetVersion' => LandingController::assetVersion()])->render();

    $base = rtrim(url('/'), '/');
    $target = rtrim((string) $this->option('url'), '/');
    $html = str_replace($base, $target, $html);

    if ($target === '') {
        $html = str_replace(
            ['<link rel="canonical" href="">', '<meta property="og:url" content="">'],
            ['<link rel="canonical" href="/">', '<meta property="og:url" content="/">'],
            $html
        );
    }

    file_put_contents(public_path('index.html'), $html);

    $this->info('Wrote public/index.html ('.number_format(strlen($html)).' bytes)');
})->purpose('Export the landing page as static HTML for static hosting');
