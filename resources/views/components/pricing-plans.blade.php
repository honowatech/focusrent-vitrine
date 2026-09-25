<div class="grid grid-cols-1 lg:grid-cols-3 gap-10 max-w-5xl mx-auto">
    @foreach (__('home.pricing.plans') as $plan)
    <article id="{{ strtolower($plan['name']) }}" class="price-card flex flex-col items-center p-8 {{ $loop->first ? 'selected' : '' }}">
        <h3 class="text-2xl font-bold gradient-text mb-2">{{ $plan['name'] }}</h3>
        <p class="mb-6 text-center">
            <span class="whitespace-nowrap text-3xl font-extrabold gradient-text lg:text-4xl">{{ $plan['price'] }}</span>
            <span class="ml-1 text-2xl text-gray-200">{{ __('home.pricing.currency') }}</span>
            <span class="block mt-2 text-lg text-gray-300">{{ __('home.pricing.period') }}</span>
        </p>
        <ul class="text-left text-gray-200 space-y-2 mb-6">
            <li><x-icon :name="$plan['icon']" class="text-orange-500 mr-2" /> {{ $plan['units'] }}</li>
            <li><x-icon name="folder-open" class="mr-2" /> {{ $plan['features'][0] }}</li>
            <li><x-icon name="bell" class="mr-2" /> {{ $plan['features'][1] }}</li>
            <li><x-icon name="file-invoice" class="mr-2" /> {{ $plan['features'][2] }}</li>
            <li><x-icon name="file-invoice" class="mr-2" /> {{ $plan['features'][3] }}</li>
            <li><x-icon name="message" class="mr-2" /> {{ $plan['features'][4] }}</li>
            <li><x-icon :name="$plan['multi_users'] ? 'users' : 'user'" class="mr-2" /> {{ $plan['users'] }}</li>
        </ul>
        <a href="{{ __('site.whatsapp_base') }}?text={{ rawurlencode($plan['whatsapp']) }}"
            class="btn-gradient px-6 py-2 font-bold rounded-full shadow mt-auto">{{ __('home.pricing.choose', ['name' => $plan['name']]) }}</a>
    </article>
    @endforeach
</div>
