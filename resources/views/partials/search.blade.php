@php
    use App\Support\SearchIndex;
    use App\Support\SearchPresenter;

    $searchLocale = app()->getLocale();
    $searchIndexUrl = asset('search/'.$searchLocale.'.json').'?v='.SearchIndex::version($searchLocale);
@endphp
<dialog id="site-search" class="search-dialog"
    aria-labelledby="site-search-title"
    data-search-index="{{ $searchIndexUrl }}"
    data-search-locale="{{ $searchLocale }}"
    data-search-page="{{ locale_route('search') }}"
    data-search-track="{{ route('search.track') }}"
    data-search-labels="{{ json_encode(SearchPresenter::labels(), JSON_UNESCAPED_UNICODE) }}"
    data-search-rules="{{ json_encode(SearchPresenter::rules(), JSON_UNESCAPED_UNICODE) }}">
    <div class="search-panel" role="document">
        <h2 id="site-search-title" class="sr-only">{{ __('search.label') }}</h2>
        <form class="search-head" method="GET" action="{{ locale_route('search') }}" role="search" data-search-form>
            <x-icon name="search" class="search-head-icon" />
            <input type="search" name="q" id="site-search-input" class="search-input"
                role="combobox" aria-expanded="false" aria-controls="site-search-list" aria-autocomplete="list"
                aria-label="{{ __('search.field') }}" placeholder="{{ __('search.placeholder') }}"
                autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false"
                enterkeyhint="search" maxlength="{{ config('search.max_chars') }}" data-search-input>
            <button type="button" class="search-clear" aria-label="{{ __('search.clear') }}" data-search-clear hidden>
                <x-icon name="xmark" />
            </button>
            <button type="button" class="search-close" data-search-close>
                <span class="sr-only">{{ __('search.close') }}</span>
                <kbd aria-hidden="true">Esc</kbd>
            </button>
        </form>
        <div class="search-body">
            <div id="site-search-list" role="listbox" aria-label="{{ __('search.results') }}" data-search-list>
                <div class="search-state search-state-initial" data-search-initial>
                    <p class="search-state-title">{{ __('search.initial.title') }}</p>
                    <p class="search-group-title">{{ __('search.initial.categories') }}</p>
                    <div class="search-chips">
                        @foreach (SearchPresenter::categories() as $category)
                            <a href="{{ $category['url'] }}" class="search-chip" role="option" aria-selected="false" data-search-option>
                                <x-icon :name="$category['icon']" /> {{ $category['label'] }}
                            </a>
                        @endforeach
                    </div>
                    <p class="search-group-title">{{ __('search.initial.popular') }}</p>
                    <div class="search-chips">
                        @foreach (__('search.popular_terms') as $term)
                            <button type="button" class="search-chip" role="option" aria-selected="false" data-search-option data-search-fill="{{ $term }}">{{ $term }}</button>
                        @endforeach
                    </div>
                    <div data-search-recent hidden>
                        <p class="search-group-title">{{ __('search.initial.recent') }} <button type="button" class="search-link-button" data-search-clear-recent>{{ __('search.initial.clear_recent') }}</button></p>
                        <div class="search-chips" data-search-recent-list></div>
                    </div>
                </div>
                <div data-search-results hidden></div>
                <div class="search-state" data-search-empty hidden>
                    <p class="search-state-title" data-search-empty-title></p>
                    <p class="search-state-text">{{ __('search.empty.text') }}</p>
                    <div class="search-chips">
                        <a href="{{ locale_route('contact') }}" class="search-chip" role="option" aria-selected="false" data-search-option><x-icon name="envelope" /> {{ __('search.empty.contact') }}</a>
                        <a href="{{ locale_route('faq') }}" class="search-chip" role="option" aria-selected="false" data-search-option><x-icon name="question-circle" /> {{ __('search.empty.help') }}</a>
                    </div>
                </div>
                <div class="search-state" data-search-error hidden>
                    <p class="search-state-title">{{ __('search.error.title') }}</p>
                    <p class="search-state-text">{{ __('search.error.text') }}</p>
                    <div class="search-chips">
                        <a href="{{ locale_route('search') }}" class="search-chip" role="option" aria-selected="false" data-search-option data-search-error-link><x-icon name="search" /> {{ __('search.error.link') }}</a>
                    </div>
                </div>
            </div>
            <p class="sr-only" role="status" aria-live="polite" data-search-status></p>
        </div>
        <div class="search-foot" aria-hidden="true">
            <span><kbd>↑</kbd><kbd>↓</kbd> {{ __('search.shortcuts.navigate') }}</span>
            <span><kbd>↵</kbd> {{ __('search.shortcuts.open') }}</span>
            <span><kbd>Esc</kbd> {{ __('search.shortcuts.close') }}</span>
        </div>
    </div>
</dialog>
