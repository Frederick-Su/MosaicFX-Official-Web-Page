<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ExportStaticSiteTest extends TestCase
{
    private string $out;

    protected function setUp(): void
    {
        parent::setUp();

        if (! File::exists(public_path('build/manifest.json'))) {
            $this->markTestSkipped('Run `npm run build` before testing the static export.');
        }

        $this->out = storage_path('framework/testing/static-export');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->out);

        parent::tearDown();
    }

    public function test_it_writes_a_self_contained_static_site(): void
    {
        // No setup chart, so the count below doesn't change when a real one is added to the folder.
        config(['mosaic.telegram_url' => 'https://t.me/+example', 'mosaic.setup.path' => 'images/does-not-exist']);

        $this->artisan('site:export', ['--out' => $this->out, '--url' => 'https://mosaicfx.pages.dev'])
            ->assertSuccessful();

        $html = File::get($this->out.'/index.html');

        $this->assertStringContainsString('<link rel="canonical" href="https://mosaicfx.pages.dev">', $html);
        $this->assertStringContainsString('https://mosaicfx.pages.dev/build/assets/', $html);
        $this->assertSame(3, substr_count($html, 'href="https://t.me/+example"'));
        $this->assertFileExists($this->out.'/build/manifest.json');
        $this->assertFileExists($this->out.'/images/og.jpg');
        $this->assertFileDoesNotExist($this->out.'/index.php');
        $this->assertFileDoesNotExist($this->out.'/.htaccess');
    }
}
