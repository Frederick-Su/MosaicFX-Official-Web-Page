<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Brand
    |--------------------------------------------------------------------------
    |
    | The wordmark (set in Fraunces Italic, as written here) and the locked tagline from
    | the Mosaic FX design system. The tagline is part of the mark: it sits
    | beneath the wordmark and is never reworded.
    |
    */

    'name' => 'MosaicFX',

    'tagline' => 'Smart Trades, Big Effects',

    /*
    |--------------------------------------------------------------------------
    | Telegram group
    |--------------------------------------------------------------------------
    |
    | Every "Join our Telegram Group" button points here. Set it in your .env
    | file (MOSAIC_TELEGRAM_URL=https://t.me/...). While it's empty the buttons
    | still work, they just show a small "link coming soon" notice.
    |
    */

    'telegram_url' => env('MOSAIC_TELEGRAM_URL'),

    /*
    |--------------------------------------------------------------------------
    | Testimonials
    |--------------------------------------------------------------------------
    |
    | Every image in this folder (inside /public) is shown in the member-proof
    | panel, sorted by file name. To swap in real screenshots, delete the
    | placeholder files and drop yours in (jpg, png, webp, avif, gif or svg).
    | Name them 01-..., 02-... to control the order. Real proof only: the
    | brand never shows an invented result.
    |
    */

    'testimonials_path' => 'images/testimonials',

];
