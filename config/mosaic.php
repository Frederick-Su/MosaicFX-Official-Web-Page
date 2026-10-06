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
    | Trade record
    |--------------------------------------------------------------------------
    |
    | The public record of every trade taken, wins and losses. It is the proof
    | panel beside "who we are". Set the link and the period it covers in your
    | .env file, written as you want them read: MOSAIC_TRADE_RECORD_FROM="March
    | 2024". Leave MOSAIC_TRADE_RECORD_TO empty while the record is still
    | running and the page says "to today". Until the link is set, a production
    | page leaves the panel out rather than point at proof that isn't there.
    |
    */

    'trade_record' => [
        'url' => env('MOSAIC_TRADE_RECORD_URL'),
        'from' => env('MOSAIC_TRADE_RECORD_FROM'),
        'to' => env('MOSAIC_TRADE_RECORD_TO'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Shared setup
    |--------------------------------------------------------------------------
    |
    | "What lands in the group" shows one real chart from a setup you shared:
    | the first image in this folder (inside /public), ideally 1600px wide or
    | more, with the entry, stop and target marked. Fill in the facts below as
    | you want them read; any left empty are simply not shown. Until an image
    | is in the folder, a production page leaves the section out. Never use a
    | mocked-up chart.
    |
    */

    'setup' => [
        'path' => 'images/setup',
        'pair' => null,       // e.g. 'XAUUSD'
        'timeframe' => null,  // e.g. 'H1'
        'shared' => null,     // e.g. '12 September 2026'
        'outcome' => null,    // e.g. 'Hit target' or 'Stopped out'
    ],

    /*
    |--------------------------------------------------------------------------
    | Testimonials
    |--------------------------------------------------------------------------
    |
    | Every image in this folder (inside /public) is shown in the member-proof
    | panel, sorted by file name. To swap in real screenshots, delete the
    | placeholder files and drop yours in (jpg, png, webp, avif, gif or svg).
    | Name them 01-..., 02-... to control the order. Real proof only: the
    | brand never shows an invented result, so files named placeholder-* are
    | always skipped.
    |
    */

    'testimonials_path' => 'images/testimonials',

];
