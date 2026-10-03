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
}
