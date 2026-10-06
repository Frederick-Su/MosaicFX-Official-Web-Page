@extends('layouts.app')

@php
    $name = config('mosaic.name');
    $tagline = config('mosaic.tagline');
    $fig = fn (int $i) => 'Fig. '.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
    // Proof is shown only when it's real: member screenshots, the trade record link, or (locally) the reminder to set it.
    $record = config('mosaic.trade_record');
    $showRecord = filled($record['url']) || app()->isLocal();
    $showProof = $testimonials->isNotEmpty() || $showRecord;
@endphp

@section('title', $name.' — '.$tagline)
@section('description', 'MosaicFX is a forex trading education and signals community. Free signals in our Telegram group, the self-paced Mosaic Academy, and real support while you learn.')

@section('content')
<main>

    {{-- ============================================================ PLATE 01 — COVER --}}
    <section class="hero" id="top" data-hero>
        <canvas class="glass-canvas" data-glass="hero" aria-hidden="true"></canvas>
        <x-mosaic-texture class="hero__texture" :opacity="0.85" />

        <div class="plate-rule hero__rule" data-glass-keepout>
            <span>{{ $name }}</span>
            <span>Plate 01<span class="meta-extra"> · Education &amp; signals</span></span>
        </div>

        <div class="hero__inner">
            {{-- The coin tilts under the cursor as if pressed, and its gold catches the light where you touch it. --}}
            <span class="hero__coin coin" data-coin data-glass-keepout>
                <img
                    src="{{ asset('images/brand/coin-512.webp') }}"
                    srcset="{{ asset('images/brand/coin-512.webp') }} 512w, {{ asset('images/brand/coin-1024.webp') }} 1024w"
                    sizes="(min-width: 1024px) 448px, 280px"
                    alt=""
                    width="512"
                    height="512"
                >
                <span class="coin__sheen" aria-hidden="true"></span>
            </span>
            <h1 class="hero__wordmark" data-glass-keepout>{{ $name }}</h1>
            <p class="hero__tagline" data-glass-keepout>{{ $tagline }}</p>

            <div class="hero__cta" data-glass-keepout data-glass-floor>
                <x-telegram-button size="xl" />
                <p class="meta">Free to join <span aria-hidden="true">·</span> Learn at your own pace</p>
            </div>
        </div>
    </section>

    {{-- ============================================================ PLATE 02 — WHO WE ARE + MEMBER PROOF --}}
    <section class="section story" id="story" aria-labelledby="story-title">
        <div class="container">
            <header class="plate-header" data-reveal>
                <span class="plate-header__plate">Plate 02</span>
                <h2 class="plate-header__title" id="story-title">Section I — who we are.</h2>
            </header>

            <div @class(['story__grid', 'story__grid--solo' => ! $showProof])>
                <div class="story__text" data-reveal data-reveal-delay="120">
                    <p class="hook">One pane at a time</p>
                    <p class="lead">MosaicFX is a trading community that learns together. We show the wins and the losses, all of them, because trust is built on the whole picture, not a highlight reel.</p>
                    <p class="body">You don't have to figure this out alone, and you don't have to rush. A brand-new member isn't a lesser trader, just a pane that hasn't been placed yet.</p>

                    <ol class="values">
                        <li class="value">
                            <span class="value__num">01</span>
                            <div>
                                <h3 class="value__title">Wins and losses, both shown.</h3>
                                <p>No cherry-picked feed. You see the real record, good days and bad.</p>
                            </div>
                        </li>
                        <li class="value">
                            <span class="value__num">02</span>
                            <div>
                                <h3 class="value__title">We never chase you.</h3>
                                <p>No countdowns, no "limited spots." You join when you're ready.</p>
                            </div>
                        </li>
                        <li class="value">
                            <span class="value__num">03</span>
                            <div>
                                <h3 class="value__title">Message us anytime.</h3>
                                <p>Especially mid-trade. Reaching for your stop loss? Don't sit with it alone.</p>
                            </div>
                        </li>
                    </ol>
                </div>

                @if ($showProof)
                <div @class(['proof', 'proof--record' => $testimonials->isEmpty()]) data-proof data-reveal data-reveal-delay="220">
                    <x-mosaic-texture class="proof__texture" :opacity="0.5" />

                    @if ($testimonials->isNotEmpty())
                        <div class="proof__grid">
                            @foreach ($testimonials->take(4) as $index => $shot)
                                <figure class="proof__slot" data-proof-slot>
                                    <button type="button" class="proof__frame" data-lightbox-index="{{ $index }}" aria-label="View {{ strtolower($shot['alt']) }} full size">
                                        <img
                                            src="{{ $shot['src'] }}"
                                            alt="{{ $shot['alt'] }}"
                                            width="{{ $shot['width'] }}"
                                            height="{{ $shot['height'] }}"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    </button>
                                    <figcaption class="proof__caption">
                                        <span data-proof-fig>{{ $fig($index) }}</span>
                                        <span>Member story</span>
                                    </figcaption>
                                </figure>
                            @endforeach
                        </div>
                        <script type="application/json" data-lightbox-items>@json($testimonials->values())</script>
                        <p class="proof__line">Shared with permission. Every size of win counts.</p>
                    @endif

                    @if ($showRecord)
                        {{-- The whole record, not a highlight reel: one glass pane, one link, and the risk said beside it. --}}
                        <div class="record glass-card">
                            <h3 class="record__title">The full trade record.</h3>
                            @if ($record['from'])
                                <p class="record__range">
                                    <span>{{ $record['from'] }}</span>
                                    <span class="record__to" aria-hidden="true"></span>
                                    <span class="sr-only">to</span>
                                    <span>{{ $record['to'] ?: 'Today' }}</span>
                                </p>
                            @endif
                            <p class="record__body">Every trade we've taken, wins and losses both. Read it before you decide to trust us.</p>
                            @if ($record['url'])
                                <a href="{{ $record['url'] }}" target="_blank" rel="noopener" class="btn btn--outline btn--lg record__link">
                                    <span>Open the trade record</span>
                                    <x-icon name="arrow-up-right" class="btn__icon" />
                                    <span class="sr-only">(opens in a new tab)</span>
                                </a>
                            @else
                                <p class="record__unset">Trade record link not set yet. Add MOSAIC_TRADE_RECORD_URL to your .env file. Production pages hide this panel until it's set.</p>
                            @endif
                            <p class="record__risk">Past results don't guarantee future performance. Trading carries a high risk of loss.</p>
                        </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ============================================================ PLATE 03 — HOW WE TEACH --}}
    <section class="section teach" id="how-we-teach" aria-labelledby="teach-title">
        <x-mosaic-texture class="teach__texture" :opacity="0.15" />

        <div class="container">
            <header class="plate-header" data-reveal>
                <span class="plate-header__plate">Plate 03</span>
                <h2 class="plate-header__title" id="teach-title">Section II — how we teach.</h2>
            </header>

            <div class="teach__intro" data-reveal data-reveal-delay="120">
                <p class="hook">Spot the setup</p>
                <p class="lead">Nobody becomes a trader in a day. We go one pane at a time, and we stay with you the whole way.</p>
            </div>

            <ul class="plate-grid stats" data-reveal data-reveal-delay="160">
                <li class="plate-cell">
                    <span class="plate-cell__head"><span>Mosaic Academy</span><span class="plate-cell__fig">Fig. 3A</span></span>
                    <span class="stat"><span class="stat__value">21</span><span class="stat__unit">lessons</span></span>
                    <span class="caption">Short, self-paced, with quiz breaks along the way.</span>
                </li>
                <li class="plate-cell">
                    <span class="plate-cell__head"><span>Pace</span><span class="plate-cell__fig">Fig. 3B</span></span>
                    <span class="stat"><span class="stat__value">7</span><span class="stat__unit">days</span></span>
                    <span class="caption">Three lessons a day, or slower if you like.</span>
                </li>
                <li class="plate-cell">
                    <span class="plate-cell__head"><span>Signals</span><span class="plate-cell__fig">Fig. 3C</span></span>
                    <span class="stat"><span class="stat__value">100%</span><span class="stat__unit">free</span></span>
                    <span class="caption">Every signal we share, wins and losses both.</span>
                </li>
                <li class="plate-cell">
                    <span class="plate-cell__head"><span>Support</span><span class="plate-cell__fig">Fig. 3D</span></span>
                    <span class="stat"><span class="stat__value">1:1</span></span>
                    <span class="caption">Message us anytime, especially mid-trade.</span>
                </li>
            </ul>

            <ol class="plate-grid steps" data-reveal data-reveal-delay="200">
                <li class="plate-cell">
                    <span class="plate-cell__head"><span>Step 01</span><span>The group</span></span>
                    <h3 class="step__title">Start in the free group.</h3>
                    <p>Join our Telegram and watch how we read the market. Every signal we share is free, and we post the losses too.</p>
                </li>
                <li class="plate-cell">
                    <span class="plate-cell__head"><span>Step 02</span><span>The academy</span></span>
                    <h3 class="step__title">Learn with Mosaic Academy.</h3>
                    <p>Twenty-one short lessons, three a day for a week, at your own pace. Each pairs a short video with written notes, plus quick quiz breaks so nothing slips by.</p>
                </li>
                <li class="plate-cell">
                    <span class="plate-cell__head"><span>Step 03</span><span>Practice</span></span>
                    <h3 class="step__title">Practice with someone in your corner.</h3>
                    <p>Start on demo. There's no pressure to touch real money until you feel ready, and if a trade rattles you, message us.</p>
                </li>
                <li class="plate-cell">
                    <span class="plate-cell__head"><span>Step 04</span><span>Your own calls</span></span>
                    <h3 class="step__title">Make your own calls.</h3>
                    <p>The goal is independence: reading setups yourself, not waiting on our calls forever. Trade the law, not the feeling.</p>
                </li>
            </ol>
        </div>
    </section>

    {{-- ============================================================ PLATE 04 — CLOSING FRAME --}}
    <section class="join" id="join" aria-labelledby="join-title">
        <canvas class="glass-canvas" data-glass="join" aria-hidden="true"></canvas>
        <x-mosaic-texture class="join__texture" :opacity="0.5" />

        <div class="join__card glass-card" data-glass-keepout data-reveal>
            <img class="join__coin" src="{{ asset('images/brand/coin-512.webp') }}" alt="" width="512" height="512" loading="lazy">
            <p class="join__wordmark">{{ $name }}</p>
            <p class="join__tagline">{{ $tagline }}</p>
            <h2 class="join__title" id="join-title">We build the picture together.</h2>
            <p class="join__body">Join the free Telegram group. Read along, ask anything, and start when you're ready. No pressure, no countdowns.</p>
            <x-telegram-button size="xl" />
            <p class="meta">Free to join <span aria-hidden="true">·</span> Leave anytime</p>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="container">
        <div class="plate-rule footer__rule">
            <span class="footer__tagline">{{ $tagline }}</span>
            <span>End of Plate</span>
        </div>

        <div class="footer__top">
            <a class="footer__brand" href="#top">
                <img src="{{ asset('images/brand/coin-160.webp') }}" alt="" width="44" height="44" loading="lazy">
                <span class="footer__wordmark">{{ $name }}</span>
            </a>
            <nav class="footer__nav" aria-label="Footer">
                <a href="#story">Who we are</a>
                <a href="#how-we-teach">How we teach</a>
                <a href="#join">Join</a>
            </nav>
        </div>

        <div class="footer__legal">
            <p><strong>Risk disclaimer.</strong> Trading forex, gold and other leveraged products carries a high level of risk and may not be suitable for everyone. Past results do not guarantee future performance. Never risk more than you can afford to lose. Everything {{ $name }} shares is for education only and is not financial advice.</p>
            <p class="footer__line">Small pieces. Long game.</p>
            <p class="meta">&copy; {{ date('Y') }} {{ $name }}</p>
        </div>
    </div>
</footer>

@if ($testimonials->isNotEmpty())
<dialog class="lightbox" data-lightbox-dialog aria-labelledby="lightbox-title">
    <div class="lightbox__plate">
        <div class="lightbox__head">
            <span class="lightbox__heading">
                <span class="meta" data-lightbox-counter>Fig. 01</span>
                <span class="lightbox__title" id="lightbox-title">Member story</span>
            </span>
            <form method="dialog">
                <button class="icon-btn icon-btn--ghost" aria-label="Close"><x-icon name="x" /></button>
            </form>
        </div>
        <div class="lightbox__stage">
            <img data-lightbox-image alt="">
        </div>
        <div class="lightbox__foot">
            <button type="button" class="icon-btn" data-lightbox-prev aria-label="Previous story"><x-icon name="chevron-left" /></button>
            <span class="lightbox__note">Shared with permission. Real proof only.</span>
            <button type="button" class="icon-btn" data-lightbox-next aria-label="Next story"><x-icon name="chevron-right" /></button>
        </div>
    </div>
</dialog>
@endif

<div class="toast" data-toast role="status" aria-live="polite" hidden>
    <x-icon name="info" class="toast__icon" />
    <span class="toast__body">
        <span class="toast__message" data-toast-message></span>
        <span class="toast__meta">Telegram</span>
    </span>
</div>
@endsection
