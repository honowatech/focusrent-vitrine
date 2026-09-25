<x-layout>
    <x-header />

    <main id="main-content">
    <section id="faq" class="py-14 px-4 md:px-24 bg-gradient-to-br from-black via-[#230f39] to-[#231021]">
        <h1 class="text-3xl md:text-4xl font-bold text-center mb-10 gradient-text">{{ __('faq.title') }}</h1>
        <div class="max-w-3xl mx-auto space-y-6">
            @foreach (__('faq.items') as $item)
            <article>
                <h2 class="font-semibold flex items-center text-lg"><x-icon name="question-circle" class="mr-2 text-orange-400" />{{ $item['q'] }}</h2>
                <div class="faq-answer">{{ $item['a'] }}</div>
            </article>
            @endforeach
        </div>
    </section>
    </main>

    <x-footer />
</x-layout>
