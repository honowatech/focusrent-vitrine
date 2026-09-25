@php
    use App\Support\Locales;
@endphp
<nav class="flex items-center gap-2 text-sm font-semibold tracking-wide" aria-label="{{ __('site.nav.language') }}">
    @foreach (Locales::available() as $code => $meta)
        @if (! $loop->first)
            <span class="text-gray-600" aria-hidden="true">|</span>
        @endif
        <a href="{{ locale_switch($code) }}"
            hreflang="{{ $meta['hreflang'] }}"
            lang="{{ $code }}"
            @if (app()->getLocale() === $code) aria-current="true" @endif
            class="{{ app()->getLocale() === $code ? 'text-white' : 'text-gray-400 hover:text-white' }}">
            {{ strtoupper($code) }}
        </a>
    @endforeach
</nav>
