<x-layout>
    <x-header />

    <main id="main-content">
    <section class="py-16 bg-focus-light border-b border-gray-200/20">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ __('about.hero.title') }}</h1>
                <p class="text-xl text-white/60 mb-8">{{ __('about.hero.subtitle') }}</p>
                <div class="flex flex-wrap justify-center gap-4">
                    @foreach (__('about.hero.badges') as $badge)
                    <span class="px-4 py-2 bg-transparent text-white rounded-full text-sm font-medium shadow-sm">
                        <x-icon name="check-circle" class="mr-2 text-[#7D30FA]" />
                        {{ $badge }}
                    </span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 border-b border-gray-200/20">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-3xl font-bold mb-10 text-center">{{ __('about.timeline.title') }}</h2>
                <div class="space-y-12">
                    @foreach (__('about.timeline.items') as $item)
                    <div class="timeline-item {{ ! $loop->last ? 'pb-12' : '' }}">
                        <h3 class="font-semibold text-xl mb-2">{{ $item['title'] }}</h3>
                        <p class="text-white/60">{{ $item['text'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 bg-focus-light border-b border-gray-200/20">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-3xl font-bold mb-10 text-center">{{ __('about.mission.title') }}</h2>
                <div class="bg-gray-200/20 rounded-xl shadow-lg p-8">
                    <div class="flex flex-col md:flex-row items-center">
                        <div class="md:w-1/3 mb-6 md:mb-0 flex justify-center">
                            <div class="bg-[#7D30FA] py-6 px-8 rounded-full flex items-center justify-center">
                                <x-icon name="building" class="text-6xl" />
                            </div>
                        </div>
                        <div class="md:w-2/3 md:pl-8">
                            <h3 class="text-2xl font-semibold mb-4">{{ __('about.mission.heading') }}</h3>
                            <p class="text-white/60 mb-4">{{ __('about.mission.p1') }}</p>
                            <p class="text-white/60">{{ __('about.mission.p2') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 border-b border-gray-200/20">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold mb-12 text-center">{{ __('about.values.title') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach (__('about.values.items') as $item)
                <div class="value-card {{ $loop->index === 1 ? 'bg-[#c76810]' : 'bg-[#261240]' }} rounded-lg shadow-md p-6">
                    <div class="mb-4">
                        <x-icon :name="$item['icon']" class="text-3xl" />
                    </div>
                    <h3 class="text-xl font-semibold mb-2">{{ $item['title'] }}</h3>
                    <p class="{{ $loop->index === 1 ? '' : 'text-white/60' }}">{{ $item['text'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-16 bg-focus-dark text-white border-b border-gray-200/20">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl font-bold mb-6">{{ __('about.vision.title') }}</h2>
                <p class="text-xl mb-8">{{ __('about.vision.lead') }}</p>
                <p class="mb-8">{{ __('about.vision.text') }}</p>
            </div>
        </div>
    </section>

    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto bg-focus-blue rounded-xl shadow-lg p-8 text-white text-center">
                <h2 class="text-3xl font-bold mb-4">{{ __('about.cta.title') }}</h2>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="{{ __('site.whatsapp_base') }}?text={{ rawurlencode(__('about.cta.whatsapp')) }}" target="_blank" rel="noopener noreferrer" class="bg-white text-[#7D30FA] rounded-full px-4 py-2 hover:bg-transparent hover:text-[#7D30FA] transition duration-300 hover:ring-2 hover:ring-[#7D30FA]">{{ __('about.cta.demo') }}</a>
                    <a href="{{ locale_route('pricing') }}"
                        class="border-2 border-white text-white rounded-full px-4 py-2 hover:bg-white hover:text-[#7D30FA] transition duration-300">{{ __('about.cta.pricing') }}</a>
                </div>
            </div>
        </div>
    </section>
    </main>

    <x-footer />
</x-layout>
