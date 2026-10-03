<?php

namespace App\Console\Commands;

use App\Http\Controllers\HomeController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;

/**
 * Renders the site into a plain folder of static files (for Cloudflare Pages or any static host).
 * The page is rendered by the same controller and Blade views as the Laravel app, so the
 * design stays identical; everything in /public (built assets, images, favicons) is copied alongside.
 */
class ExportStaticSite extends Command
{
    protected $signature = 'site:export
        {--out=dist : Folder to write the static site into}
        {--url= : Public URL the site will live at (defaults to APP_URL)}';

    protected $description = 'Export the site as static files (index.html plus everything in /public)';

    /** Server-only files in /public that a static host doesn't need. */
    private const SKIP = ['index.php', '.htaccess', 'hot'];

    public function handle(): int
    {
        $url = rtrim($this->option('url') ?: config('app.url'), '/');
        $out = $this->outputPath();

        if (! File::exists(public_path('build/manifest.json'))) {
            $this->error('No production assets found. Run `npm run build` first.');

            return self::FAILURE;
        }

        // Render against the built assets even if a Vite dev server left a "hot" file behind.
        Vite::useHotFile(storage_path('framework/vite.hot.disabled'));

        // Every absolute URL in the page (canonical, og:image, assets) points at the real site.
        config(['app.url' => $url]);
        URL::forceRootUrl($url);
        URL::forceScheme(parse_url($url, PHP_URL_SCHEME) ?: 'https');

        $html = app(HomeController::class)()->render();

        File::deleteDirectory($out);
        File::copyDirectory(public_path(), $out);
        foreach (self::SKIP as $file) {
            File::delete($out.DIRECTORY_SEPARATOR.$file);
        }
        File::put($out.DIRECTORY_SEPARATOR.'index.html', $html);

        $telegram = config('mosaic.telegram_url') ?: 'not set (buttons show the "coming soon" notice)';
        $this->info("Static site written to {$out}");
        $this->line("  URL:      {$url}");
        $this->line("  Telegram: {$telegram}");

        return self::SUCCESS;
    }

    private function outputPath(): string
    {
        $out = $this->option('out');

        return str_starts_with($out, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:[\\\\\/]/', $out)
            ? $out
            : base_path($out);
    }
}
