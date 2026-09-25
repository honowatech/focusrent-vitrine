@props(['file'])

<x-layout>
    <x-header />
    <main id="main-content" class="py-14 px-4 md:px-24 max-w-5xl mx-auto text-gray-200">
        <h1 class="text-4xl font-bold mb-8 gradient-text text-center">{{ __($file.'.title') }}</h1>
        <p class="text-center text-gray-300 mb-10">{{ __($file.'.updated') }}</p>

        <div class="space-y-12">
            @if (is_array(__($file.'.intro')))
                <div>
                    @foreach (__($file.'.intro') as $paragraph)
                        <p @class(['mt-4' => ! $loop->first])>{{ $paragraph }}</p>
                    @endforeach
                </div>
            @else
                <p>{{ __($file.'.intro') }}</p>
            @endif

            @foreach (__($file.'.sections') as $section)
                <section>
                    <h2 class="text-2xl font-bold mb-4 gradient-text">{{ $section['title'] }}</h2>
                    @foreach ($section['blocks'] as $block)
                        @if (($block['type'] ?? 'p') === 'h3')
                            <h3 class="text-xl font-semibold mb-2">{{ $block['text'] }}</h3>
                        @elseif (($block['type'] ?? 'p') === 'ul')
                            <ul class="list-disc pl-6 space-y-2 mb-4">
                                @foreach ($block['items'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p @class(['mb-4' => ! $loop->last])>{{ $block['text'] }}</p>
                        @endif
                    @endforeach
                </section>
            @endforeach
        </div>
    </main>
    <x-footer />
</x-layout>
