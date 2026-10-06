<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_the_home_page_shows_the_mark_and_every_plate(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(config('mosaic.name'))
            ->assertSee('Smart Trades, Big Effects')
            ->assertSee('Section I — who we are.')
            ->assertSee('Section II — how we teach.')
            ->assertSee('We build the picture together.')
            ->assertSee('End of Plate')
            ->assertSee('Risk disclaimer.', false);
    }

    public function test_the_glass_reads_its_panes_from_the_brand_texture(): void
    {
        config(['mosaic.trade_record.url' => 'https://example.com/record']);

        $content = $this->get('/')->assertOk()->getContent();

        // Hero, member proof, how-we-teach and the closing frame each carry the texture.
        $this->assertSame(4, substr_count($content, 'class="mosaic-texture'));
        $this->assertStringContainsString('points="0,0 98,0 88,112 0,95" fill="#5B2E91"', $content);
    }

    public function test_telegram_buttons_wait_for_a_link_until_one_is_set(): void
    {
        config(['mosaic.telegram_url' => null]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Join our Telegram Group')
            ->assertSee('data-telegram-placeholder', false);
    }

    public function test_telegram_buttons_use_the_configured_link(): void
    {
        config(['mosaic.telegram_url' => 'https://t.me/+example']);

        $response = $this->get('/')->assertOk();

        $response->assertDontSee('data-telegram-placeholder', false);
        $this->assertSame(2, substr_count($response->getContent(), 'href="https://t.me/+example"'));
    }

    public function test_every_image_in_the_testimonials_folder_reaches_the_proof_panel(): void
    {
        $images = collect(File::files(public_path(config('mosaic.testimonials_path'))))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp', 'avif', 'gif', 'svg']));

        $response = $this->get('/')->assertOk();

        $this->assertSame(min(4, $images->count()), substr_count($response->getContent(), 'data-proof-slot'));
        foreach ($images as $image) {
            $response->assertSee(rawurlencode($image->getFilename()), false);
        }
    }

    public function test_an_empty_testimonials_folder_hides_the_frames(): void
    {
        config(['mosaic.testimonials_path' => 'images/does-not-exist']);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('data-proof-slot', false)
            ->assertDontSee('data-lightbox-items', false);
    }

    public function test_placeholder_artwork_never_reaches_the_page(): void
    {
        $folder = 'images/testimonials-test';
        File::ensureDirectoryExists(public_path($folder));
        File::put(public_path($folder.'/placeholder-01.svg'), '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 360 640"></svg>');
        File::put(public_path($folder.'/01-member.svg'), '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 360 640"></svg>');
        config(['mosaic.testimonials_path' => $folder]);

        try {
            $content = $this->get('/')->assertOk()->getContent();

            $this->assertStringNotContainsString('placeholder-01.svg', $content);
            $this->assertStringContainsString('01-member.svg', $content);
            $this->assertSame(1, substr_count($content, 'data-proof-slot'));
        } finally {
            File::deleteDirectory(public_path($folder));
        }
    }

    public function test_the_trade_record_shows_its_link_period_and_risk_note(): void
    {
        config([
            'mosaic.testimonials_path' => 'images/does-not-exist',
            'mosaic.trade_record' => ['url' => 'https://example.com/record', 'from' => 'March 2024', 'to' => null],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('The full trade record.')
            ->assertSee('href="https://example.com/record"', false)
            ->assertSeeInOrder(['March 2024', 'Today'])
            ->assertSee("Past results don't guarantee future performance.", false)
            ->assertDontSee('Real proof only')
            ->assertDontSee('data-lightbox-dialog', false);
    }

    public function test_a_closed_record_shows_its_end_date(): void
    {
        config(['mosaic.trade_record' => ['url' => 'https://example.com/record', 'from' => 'March 2024', 'to' => 'September 2026']]);

        $this->get('/')->assertOk()->assertSeeInOrder(['March 2024', 'September 2026']);
    }

    public function test_production_leaves_the_proof_panel_out_until_the_record_link_is_set(): void
    {
        $this->app['env'] = 'production';
        config([
            'mosaic.testimonials_path' => 'images/does-not-exist',
            'mosaic.trade_record' => ['url' => null, 'from' => null, 'to' => null],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('data-proof', false)
            ->assertDontSee('The full trade record.')
            ->assertSee('story__grid--solo', false);
    }

    public function test_locally_an_unset_record_link_shows_a_reminder(): void
    {
        $this->app['env'] = 'local';
        config(['mosaic.trade_record' => ['url' => null, 'from' => null, 'to' => null]]);

        $this->get('/')
            ->assertOk()
            ->assertSee('The full trade record.')
            ->assertSee('MOSAIC_TRADE_RECORD_URL');
    }
}
