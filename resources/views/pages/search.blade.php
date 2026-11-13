@php
    use App\Support\SearchEngine;
    use App\Support\SearchPresenter;

    $searchUrl = locale_route('search');
    $filterUrl = fn (string $group = '') => $searchUrl.'?'.http_build_query(array_filter(['q' => $query, 'type' => $group ?: null, 't' => $token ?: null]));
    $resultUrl = fn (array $document, int $position) => $token !== ''
        ? route('search.go', ['t' => $token, 'id' => $document['id'], 'p' => $position, 'l' => app()->getLocale()])
        : $document['url'];
@endphp
<x-layout :title="($query !== '' ? $query.' | ' : '').__('search.page.title').' | Focus Rent'" :description="__('seo.search.description')" robots="noindex, follow">
    <x-header />

    <main id="main-content" class="search-page">
        <section class="search-page-head">
            <h1 class="search-page-title gradient-text">{{ __('search.page.heading') }}</h1>
            <p class="search-page-intro">{{ __('search.page.intro') }}</p>
            <form method="GET" action="{{ $searchUrl }}" role="search" class="search-page-form">
                <label for="search-page-input" class="sr-only">{{ __('search.field') }}</label>
                <x-icon name="search" class="search-head-icon" />
                <input type="search" id="search-page-input" name="q" value="{{ $query }}" class="search-input"
                    placeholder="{{ __('search.placeholder') }}" autocomplete="off" autocapitalize="off" spellcheck="false"
                    enterkeyhint="search" maxlength="{{ $rules['max_chars'] }}" @if($query === '') autofocus @endif>
                <button type="submit" class="btn-gradient search-page-submit">{{ __('search.submit') }}</button>
            </form>
        </section>

        @if ($query === '')
            <section class="search-page-state">
                <p class="search-state-title">{{ __('search.initial.title') }}</p>
                <div class="search-chips">
                    @foreach (SearchPresenter::categories() as $category)
                        <a href="{{ $category['url'] }}" class="search-chip"><x-icon :name="$category['icon']" /> {{ $category['label'] }}</a>
                    @endforeach
                </div>
                <p class="search-group-title">{{ __('search.initial.popular') }}</p>
                <div class="search-chips">
                    @foreach (__('search.popular_terms') as $term)
                        <a href="{{ $searchUrl.'?q='.rawurlencode($term) }}" class="search-chip">{{ $term }}</a>
                    @endforeach
                </div>
            </section>
        @elseif ($tooShort)
            <section class="search-page-state">
                <p class="search-state-title" role="status">{{ __('search.status.short', ['min' => $rules['min_chars']]) }}</p>
            </section>
        @elseif ($total === 0)
            <section class="search-page-state">
                <p class="search-state-title" role="status">{{ __('search.empty.title', ['query' => $query]) }}</p>
                <p class="search-state-text">{{ __('search.empty.text') }}</p>
                <div class="search-chips">
                    <a href="{{ locale_route('contact') }}" class="search-chip"><x-icon name="envelope" /> {{ __('search.empty.contact') }}</a>
                    <a href="{{ locale_route('faq') }}" class="search-chip"><x-icon name="question-circle" /> {{ __('search.empty.help') }}</a>
                </div>
            </section>
        @else
            <p class="search-page-status" role="status">
                {{ $total === 1 ? __('search.status.one', ['query' => $query]) : __('search.status.many', ['count' => $total, 'query' => $query]) }}
            </p>

            <nav class="search-filters" aria-label="{{ __('search.page.filter') }}">
                <a href="{{ $filterUrl() }}" class="search-filter {{ $type === '' ? 'is-active' : '' }}" @if($type === '') aria-current="true" @endif>{{ __('search.page.all') }} <span>{{ $total }}</span></a>
                @foreach ($rules['groups'] as $group)
                    @if (($counts[$group] ?? 0) > 0)
                        <a href="{{ $filterUrl($group) }}" class="search-filter {{ $type === $group ? 'is-active' : '' }}" @if($type === $group) aria-current="true" @endif>{{ __('search.groups.'.$group) }} <span>{{ $counts[$group] }}</span></a>
                    @endif
                @endforeach
            </nav>

            @foreach ($groups as $group => $results)
                <section class="search-page-group" aria-labelledby="group-{{ $group }}">
                    <h2 id="group-{{ $group }}" class="search-group-title">{{ __('search.groups.'.$group) }} <span>{{ $counts[$group] }}</span></h2>
                    <ol class="search-page-list">
                        @foreach ($results as $i => $result)
                            @php $document = $result['document']; @endphp
                            <li>
                                <a href="{{ $resultUrl($document, $i + 1) }}" class="search-option">
                                    <span class="search-option-icon"><x-icon :name="SearchPresenter::icon($group)" /></span>
                                    <span class="search-option-main">
                                        @if ($document['breadcrumb'] !== [])
                                            <span class="search-option-crumb">{{ implode(' › ', $document['breadcrumb']) }}</span>
                                        @endif
                                        <span class="search-option-label">{!! SearchEngine::highlight($document['label'], $terms) !!}</span>
                                        <span class="search-option-excerpt">{!! SearchEngine::highlight(SearchEngine::snippet($document['body'] !== '' ? $document['body'] : $document['excerpt'], $terms), $terms) !!}</span>
                                        @if (! empty($document['price']))
                                            <span class="search-option-meta"><strong>{{ $document['price'] }}</strong> · {{ $document['units'] }} · {{ $document['users'] }}</span>
                                        @endif
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                    @if ($type === '' && $counts[$group] > count($results))
                        <a href="{{ $filterUrl($group) }}" class="search-see-more">{{ __('search.see_more', ['count' => $counts[$group]]) }} <x-icon name="arrow-right" /></a>
                    @endif
                </section>
            @endforeach
        @endif
    </main>

    <x-footer />
</x-layout>
