{{-- The one ask: a primary design-system button (gold fill, indigo mono type, square corners). --}}
@props(['label' => 'Join our Telegram Group', 'size' => 'lg'])

@php
    $url = config('mosaic.telegram_url');
    $placeholder = app()->isLocal()
        ? 'Telegram link not set yet. Add MOSAIC_TELEGRAM_URL to your .env file.'
        : 'Our Telegram link is coming soon.';
@endphp

<a
    href="{{ $url ?: '#join' }}"
    @if ($url) target="_blank" rel="noopener" @else data-telegram-placeholder="{{ $placeholder }}" @endif
    {{ $attributes->class(['btn', 'btn--primary', 'btn--'.$size]) }}
>
    <span>{{ $label }}</span>
    <x-icon name="send" class="btn__icon" />
</a>
