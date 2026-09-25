@php
    $links = [
        ['label' => __('site.nav.home'), 'href' => locale_route('index'), 'active' => locale_route_is('index')],
        ['label' => __('site.nav.pricing'), 'href' => locale_route('pricing'), 'active' => locale_route_is('pricing')],
        ['label' => __('site.nav.about'), 'href' => locale_route('about'), 'active' => locale_route_is('about')],
        ['label' => __('site.nav.faq'), 'href' => locale_route('faq'), 'active' => locale_route_is('faq')],
        ['label' => __('site.nav.contact'), 'href' => locale_route('contact'), 'active' => locale_route_is('contact')],
        ['label' => __('site.nav.demo'), 'href' => __('site.demo_url'), 'active' => false, 'target' => '_blank', 'button' => true],
    ];
@endphp

<header
    class="relative py-[0.5rem] px-4 md:py-[0.79rem] md:px-8 lg:px-24 flex items-center justify-between gap-3 border-b border-gray-800 bg-black/90 sticky top-0 z-20">
    <a href="{{ locale_route('index') }}" rel="home" class="min-w-0">
        <div class="flex items-center gap-2 md:gap-3">
            <div
                class="w-10 h-10 md:w-14 md:h-14 shrink-0 flex justify-center items-center">
                <img src="{{ asset('images/logo.svg') }}" alt="{{ __('site.brand') }}" width="40" height="40" class="h-7 w-7 md:h-10 md:w-10 object-contain" decoding="async">
            </div>
            <span class="truncate text-xl sm:text-2xl md:text-3xl font-bold gradient-text tracking-wide">{{ config('app.name') }}</span>
        </div>
    </a>
    <nav class="hidden md:flex items-center gap-4 lg:gap-8 text-base lg:text-lg" aria-label="{{ __('site.nav.home') }}">
        @foreach ($links as $link)
            @php $isButton = !empty($link['button']); @endphp
            <a href="{{ $link['href'] }}"
                @if(!empty($link['target'])) target="{{ $link['target'] }}" rel="noopener noreferrer" @endif
                @if($link['active'] && !$isButton) aria-current="page" @endif
                class="{{ $link['active'] && !$isButton ? 'before:absolute before:left-0 before:bottom-0 before:h-0.5 before:w-full before:bg-gradient-to-r before:from-[#7D30FA] before:to-[#FC912C] before:rounded-full' : '' }}{{ !$isButton ? ' hover:before:absolute hover:before:left-0 hover:before:bottom-0 hover:before:h-0.5 hover:before:w-full hover:before:bg-gradient-to-r hover:before:from-[#7D30FA] hover:before:to-[#FC912C] hover:before:rounded-full' : '' }} relative {{ $isButton ? ' btn-gradient px-4 py-1 rounded-md font-bold' : '' }}">
                {{ $link['label'] }}
            </a>
        @endforeach
        <x-language-switcher />
    </nav>

    <div class="flex shrink-0 items-center gap-3 md:hidden">
        <x-language-switcher />
        <button type="button" aria-label="{{ __('site.nav.menu') }}" id="menu-toggler"
            class="flex flex-col items-stretch text-center justify-between gap-1.5 rounded-md cursor-pointer">
            <span class="w-6 h-0.5 bg-white transform transition"></span>
            <span class="w-6 h-0.5 bg-white transform transition"></span>
            <span class="w-6 h-0.5 bg-white transform transition "></span>
        </button>
        <div id="mobile-menu" class="hidden fixed inset-x-0 top-[3.71rem] bottom-0 z-30 bg-black py-2">
            <nav class="flex flex-col items-stretch text-center h-full space-y-2 text-lg px-2 pt-4">
                @foreach ($links as $link)
                    <a href="{{ $link['href'] }}"
                        @if(!empty($link['target'])) target="{{ $link['target'] }}" rel="noopener noreferrer" @endif
                        @if($link['active'] && empty($link['button'])) aria-current="page" @endif
                        class="{{ $link['active'] && empty($link['button']) ? 'before:absolute before:left-0 before:bottom-0 before:h-0.5 before:w-full before:bg-gradient-to-r before:from-[#7D30FA] before:to-[#FC912C] before:rounded-full' : '' }}{{ empty($link['button']) ? 'hover:before:absolute hover:before:left-0 hover:before:bottom-0 hover:before:h-0.5 hover:before:w-full hover:before:bg-gradient-to-r hover:before:from-[#7D30FA] hover:before:to-[#FC912C] hover:before:rounded-full' : '' }} relative {{ !empty($link['button']) ? ' btn-gradient px-4 py-1 rounded-md font-bold' : '' }}">{{ $link['label'] }}</a>
                @endforeach
            </nav>
        </div>
    </div>
</header>
