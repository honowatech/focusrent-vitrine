<x-layout>
    <x-header />

    <main id="main-content">
    <section id="contact"
        class="py-14 px-4 md:px-20 flex flex-col items-center bg-gradient-to-br from-[#1A0929] to-[#3C206B]">
        <h1 class="w-full min-w-0 text-3xl md:text-4xl font-bold mb-8 gradient-text text-center">{{ __('contact.title') }}</h1>
        <p class="w-full min-w-0 max-w-3xl text-center">
            <x-icon name="phone" class="text-orange-400/90 mr-1" />{{ __('site.phone') }} &nbsp;|&nbsp; <x-icon
                name="whatsapp" class="text-orange-400/90 mr-1" /><a
                href="{{ __('site.whatsapp_base') }}?text={{ rawurlencode(__('contact.whatsapp_message')) }}">{{ __('contact.whatsapp') }}
                <x-icon name="external-link" class="ml-1" /></a> &nbsp;|&nbsp; <x-icon
                name="envelope" class="text-orange-400/90 mr-1" /><a
                href="mailto:{{ __('site.email') }}">{{ __('site.email') }}</a>
        </p>
        <div class="text-gray-300 text-center mt-4 max-w-xl">
            <p class="text-xs mt-2 text-gray-500">{{ __('contact.hint') }}</p>
        </div>
        @if (session('success'))
            <p class="mt-4 text-green-400" role="status">{{ session('success') }}</p>
        @endif
        @if (session('error'))
            <p class="mt-4 text-red-400" role="alert">{{ session('error') }}</p>
        @endif
        @error('g-recaptcha-response')
            <p class="mt-4 text-red-400" role="alert">{{ $message }}</p>
        @enderror
        <form id="form" class="rounded-lg shadow-xl max-w-xl w-full p-8 grid grid-cols-1 gap-6" method="POST"
            action="{{ locale_route('contact.send') }}">
            @csrf
            <div>
                <label class="sr-only" for="name">{{ __('contact.fields.name') }}</label>
                <input type="text" id="name" name="name" placeholder="{{ __('contact.fields.name') }} *"
                    class="w-full border border-orange-400/90 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 text-white" value="{{ old('name') }}">
                @error('name')
                    <p class="text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="sr-only" for="email">{{ __('contact.fields.email') }}</label>
                <input type="email" id="email" name="email" placeholder="{{ __('contact.fields.email') }}"
                    class="w-full border border-orange-400/90 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 text-white" value="{{ old('email') }}">
                @error('email')
                    <p class="text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="sr-only" for="phone">{{ __('contact.fields.phone') }}</label>
                <input type="tel" id="phone" name="phone" placeholder="{{ __('contact.fields.phone') }} *"
                    class="w-full border border-orange-400/90 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 text-white" value="{{ old('phone') }}">
                @error('phone')
                    <p class="text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="sr-only" for="message">{{ __('contact.fields.message') }}</label>
                <textarea id="message" name="message" placeholder="{{ __('contact.fields.message') }} *" rows="3"
                    class="w-full border border-orange-400/90 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 text-white">{{ old('message') }}</textarea>
                @error('message')
                    <p class="text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit"
                @if(\App\Rules\Recaptcha::enabled())
                    data-sitekey="{{ config('services.recaptcha.key') }}" data-action='submit' data-callback='onSubmit'
                    data-error-callback='onRecaptchaError'
                    class="g-recaptcha btn-gradient font-bold py-3 rounded-full text-xl mt-2 w-full cursor-pointer"
                @else
                    class="btn-gradient font-bold py-3 rounded-full text-xl mt-2 w-full cursor-pointer"
                @endif>{{ __('contact.submit') }}</button>
        </form>
        <div class="text-gray-300 text-center mt-4 max-w-lg">
            <p class="text-xs mt-2 text-gray-500">{{ __('contact.hint') }}</p>
        </div>
    </section>
    </main>

    <x-footer />
    @push('head')
        <link rel="preconnect" href="https://www.google.com">
        <link rel="preconnect" href="https://www.gstatic.com" crossorigin>
    @endpush
    @push('scripts')
        @if(\App\Rules\Recaptcha::enabled())
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
            <script>
                function onSubmit(token) {
                    document.getElementById("form").submit();
                }

                // Si le widget échoue (domaine non autorisé, réseau), on laisse le
                // formulaire partir : la validation serveur affichera une erreur claire
                // plutôt qu'un clic sans effet.
                function onRecaptchaError() {
                    document.getElementById("form").submit();
                }
            </script>
        @endif
    @endpush
</x-layout>
