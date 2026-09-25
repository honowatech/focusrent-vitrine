<x-layout>
    <x-header />

    <main id="main-content">
        <nav aria-label="{{ __('pricing.title') }}" class="px-4 pt-8 md:px-24">
            <ol class="mx-auto flex max-w-6xl flex-wrap items-center gap-2 text-sm text-gray-400">
                <li><a href="{{ locale_route('index') }}" class="hover:text-white">{{ __('site.nav.home') }}</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-white">{{ __('seo.pricing.nav') }}</li>
            </ol>
        </nav>

        <section class="mx-auto max-w-6xl px-4 py-10 md:px-24">
            <h1 class="mb-6 text-center text-3xl font-bold gradient-text md:text-5xl">{{ __('pricing.title') }}</h1>
            <p class="mx-auto max-w-3xl text-center text-lg text-gray-200">{{ __('pricing.lead') }}</p>
        </section>

        <section aria-labelledby="plans-title" class="bg-gradient-to-br from-[#21153D] to-black px-4 py-14 md:px-24">
            <h2 id="plans-title" class="mb-10 text-center text-3xl font-bold gradient-text">{{ __('pricing.plans_title') }}</h2>
            <x-pricing-plans />
            <p class="mt-8 text-center text-xs text-gray-400">{{ __('home.pricing.note') }}</p>
        </section>

        <section aria-labelledby="compare-title" class="bg-focus-light px-4 py-14 md:px-24">
            <div class="mx-auto max-w-6xl">
                <h2 id="compare-title" class="mb-8 text-center text-3xl font-bold gradient-text">{{ __('pricing.compare_title') }}</h2>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[40rem] border-collapse text-left text-sm text-gray-200">
                        <caption class="sr-only">{{ __('pricing.compare_caption') }}</caption>
                        <thead>
                            <tr class="border-b border-gray-800">
                                <th scope="col" class="px-4 py-3 font-semibold text-white">{{ __('pricing.compare_feature') }}</th>
                                @foreach (__('home.pricing.plans') as $plan)
                                <th scope="col" class="px-4 py-3 font-semibold text-white">
                                    <a href="#{{ strtolower($plan['name']) }}" class="hover:text-[#FC912C]">{{ $plan['name'] }}</a>
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-gray-800">
                                <th scope="row" class="px-4 py-3 font-semibold text-white">{{ __('pricing.row_price') }}</th>
                                @foreach (__('home.pricing.plans') as $plan)
                                <td class="px-4 py-3">{{ $plan['price'] }} {{ __('home.pricing.currency') }} {{ __('home.pricing.period') }}</td>
                                @endforeach
                            </tr>
                            <tr class="border-b border-gray-800">
                                <th scope="row" class="px-4 py-3 font-semibold text-white">{{ __('pricing.row_units') }}</th>
                                @foreach (__('home.pricing.plans') as $plan)
                                <td class="px-4 py-3">{{ $plan['units'] }}</td>
                                @endforeach
                            </tr>
                            <tr class="border-b border-gray-800">
                                <th scope="row" class="px-4 py-3 font-semibold text-white">{{ __('pricing.row_users') }}</th>
                                @foreach (__('home.pricing.plans') as $plan)
                                <td class="px-4 py-3">{{ $plan['users'] }}</td>
                                @endforeach
                            </tr>
                            @foreach (__('home.pricing.plans')[0]['features'] as $feature)
                            <tr class="border-b border-gray-800">
                                <th scope="row" class="px-4 py-3 font-semibold text-white">{{ $feature }}</th>
                                @foreach (__('home.pricing.plans') as $plan)
                                <td class="px-4 py-3">{{ __('pricing.included') }}</td>
                                @endforeach
                            </tr>
                            @endforeach
                            <tr>
                                <th scope="row" class="px-4 py-3 font-semibold text-white">{{ __('pricing.row_payment') }}</th>
                                @foreach (__('home.pricing.plans') as $plan)
                                <td class="px-4 py-3">{{ __('pricing.payment_value') }}</td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section aria-labelledby="billing-title" class="mx-auto max-w-3xl px-4 py-14 md:px-24">
            <h2 id="billing-title" class="mb-6 text-center text-3xl font-bold gradient-text">{{ __('pricing.billing_title') }}</h2>
            <ul class="list-disc space-y-2 pl-6 text-gray-200">
                @foreach (__('pricing.billing') as $line)
                <li>{{ $line }}</li>
                @endforeach
            </ul>
        </section>

        <section id="faq" aria-labelledby="pricing-faq-title" class="bg-[#130822] px-4 py-14 md:px-24">
            <h2 id="pricing-faq-title" class="mb-10 text-center text-3xl font-bold gradient-text">{{ __('pricing.faq_title') }}</h2>
            <div class="mx-auto max-w-3xl space-y-6">
                @foreach (__('pricing.faq') as $item)
                <article>
                    <h3 class="flex items-center text-lg font-semibold"><x-icon name="question-circle" class="mr-2 text-orange-400" />{{ $item['q'] }}</h3>
                    <div class="faq-answer">{{ $item['a'] }}</div>
                </article>
                @endforeach
            </div>
        </section>

        <section aria-labelledby="pricing-cta-title" class="px-4 py-16">
            <div class="mx-auto max-w-4xl rounded-xl bg-focus-blue p-8 text-center text-white shadow-lg">
                <h2 id="pricing-cta-title" class="mb-4 text-3xl font-bold">{{ __('pricing.cta_title') }}</h2>
                <p class="mb-8">{{ __('pricing.cta_text') }}</p>
                <div class="flex flex-col justify-center gap-4 sm:flex-row">
                    <a href="{{ __('site.whatsapp_base') }}?text={{ rawurlencode(__('pricing.cta_whatsapp')) }}" target="_blank" rel="noopener noreferrer" class="rounded-full bg-white px-4 py-2 font-semibold text-[#7D30FA] transition duration-300 hover:bg-transparent hover:text-white hover:ring-2 hover:ring-white">{{ __('pricing.cta_demo') }}</a>
                    <a href="{{ locale_route('contact') }}" class="rounded-full border-2 border-white px-4 py-2 font-semibold text-white transition duration-300 hover:bg-white hover:text-[#7D30FA]">{{ __('pricing.cta_contact') }}</a>
                </div>
            </div>
        </section>

        <section aria-labelledby="related-title" class="mx-auto max-w-3xl px-4 pb-16">
            <h2 id="related-title" class="mb-4 text-center text-xl font-bold">{{ __('pricing.related') }}</h2>
            <ul class="flex flex-wrap justify-center gap-4 text-gray-300">
                <li><a href="{{ locale_route('faq') }}" class="hover:text-white">{{ __('site.nav.faq') }}</a></li>
                <li><a href="{{ locale_route('contact') }}" class="hover:text-white">{{ __('site.nav.contact') }}</a></li>
                <li><a href="{{ locale_route('terms') }}" class="hover:text-white">{{ __('site.footer.terms') }}</a></li>
                <li><a href="{{ locale_route('about') }}" class="hover:text-white">{{ __('site.nav.about') }}</a></li>
            </ul>
        </section>
    </main>

    <x-footer />
</x-layout>
