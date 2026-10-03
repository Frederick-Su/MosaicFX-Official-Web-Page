{{--
    The leaded stained-glass field — the brand's one legal decorative texture.
    A straight port of the design system's MosaicTexture component: pane coordinates and
    colours are lifted verbatim from Brand Reference Plate 01, Section III. Never re-draw them.
    The animated glass (resources/js/glass) reads its panes from this markup too.

    Intensity: 0.15 behind a dense editorial grid, 0.35 under an educational hook,
    0.5 the default, 0.85 hero frames only.
--}}
@props(['opacity' => 0.5, 'gloss' => true, 'backlight' => true])

@php($uid = 'mfx-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(6)))

<svg
    viewBox="0 0 400 400"
    preserveAspectRatio="xMidYMid slice"
    aria-hidden="true"
    focusable="false"
    {{ $attributes->class('mosaic-texture')->merge(['style' => "opacity: {$opacity}"]) }}
>
    <defs>
        <radialGradient id="{{ $uid }}-backlight" cx="42%" cy="34%" r="72%">
            <stop offset="0%" stop-color="#F2C879" stop-opacity="0.42"/>
            <stop offset="46%" stop-color="#F2C879" stop-opacity="0.12"/>
            <stop offset="100%" stop-color="#1B1030" stop-opacity="0.55"/>
        </radialGradient>
        <linearGradient id="{{ $uid }}-gloss" x1="0" y1="0" x2="0.7" y2="1">
            <stop offset="0%" stop-color="#F5F0E6" stop-opacity="0.30"/>
            <stop offset="38%" stop-color="#F5F0E6" stop-opacity="0.05"/>
            <stop offset="100%" stop-color="#1B1030" stop-opacity="0.28"/>
        </linearGradient>
    </defs>
    <rect width="400" height="400" fill="#1B1030"/>
    <g data-panes>
        <polygon points="0,0 98,0 88,112 0,95" fill="#5B2E91"/>
        <polygon points="98,0 205,0 212,92 88,112" fill="#3A4FD0"/>
        <polygon points="205,0 300,0 312,108" fill="#D6237F"/>
        <polygon points="205,0 312,108 212,92" fill="#42207A"/>
        <polygon points="300,0 400,0 400,98 312,108" fill="#5B2E91"/>
        <polygon points="0,95 88,112 104,196 0,208" fill="#2A1550"/>
        <polygon points="88,112 212,92 196,214" fill="#D6237F"/>
        <polygon points="88,112 196,214 104,196" fill="#3A4FD0"/>
        <polygon points="212,92 312,108 298,192 196,214" fill="#5B2E91"/>
        <polygon points="312,108 400,98 400,205 298,192" fill="#8A2A6A"/>
        <polygon points="0,208 104,196 92,308 0,296" fill="#3A4FD0"/>
        <polygon points="104,196 196,214 208,292 92,308" fill="#42207A"/>
        <polygon points="196,214 298,192 306,312" fill="#D6237F"/>
        <polygon points="196,214 306,312 208,292" fill="#5B2E91"/>
        <polygon points="298,192 400,205 400,298 306,312" fill="#3A4FD0"/>
        <polygon points="0,296 92,308 110,400 0,400" fill="#5B2E91"/>
        <polygon points="92,308 208,292 198,400 110,400" fill="#8A2A6A"/>
        <polygon points="208,292 306,312 304,400 198,400" fill="#2A1550"/>
        <polygon points="306,312 400,298 400,400 304,400" fill="#D6237F"/>
    </g>
    <g fill="none" stroke="#D8B15C" stroke-width="2.2" stroke-linejoin="round" opacity="0.92">
        <polyline points="0,95 88,112 212,92 312,108 400,98"/>
        <polyline points="0,208 104,196 196,214 298,192 400,205"/>
        <polyline points="0,296 92,308 208,292 306,312 400,298"/>
        <polyline points="98,0 88,112 104,196 92,308 110,400"/>
        <polyline points="205,0 212,92 196,214 208,292 198,400"/>
        <polyline points="300,0 312,108 298,192 306,312 304,400"/>
        <polyline points="205,0 312,108 212,92"/>
        <polyline points="88,112 196,214 104,196"/>
        <polyline points="196,214 306,312 208,292"/>
    </g>
    @if ($gloss)
        <rect width="400" height="400" fill="url(#{{ $uid }}-gloss)"/>
    @endif
    @if ($backlight)
        <rect width="400" height="400" fill="url(#{{ $uid }}-backlight)"/>
    @endif
</svg>
