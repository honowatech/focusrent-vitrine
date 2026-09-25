@php
    $socials = [
        ['label' => 'facebook', 'href' => 'https://facebook.com/Tech.Honowa', 'aria' => __('site.footer.social_facebook')],
        ['label' => 'twitter', 'href' => 'https://twitter.com/honowa6', 'aria' => __('site.footer.social_twitter')],
        ['label' => 'linkedin', 'href' => 'https://cm.linkedin.com/company/honowa-technologies', 'aria' => __('site.footer.social_linkedin')],
    ];

    $links = [
        ['label' => __('site.nav.home'), 'href' => locale_route('index')],
        ['label' => __('site.nav.pricing'), 'href' => locale_route('pricing')],
        ['label' => __('site.nav.about'), 'href' => locale_route('about')],
        ['label' => __('site.nav.contact'), 'href' => locale_route('contact')],
        ['label' => __('site.nav.faq'), 'href' => locale_route('faq')],
    ];
@endphp

<footer class="bg-black/90 text-gray-300 py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <p class="mb-4">{{ __('site.footer.blurb') }}</p>
                    <div class="flex space-x-4">
                        @foreach ($socials as $social)
                            <a href="{{ $social['href'] }}" aria-label="{{ $social['aria'] }}" class="hover:text-white" rel="noopener noreferrer" target="_blank"><x-icon :name="$social['label']" /></a>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h2 class="text-white text-lg font-semibold mb-4">{{ __('site.footer.quick_links') }}</h2>
                    <ul class="space-y-2">
                        @foreach ($links as $link)
                            <li><a href="{{ $link['href'] }}" class="hover:text-white">{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h2 class="text-white text-lg font-semibold mb-4">{{ __('site.footer.resources') }}</h2>
                    <ul class="space-y-2">
                        <li><a href="{{ locale_route('index') }}#temoignages" class="hover:text-white">{{ __('site.footer.testimonials') }}</a></li>
                        <li><a href="{{ locale_route('faq') }}" class="hover:text-white">{{ __('site.nav.faq') }}</a></li>
                        <li><a href="{{ locale_route('llms') }}" class="hover:text-white">llms.txt</a></li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-white text-lg font-semibold mb-4">{{ __('site.footer.contact') }}</h2>
                    <ul class="space-y-2">
                        <li><x-icon name="map-marker-alt" class="mr-2" />{{ __('site.footer.city') }}</li>
                        <li><x-icon name="phone" class="mr-2" />{{ __('site.phone') }}</li>
                        <li><x-icon name="envelope" class="mr-2" /><a href="mailto:{{ __('site.email') }}" class="hover:text-white">{{ __('site.email') }}</a></li>
                        <li><a href="{{ __('site.whatsapp_base') }}" target="_blank" rel="noopener noreferrer" class="flex hover:text-white items-center"><x-icon name="whatsapp" class="mr-2" /> {{ __('site.footer.whatsapp') }}</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-12 pt-8 text-sm text-center">
                <p>© {{ date('Y') }} Focus Rent. {{ __('site.footer.rights') }} <a href="https://honowa.com/home" class="text-[#FC912C]">Honowa Technologies</a></p>
                <p class="mt-2">
                    <a href="{{ locale_route('terms') }}" class="hover:text-white mr-4">{{ __('site.footer.terms') }}</a>
                    <a href="{{ locale_route('privacy') }}" class="hover:text-white">{{ __('site.footer.privacy') }}</a>
                </p>
            </div>
        </div>
    </footer>
