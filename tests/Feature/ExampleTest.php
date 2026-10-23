<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_landing_page_loads(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Learn to trade', false);
        $response->assertSee('https://t.me/MosaicFX', false);
        $response->assertSee('css/mosaicfx.css', false);
    }

    public function test_static_assets_exist(): void
    {
        foreach (['css/mosaicfx.css', 'js/mosaicfx.js', 'favicon.png', 'images/scenes/hero-window.webp', 'images/testimonials/testimonial-14.webp'] as $file) {
            $this->assertFileExists(public_path($file));
        }
    }
}
