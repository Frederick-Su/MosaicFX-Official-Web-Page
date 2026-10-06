@extends('layouts.app')

@php
    $name = config('mosaic.name');
    $tagline = config('mosaic.tagline');
    $fig = fn (int $i) => 'Fig. '.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
    // Proof is shown only when it's real: member screenshots, the trade record link, or (locally) the reminder to set it.
    $record = config('mosaic.trade_record');
    $showRecord = filled($record['url']) || app()->isLocal();
    $showProof = $testimonials->isNotEmpty() || $showRecord;
    // The shared setup appears only once a real chart is in the folder (locally, a reminder shows instead).
    $showSetup = filled($setup) || app()->isLocal();
@endphp

@section('title', $name.' — Learn forex from zero, with free setups in Telegram')
@section('description', 'MosaicFX teaches forex from zero: our own backtested strategies, hands-on MetaTrader 5, TradingView and Exness lessons, and free trade setups in our Telegram group.')

@section('content')
<main>

    {{-- ============================================================ PLATE 01 — COVER --}}
    <section class="hero" id="top" data-hero>
        <canvas class="glass-canvas" data-glass="hero" aria-hidden="true"></canvas>
        <x-mosaic-texture class="hero__texture" :opacity="0.85" />

        <div class="plate-rule hero__rule" data-glass-keepout>
            <span>{{ $name }}</span>
            <span>Education &amp; setups</span>
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
            {{-- The wordmark and locked tagline are the mark; the h1 says what MosaicFX actually offers. --}}
            <p class="hero__wordmark" data-glass-keepout>{{ $name }}</p>
            <p class="hero__tagline" data-glass-keepout>{{ $tagline }}</p>
            <h1 class="hero__offer" data-glass-keepout>Learn to trade forex from zero, with strategies we built and backtested ourselves.</h1>

            <div class="hero__cta" data-glass-keepout data-glass-floor>
                <x-telegram-button size="xl" />
                <p class="meta">Free to join <span aria-hidden="true">·</span> Learn at your own pace</p>
                <p class="cta-risk">Trading carries a high risk of loss. Education, not financial advice.</p>
            </div>
        </div>
    </section>

    {{-- ============================================================ PLATE 02 — WHAT LANDS IN THE GROUP --}}
    @if ($showSetup)
    <section class="section setup" id="setups" aria-labelledby="setup-title">
        <div class="container">

            <div class="setup__intro" data-reveal data-reveal-delay="120">
                <h2 class="hook" id="setup-title">Spot the setup</h2>
                <p class="lead">This is what we share in the free Telegram group: real setups on the chart, with the entry, the stop and the target marked.</p>
            </div>

            {{-- The one real chart, shown large in a leaded frame. It opens full size without any script. --}}
            <figure class="setup__figure" data-reveal data-reveal-delay="160">
                @if ($setup)
                    <a class="setup__frame" href="{{ $setup['src'] }}" target="_blank" rel="noopener">
                        <img
                            src="{{ $setup['src'] }}"
                            alt="{{ $setup['alt'] }}"
                            width="{{ $setup['width'] }}"
                            height="{{ $setup['height'] }}"
                            loading="lazy"
                            decoding="async"
                        >
                        <span class="sr-only">Open the chart full size (opens in a new tab)</span>
                    </a>
                    @if ($setup['facts'])
                        <figcaption class="setup__caption">
                            @foreach ($setup['facts'] as $label => $value)
                                <span class="setup__fact"><span class="setup__fact-label">{{ $label }}</span> {{ $value }}</span>
                            @endforeach
                        </figcaption>
                    @endif
                @else
                    <p class="setup__unset">No setup chart yet. Add a real chart image (1600px wide or more) to public/images/setup/ and its details to config/mosaic.php. Production pages hide this section until it's there.</p>
                @endif
            </figure>
            <p class="setup__risk" data-reveal data-reveal-delay="160">A shared setup is education, not financial advice. Past results don't guarantee future performance.</p>

            <ul class="setup__points" data-reveal data-reveal-delay="200">
                <li class="setup__point">
                    <h3 class="setup__point-title">Marked on the chart.</h3>
                    <p>Entry, stop and target, the way you'll see them on MetaTrader 5 or TradingView.</p>
                </li>
                <li class="setup__point">
                    <h3 class="setup__point-title">Losses posted too.</h3>
                    <p>Every setup we share stays on the record, win or lose.</p>
                </li>
                <li class="setup__point">
                    <h3 class="setup__point-title">Free to read along.</h3>
                    <p>Watch how we read the market for as long as you like. Leave anytime.</p>
                </li>
            </ul>

            <div class="setup__cta" data-reveal data-reveal-delay="240">
                <x-telegram-button size="xl" />
            </div>
        </div>
    </section>
    @endif

    {{-- ============================================================ PLATE 03 — WHO WE ARE + MEMBER PROOF --}}
    <section class="section story" id="story" aria-labelledby="story-title">
        <div class="container">

            <div @class(['story__grid', 'story__grid--solo' => ! $showProof])>
                <div class="story__text" data-reveal data-reveal-delay="120">
                    <h2 class="hook" id="story-title">One pane at a time</h2>
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
                                <p>Rattled by a trade? Message us. We'll help you think it through against your plan, but we won't tell you what to do with your money.</p>
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

    {{-- ============================================================ PLATE 04 — HOW WE TEACH --}}
    <section class="section teach" id="how-we-teach" aria-labelledby="teach-title">
        <x-mosaic-texture class="teach__texture" :opacity="0.15" />

        <div class="container">

            <div class="teach__intro" data-reveal data-reveal-delay="120">
                <h2 class="hook" id="teach-title">One path, end to end</h2>
                <p class="lead">Nobody becomes a trader in a day. We start from zero and stay with you the whole way, one pane at a time.</p>
            </div>

            {{-- The numbers stand on their own band, apart from the path below. --}}
            <ul class="plate-grid stats" data-reveal data-reveal-delay="160">
                <li class="plate-cell">
                    <span class="plate-cell__head"><span>Mosaic Academy</span></span>
                    <span class="stat"><span class="stat__value">21</span><span class="stat__unit">lessons</span></span>
                    <span class="caption">Short, self-paced, with quiz breaks along the way.</span>
                </li>
                <li class="plate-cell">
                    <span class="plate-cell__head"><span>Pace</span></span>
                    <span class="stat"><span class="stat__value">7</span><span class="stat__unit">days</span></span>
                    <span class="caption">Three lessons a day, or slower if you like.</span>
                </li>
                <li class="plate-cell">
                    <span class="plate-cell__head"><span>Setups</span></span>
                    <span class="stat"><span class="stat__value">Free</span></span>
                    <span class="caption">Every setup we share, wins and losses both.</span>
                </li>
                <li class="plate-cell">
                    <span class="plate-cell__head"><span>Support</span></span>
                    <span class="stat"><span class="stat__value">Direct</span><span class="stat__unit">messages</span></span>
                    <span class="caption">Message us anytime. We help you think it through; the decision stays yours.</span>
                </li>
            </ul>

            <ol class="plate-grid steps" data-reveal data-reveal-delay="200">
                <li class="plate-cell">
                    <span class="plate-cell__head"><span>Step 01</span><span>The group</span></span>
                    <h3 class="step__title">Start in the free group.</h3>
                    <p>Join our Telegram and watch how we read the market. Every setup we share is free, and we post the losses too.</p>
                </li>
                <li class="plate-cell">
                    <span class="plate-cell__head"><span>Step 02</span><span>The academy</span></span>
                    <h3 class="step__title">Learn with Mosaic Academy.</h3>
                    <p>Twenty-one short lessons, three a day for a week, at your own pace: the basics, then our backtested strategies, then the apps. <a class="step__link" href="#academy">See what's inside</a></p>
                </li>
                <li class="plate-cell">
                    <span class="plate-cell__head"><span>Step 03</span><span>Practice</span></span>
                    <h3 class="step__title">Practice with someone in your corner.</h3>
                    <p>Start on demo. There's no pressure to touch real money until you feel ready, and if a trade rattles you, message us.</p>
                </li>
                <li class="plate-cell">
                    <span class="plate-cell__head"><span>Step 04</span><span>Independence</span></span>
                    <h3 class="step__title">Make your own decisions.</h3>
                    <p>The goal is independence: reading setups yourself, not waiting on our setups forever. Trade the law, not the feeling.</p>
                </li>
            </ol>

            {{-- Step 02 opened up: the three stages as panes joined by gold leading, one after the other. --}}
            <div class="academy" id="academy" data-reveal data-reveal-delay="120">
                <div class="academy__intro">
                    <h3 class="academy__title" id="academy-title">Inside Mosaic Academy.</h3>
                    <p class="academy__body">Each lesson pairs a short video with written notes, plus quick quiz breaks so nothing slips by. It runs in three stages, and each one builds on the last.</p>
                    <x-telegram-button label="Ask about the Academy in the group" variant="outline" class="academy__cta" />
                    <p class="academy__note">Access is arranged by an admin in our Telegram group.</p>
                </div>

                <ol class="academy__path">
                    <li class="academy__stage glass-card">
                        <span class="academy__stage-num">Stage 1</span>
                        <h4 class="academy__stage-title">Trading from zero.</h4>
                        <p>No prior knowledge assumed. The basics of how a trade works come first, and each lesson builds on the last.</p>
                    </li>
                    <li class="academy__stage glass-card">
                        <span class="academy__stage-num">Stage 2</span>
                        <h4 class="academy__stage-title">MosaicFX strategies.</h4>
                        <p>The professional strategies we built and backtested ourselves.</p>
                    </li>
                    <li class="academy__stage glass-card">
                        <span class="academy__stage-num">Stage 3</span>
                        <h4 class="academy__stage-title">The apps, hands-on.</h4>
                        <p>MetaTrader 5, TradingView and Exness, taught step by step as you'll actually see them.</p>
                        <p class="academy__disclosure">Exness is our recommended broker. MosaicFX earns a commission if you open an account through our link.</p>
                    </li>
                </ol>
            </div>
        </div>
    </section>

    {{-- ============================================================ PLATE 05 — CLOSING FRAME --}}
    <section class="join" id="join" aria-labelledby="join-title">
        <canvas class="glass-canvas" data-glass="join" aria-hidden="true"></canvas>
        <x-mosaic-texture class="join__texture" :opacity="0.5" />

        <div class="join__card glass-card" data-glass-keepout data-reveal>
            <img class="join__coin" src="{{ asset('images/brand/coin-512.webp') }}" alt="" width="512" height="512" loading="lazy">
            <h2 class="join__title" id="join-title">We build the picture together.</h2>
            <p class="join__body">Join the free Telegram group. Read along, ask anything, and start when you're ready. No pressure, no countdowns.</p>
            <x-telegram-button size="xl" />
            <p class="meta">Free to join <span aria-hidden="true">·</span> Leave anytime</p>
            <p class="cta-risk">Trading carries a high risk of loss. Education, not financial advice.</p>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="container">

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
            <p><strong>Risk disclaimer.</strong> Trading forex, gold and other leveraged products carries a high level of risk and may not be suitable for everyone. Past results do not guarantee future performance. Never risk more than you can afford to lose. Everything {{ $name }} shares is for education only and is not financial advice. {{ $name }} earns a commission if you open an Exness account through our link.</p>
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
