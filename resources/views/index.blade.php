<x-layout>
    <x-header />

    <main id="main-content">
    <section class="relative flex items-center text-right overflow-hidden">
        <img src="{{ asset('images/hero-bg.webp') }}" alt="" width="1600" height="307" fetchpriority="high" decoding="async" class="absolute inset-0 h-full w-full object-cover" aria-hidden="true">
        <div class="relative py-16 md:py-24 bg-black/50 w-full min-w-0 px-4 md:px-8 lg:px-24 flex flex-col items-end justify-between">
            <h1 class="w-full min-w-0 max-w-7xl text-4xl md:text-6xl font-extrabold mb-8">{{ __('home.hero.title_before') }} <span class="text-[#FC912C]">{{ __('home.hero.title_highlight') }}</span>{{ __('home.hero.title_after') }}</h1>
            <p class="w-full min-w-0 max-w-2xl text-xl mb-6 text-gray-400">
                {{ __('home.hero.subtitle') }}
            </p>
            <a href="https://apps.focusrent.cm/register" target="_blank" rel="noopener noreferrer"
                class="btn-gradient px-8 py-3 rounded-full font-bold text-xl shadow-lg inline-block mb-2">{{ __('home.hero.cta') }}</a>
        </div>
    </section>

    <section id="pourquoi" class="py-14 px-4 max-w-6xl mx-auto">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-6 gradient-text">{{ __('home.why.title') }}
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-16 mt-10">
            @foreach (__('home.why.items') as $item)
            <div class="flex flex-col items-center text-center space-y-2">
                <span class="why-icon"><x-icon :name="$item['icon']" /></span>
                <h3 class="text-xl font-bold">{{ $item['title'] }}</h3>
                <p>{{ $item['text'] }}</p>
            </div>
            @endforeach
        </div>
    </section>

    <section id="fonctionnalites" class="py-14 px-4 md:px-24 bg-gradient-to-br from-[#21153D] via-[#140925] to-black">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-10 gradient-text">{{ __('home.features.title') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-16 max-w-6xl mx-auto">
            @foreach (__('home.features.items') as $item)
            <div class="flex flex-col items-center text-center space-y-2">
                <span class="feature-icon"><x-icon :name="$item['icon']" /></span>
                <h3 class="font-semibold text-lg">{{ $item['title'] }}</h3>
                <p>{{ $item['text'] }}</p>
            </div>
            @endforeach
        </div>
        <div class="flex justify-center mt-10">
        <a href="{{ locale_route('pricing') }}"
            class="btn-gradient px-8 py-3 rounded-full font-bold text-xl shadow-lg inline-block mb-2">{{ __('home.features.cta') }}</a>
        </div>
    </section>

    <section class="py-14 px-4 md:px-24 bg-[#1D142B] flex flex-col items-center justify-between">
        <h2 class="text-3xl md:text-4xl font-bold gradient-text mb-8 text-center">{{ __('home.gallery.title') }}</h2>
        <div class="w-full max-w-2xl px-2 basis-2xl">
            <div class="relative overflow-hidden h-full">
                <div class="carousel-inner relative w-72 mx-auto aspect-[720/1599]">
                    @foreach (__('home.gallery.slides') as $slide)
                    <div class="carousel-item transition duration-300 {{ $loop->first ? 'active' : '' }}">
                        <img class="absolute inset-0 h-full w-full object-contain rounded-xl shadow-2xl" alt="{{ $slide['alt'] }}"
                            src="{{ asset($slide['src']) }}" width="720" height="1599" loading="lazy" decoding="async" />
                    </div>
                    @endforeach
                </div>
                <button class="absolute top-0 left-1 z-30 flex items-center justify-center w-10 h-full text-2xl"
                    type="button" aria-label="{{ __('home.gallery.prev') }}" id="prev-btn">
                    <svg class="size-10 text-white cursor-pointer transition-colors duration-150 hover:bg-gray-100 dark:hover:bg-gray-800 p-2 rounded-full"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                </button>
                <button class="absolute top-0 right-1 z-30 flex items-center justify-center w-10 h-full text-2xl"
                    type="button" aria-label="{{ __('home.gallery.next') }}" id="next-btn">
                    <svg class="size-10 text-white cursor-pointer transition-colors duration-150 hover:bg-gray-100 dark:hover:bg-gray-800 p-2 rounded-full"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                        </path>
                    </svg>
                </button>
            </div>
        </div>

        <a href="{{ __('site.demo_url') }}" target="_blank" rel="noopener noreferrer"
            class="btn-gradient px-7 py-3 rounded-full font-semibold text-lg shadow-lg mt-4 mx-auto">{{ __('home.gallery.demo') }}</a>
    </section>

    <section id="tarifs" class="py-14 px-4 md:px-24 bg-gradient-to-br from-[#21153D] to-black">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-10 gradient-text">{{ __('home.pricing.title') }}</h2>
        <p class="text-lg text-gray-200 text-center mb-8">{{ __('home.pricing.subtitle') }}</p>
        <x-pricing-plans />
        <p class="text-xs text-gray-400 text-center mt-8">{{ __('home.pricing.note') }}</p>
        <p class="text-center mt-4"><a href="{{ locale_route('pricing') }}" class="font-semibold text-[#FC912C] hover:text-white">{{ __('pricing.home_more') }}</a></p>
    </section>

    <section id="temoignages" class="py-14 px-4 bg-[#130822]">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-10 gradient-text">{{ __('home.testimonials.title') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10 max-w-6xl mx-auto">
            @foreach (__('home.testimonials.items') as $item)
            <blockquote class="testimonial p-6 flex flex-col items-center text-center">
                <p class="mb-2 text-lg font-medium">« {{ $item['quote'] }} »</p>
                <footer class="font-bold gradient-text">{{ $item['author'] }}</footer>
            </blockquote>
            @endforeach
        </div>
    </section>
    </main>

    <x-footer />
</x-layout>
