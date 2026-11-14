/**
 * Palette de recherche du site : dialogue natif accessible, MiniSearch,
 * rendu groupé, clavier, récents et suivi anonyme. Chargée à la demande
 * par search.js, à la première ouverture ou au survol de la loupe.
 *
 * Règles communes avec la page de résultats serveur : config/search.php,
 * injecté en data-search-rules sur le <dialog>.
 */

export function setup(dialog) {
    const input = dialog.querySelector('[data-search-input]');
    const form = dialog.querySelector('[data-search-form]');
    const list = dialog.querySelector('[data-search-list]');
    const initialState = dialog.querySelector('[data-search-initial]');
    const resultsBox = dialog.querySelector('[data-search-results]');
    const emptyState = dialog.querySelector('[data-search-empty]');
    const emptyTitle = dialog.querySelector('[data-search-empty-title]');
    const errorState = dialog.querySelector('[data-search-error]');
    const errorLink = dialog.querySelector('[data-search-error-link]');
    const status = dialog.querySelector('[data-search-status]');
    const clearButton = dialog.querySelector('[data-search-clear]');
    const recentBox = dialog.querySelector('[data-search-recent]');
    const recentList = dialog.querySelector('[data-search-recent-list]');

    const labels = parseJson(dialog.dataset.searchLabels, {});
    const rules = parseJson(dialog.dataset.searchRules, {});
    const indexUrl = dialog.dataset.searchIndex;
    const pageUrl = dialog.dataset.searchPage;
    const trackUrl = dialog.dataset.searchTrack;
    const locale = dialog.dataset.searchLocale || 'fr';

    const MIN_CHARS = Number(rules.min_chars || 2);
    const MAX_CHARS = Number(rules.max_chars || 80);
    const PER_GROUP = Number(rules.per_group || 5);
    const GROUPS = Array.isArray(rules.groups) ? rules.groups : ['page'];
    const BOOST = rules.boost || { label: 5, title: 1.5, excerpt: 2, domain: 1.5, body: 1 };
    const FUZZY_MIN = Number(rules.fuzzy_min_chars || 5);
    const KNOWN_MIN = Number((rules.known_word || {}).min_chars || 4);
    const KNOWN_PREFIX_WEIGHT = Number((rules.known_word || {}).prefix_weight || 0.25);
    const SYNONYM_WEIGHT = 0.7;
    const STOP_WORDS = new Set((rules.stop_words || []).map(fold));
    const SYNONYMS = buildSynonyms(rules.synonyms || []);
    const RECENT_KEY = 'fr-search-recent-' + locale;
    const DEBOUNCE = 120;
    const SETTLE = 1500;

    let engine = null;
    let documents = [];
    let knownWords = new Set();
    let loading = null;
    let opener = null;
    let activeIndex = -1;
    let timer = null;
    let lastQuery = '';
    let pending = null;
    let requestId = 0;

    // ---------------------------------------------------------------- ouverture

    function open(from) {
        if (dialog.open) {
            return;
        }
        opener = from instanceof HTMLElement ? from : null;
        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
        } else {
            dialog.setAttribute('open', '');
        }
        document.body.classList.add('search-open');
        renderRecent();
        input.focus();
        input.select();
        prefetch();
        if (input.value.trim().length >= MIN_CHARS) {
            runSearch(input.value);
        }
    }

    function close() {
        if (!dialog.open) {
            return;
        }
        flushPending('close');
        if (typeof dialog.close === 'function') {
            dialog.close();
        } else {
            dialog.removeAttribute('open');
        }
    }

    dialog.addEventListener('close', () => {
        document.body.classList.remove('search-open');
        if (opener && document.contains(opener)) {
            opener.focus({ preventScroll: true });
        }
        opener = null;
    });

    dialog.addEventListener('cancel', (event) => {
        event.preventDefault();
        close();
    });

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            close();
        }
    });

    dialog.querySelectorAll('[data-search-close]').forEach((button) => {
        button.addEventListener('click', close);
    });

    // ---------------------------------------------------------------- saisie

    input.addEventListener('input', () => {
        clearButton.hidden = input.value === '';
        clearTimeout(timer);
        timer = setTimeout(() => runSearch(input.value), DEBOUNCE);
    });

    clearButton.addEventListener('click', () => {
        input.value = '';
        clearButton.hidden = true;
        showInitial();
        input.focus();
    });

    form.addEventListener('submit', (event) => {
        const query = input.value.trim();
        if (query.length < MIN_CHARS) {
            event.preventDefault();
            announce(format(labels.status && labels.status.short, { min: MIN_CHARS }));
            return;
        }
        // La page de résultats compte elle-même la recherche.
        pending = null;
        rememberRecent(query);
    });

    input.addEventListener('keydown', (event) => {
        const options = visibleOptions();

        switch (event.key) {
            case 'ArrowDown':
                event.preventDefault();
                setActive(options, Math.min(activeIndex + 1, options.length - 1));
                break;
            case 'ArrowUp':
                event.preventDefault();
                setActive(options, Math.max(activeIndex - 1, 0));
                break;
            case 'Home':
                if (options.length && activeIndex >= 0) {
                    event.preventDefault();
                    setActive(options, 0);
                }
                break;
            case 'End':
                if (options.length && activeIndex >= 0) {
                    event.preventDefault();
                    setActive(options, options.length - 1);
                }
                break;
            case 'Enter':
                if (activeIndex >= 0 && options[activeIndex]) {
                    event.preventDefault();
                    options[activeIndex].click();
                }
                break;
            case 'Escape':
                event.preventDefault();
                close();
                break;
            default:
                break;
        }
    });

    list.addEventListener('click', (event) => {
        const fill = event.target.closest('[data-search-fill]');
        if (fill) {
            input.value = fill.dataset.searchFill;
            clearButton.hidden = false;
            runSearch(input.value);
            input.focus();
            return;
        }

        const option = event.target.closest('a[data-search-option]');
        if (!option) {
            return;
        }

        if (option.dataset.searchResult) {
            const query = lastQuery;
            flushPending('click', {
                clicked_id: option.dataset.searchResult,
                clicked_type: option.dataset.searchType,
                clicked_position: Number(option.dataset.searchPosition || 0),
            });
            rememberRecent(query);
        } else if (option.dataset.searchSeeAll) {
            pending = null;
            rememberRecent(lastQuery);
        }

        close();
    });

    list.addEventListener('mousemove', (event) => {
        const option = event.target.closest('[data-search-option]');
        if (!option) {
            return;
        }
        const options = visibleOptions();
        const index = options.indexOf(option);
        if (index !== -1 && index !== activeIndex) {
            setActive(options, index, false);
        }
    });

    const clearRecent = dialog.querySelector('[data-search-clear-recent]');
    if (clearRecent) {
        clearRecent.addEventListener('click', () => {
            storage('remove');
            renderRecent();
            input.focus();
        });
    }

    window.addEventListener('pagehide', () => flushPending('leave'));
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            flushPending('leave');
        }
    });

    // ---------------------------------------------------------------- chargement

    function prefetch() {
        if (loading) {
            return loading;
        }
        loading = Promise.all([
            import('minisearch'),
            fetch(indexUrl, { credentials: 'omit' }).then((response) => {
                if (!response.ok) {
                    throw new Error('index ' + response.status);
                }
                return response.json();
            }),
        ]).then(([module, index]) => {
            const MiniSearch = module.default || module;
            documents = Array.isArray(index.documents) ? index.documents : [];
            knownWords = collectWords(documents);
            engine = new MiniSearch({
                // Même découpage que SearchText::tokens() côté PHP.
                tokenize: (text) => fold(text).split(/[^a-z0-9]+/).filter(Boolean),
                fields: ['label', 'title', 'excerpt', 'domain', 'body'],
                storeFields: ['id', 'type', 'title', 'section', 'label', 'url', 'breadcrumb', 'excerpt', 'body', 'domain', 'price', 'units', 'users'],
                processTerm: processTerm,
                searchOptions: {
                    boost: BOOST,
                    combineWith: 'AND',
                },
            });
            engine.addAll(documents);
            return engine;
        }).catch((error) => {
            loading = null;
            throw error;
        });
        return loading;
    }

    function processTerm(term) {
        const folded = fold(term);
        if (!folded || STOP_WORDS.has(folded)) {
            return null;
        }
        return stem(folded);
    }

    function collectWords(docs) {
        const words = new Set();
        docs.forEach((doc) => {
            ['label', 'title', 'excerpt', 'domain', 'body'].forEach((field) => {
                tokenize(doc[field] || '').forEach((word) => words.add(word));
            });
        });
        return words;
    }

    // ---------------------------------------------------------------- recherche

    function runSearch(raw) {
        const query = raw.replace(/\s+/g, ' ').trim().slice(0, MAX_CHARS);

        if (query.length < MIN_CHARS) {
            showInitial();
            if (query.length > 0) {
                announce(format(labels.status && labels.status.short, { min: MIN_CHARS }));
            }
            return;
        }

        const id = ++requestId;
        announce(labels.loading || '');

        prefetch().then(() => {
            if (id !== requestId) {
                return;
            }
            const results = search(query);
            render(query, results);
        }).catch(() => {
            if (id !== requestId) {
                return;
            }
            showError(query);
        });
    }

    function search(query) {
        const terms = tokenize(query);
        if (terms.length === 0) {
            return [];
        }

        let results = engine.search(buildQuery(terms, 'AND', false));
        if (results.length === 0 && terms.length > 1) {
            results = engine.search(buildQuery(terms, 'OR', false));
        }

        // Mot connu : ses prolongements restent trouvés, mais loin derrière.
        if (terms.some(isKnown)) {
            const seen = new Set(results.map((hit) => hit.id));
            const extra = engine.search(buildQuery(terms, results.length ? 'AND' : 'OR', true))
                .filter((hit) => !seen.has(hit.id))
                .map((hit) => ({ ...hit, score: hit.score * KNOWN_PREFIX_WEIGHT }));
            results = results.concat(extra);
        }

        return results;
    }

    /**
     * Arbre de requête MiniSearch : tous les mots (ET) ou au moins un (OU),
     * chaque mot étant élargi à ses synonymes (OU, pondérés plus bas).
     * Les mots sont déjà repliés et racinés : pas de second traitement.
     */
    function buildQuery(terms, combineWith, fullPrefix) {
        return {
            combineWith,
            queries: terms.map((term) => ({
                combineWith: 'OR',
                queries: [term].concat(SYNONYMS.get(term) || []),
                processTerm: (word) => word,
                boostTerm: (word) => (word === term ? 1 : SYNONYM_WEIGHT),
                prefix: (word) => fullPrefix || !isKnown(word),
                fuzzy: (word) => (word.length >= FUZZY_MIN ? 1 : false),
            })),
        };
    }

    function isKnown(term) {
        return term.length >= KNOWN_MIN && knownWords.has(term);
    }

    function buildSynonyms(groups) {
        const map = new Map();
        groups.forEach((raw) => {
            const words = String(raw).split('|');
            const single = new Set();
            const all = [];
            words.forEach((word) => {
                const tokensOfWord = tokenize(word);
                if (tokensOfWord.length === 1) {
                    single.add(tokensOfWord[0]);
                }
                tokensOfWord.forEach((token) => all.push(token));
            });
            single.forEach((word) => {
                const others = all.filter((token) => token !== word);
                map.set(word, [...new Set((map.get(word) || []).concat(others))]);
            });
        });
        return map;
    }

    // ---------------------------------------------------------------- rendu

    function render(query, results) {
        const grouped = new Map();
        results.forEach((hit) => {
            if (!grouped.has(hit.type)) {
                grouped.set(hit.type, []);
            }
            grouped.get(hit.type).push(hit);
        });

        const total = results.length;
        notePending(query, total);
        lastQuery = query;

        if (total === 0) {
            showEmpty(query);
            return;
        }

        const terms = highlightTerms(query);
        const fragment = document.createDocumentFragment();
        let position = 0;

        GROUPS.concat([...grouped.keys()].filter((type) => !GROUPS.includes(type))).forEach((type) => {
            const hits = grouped.get(type);
            if (!hits || hits.length === 0) {
                return;
            }

            const title = document.createElement('p');
            title.className = 'search-group-title';
            title.id = 'site-search-group-' + type;
            title.textContent = (labels.groups && labels.groups[type]) || type;
            const count = document.createElement('span');
            count.textContent = String(hits.length);
            title.appendChild(count);
            fragment.appendChild(title);

            const group = document.createElement('div');
            group.setAttribute('role', 'group');
            group.setAttribute('aria-labelledby', title.id);

            hits.slice(0, PER_GROUP).forEach((hit) => {
                position++;
                group.appendChild(renderOption(hit, terms, position));
            });

            fragment.appendChild(group);
        });

        const seeAll = document.createElement('a');
        seeAll.href = pageUrl + '?q=' + encodeURIComponent(query);
        seeAll.className = 'search-see-all';
        seeAll.setAttribute('role', 'option');
        seeAll.setAttribute('aria-selected', 'false');
        seeAll.dataset.searchOption = '';
        seeAll.dataset.searchSeeAll = '';
        seeAll.textContent = (labels.see_all || 'Voir tous les résultats') + ' (' + total + ')';
        fragment.appendChild(seeAll);

        resultsBox.replaceChildren(fragment);
        show('results');
        announce(total === 1
            ? format(labels.status && labels.status.one, { query })
            : format(labels.status && labels.status.many, { count: total, query }));
    }

    function renderOption(hit, terms, position) {
        const option = document.createElement('a');
        option.href = hit.url;
        option.className = 'search-option';
        option.setAttribute('role', 'option');
        option.setAttribute('aria-selected', 'false');
        option.id = 'site-search-option-' + position;
        option.dataset.searchOption = '';
        option.dataset.searchResult = hit.id;
        option.dataset.searchType = hit.type;
        option.dataset.searchPosition = String(position);

        const icon = document.createElement('span');
        icon.className = 'search-option-icon';
        icon.innerHTML = iconFor(hit.type);
        option.appendChild(icon);

        const main = document.createElement('span');
        main.className = 'search-option-main';

        if (Array.isArray(hit.breadcrumb) && hit.breadcrumb.length) {
            const crumb = document.createElement('span');
            crumb.className = 'search-option-crumb';
            crumb.textContent = hit.breadcrumb.join(' › ');
            main.appendChild(crumb);
        }

        const label = document.createElement('span');
        label.className = 'search-option-label';
        label.append(...highlight(hit.label || hit.title, terms));
        main.appendChild(label);

        const source = hit.body || hit.excerpt || '';
        if (source) {
            const excerpt = document.createElement('span');
            excerpt.className = 'search-option-excerpt';
            excerpt.append(...highlight(snippet(source, terms, 160), terms));
            main.appendChild(excerpt);
        }

        if (hit.price) {
            const meta = document.createElement('span');
            meta.className = 'search-option-meta';
            const strong = document.createElement('strong');
            strong.textContent = hit.price;
            meta.append(strong, ' · ' + [hit.units, hit.users].filter(Boolean).join(' · '));
            main.appendChild(meta);
        }

        option.appendChild(main);
        return option;
    }

    function iconFor(type) {
        const paths = {
            feature: '<svg viewBox="0 0 448 512" aria-hidden="true" focusable="false" class="icon-svg" fill="currentColor"><path d="M349.4 44.6c5.9-13.7 1.5-29.7-10.6-38.5s-28.6-8-39.9 1.8l-256 224c-10 8.8-13.6 22.9-8.9 35.3S50.7 288 64 288H175.5L98.6 467.4c-5.9 13.7-1.5 29.7 10.6 38.5s28.6 8 39.9-1.8l256-224c10-8.8 13.6-22.9 8.9-35.3s-16.6-20.7-30-20.7H272.5L349.4 44.6z"/></svg>',
            plan: '<svg viewBox="0 0 384 512" aria-hidden="true" focusable="false" class="icon-svg" fill="currentColor"><path d="M64 0C28.7 0 0 28.7 0 64V448c0 35.3 28.7 64 64 64H320c35.3 0 64-28.7 64-64V160H256c-17.7 0-32-14.3-32-32V0H64zM256 0V128H384L256 0zM64 80c0-8.8 7.2-16 16-16h64c8.8 0 16 7.2 16 16s-7.2 16-16 16H80c-8.8 0-16-7.2-16-16zm0 64c0-8.8 7.2-16 16-16h64c8.8 0 16 7.2 16 16s-7.2 16-16 16H80c-8.8 0-16-7.2-16-16zm128 72c8.8 0 16 7.2 16 16v17.3c8.5 1.2 16.7 3.1 24.1 5.1c8.5 2.3 13.6 11 11.3 19.6s-11 13.6-19.6 11.3c-11.1-3-22-5.2-32.1-5.3c-8.4-.1-17.4 1.8-23.6 5.5c-5.7 3.4-8.1 7.3-8.1 12.8c0 3.7 1.3 6.5 7.3 10.1c6.9 4.1 16.6 7.1 29.2 10.9l.5 .1c11.3 3.4 25.3 7.6 36.3 14.6c12.1 7.6 22.4 19.7 22.7 38.2c.3 19.3-9.6 33.3-22.9 41.6c-7.7 4.8-16.4 7.6-25.1 9.1V440c0 8.8-7.2 16-16 16s-16-7.2-16-16V422.2c-11.2-2.1-21.7-5.7-30.9-8.9c-2.1-.7-4.2-1.4-6.2-2.1c-8.4-2.8-12.9-11.9-10.1-20.2s11.9-12.9 20.2-10.1c2.5 .8 4.8 1.6 7.1 2.4c13.6 4.6 24.6 8.4 36.3 8.7c9.1 .3 17.9-1.7 23.7-5.3c5.1-3.2 7.9-7.3 7.8-14c-.1-4.6-1.8-7.8-7.7-11.6c-6.8-4.3-16.5-7.4-29-11.2l-1.6-.5c-11-3.3-24.3-7.3-34.8-13.7c-12-7.2-22.6-18.9-22.7-37.3c-.1-19.4 10.8-32.8 23.8-40.5c7.5-4.4 15.8-7.2 24.1-8.7V232c0-8.8 7.2-16 16-16z"/></svg>',
            faq: '<svg viewBox="0 0 512 512" aria-hidden="true" focusable="false" class="icon-svg" fill="currentColor"><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM169.8 165.3c7.9-22.3 29.1-37.3 52.8-37.3h58.3c34.9 0 63.1 28.3 63.1 63.1c0 22.6-12.1 43.5-31.7 54.8L280 264.4c-.2 13-10.9 23.6-24 23.6c-13.3 0-24-10.7-24-24V250.5c0-8.6 4.6-16.5 12.1-20.8l44.3-25.4c4.7-2.7 7.6-7.7 7.6-13.1c0-8.4-6.8-15.1-15.1-15.1H222.6c-3.4 0-6.4 2.1-7.5 5.3l-.4 1.2c-4.4 12.5-18.2 19-30.6 14.6s-19-18.2-14.6-30.6l.4-1.2zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z"/></svg>',
            page: '<svg viewBox="0 0 576 512" aria-hidden="true" focusable="false" class="icon-svg" fill="currentColor"><path d="M88.7 223.8L0 375.8V96C0 60.7 28.7 32 64 32H181.5c17 0 33.3 6.7 45.3 18.7l26.5 26.5c12 12 28.3 18.7 45.3 18.7H416c35.3 0 64 28.7 64 64v32H144c-22.8 0-43.8 12.1-55.3 31.8zm27.6 16.1C122.1 230 132.6 224 144 224H544c11.5 0 22 6.1 27.7 16.1s5.7 22.2-.1 32.1l-112 192C453.9 474 443.4 480 432 480H32c-11.5 0-22-6.1-27.7-16.1s-5.7-22.2 .1-32.1l112-192z"/></svg>',
        };
        return paths[type] || paths.page;
    }

    function showInitial() {
        requestId++;
        notePending('', 0);
        lastQuery = '';
        show('initial');
        announce('');
    }

    function showEmpty(query) {
        emptyTitle.textContent = format(labels.empty && labels.empty.title, { query });
        show('empty');
        announce(format(labels.status && labels.status.none, { query }));
    }

    function showError(query) {
        if (errorLink) {
            errorLink.href = pageUrl + '?q=' + encodeURIComponent(query);
        }
        show('error');
        announce((labels.error && labels.error.title) || '');
    }

    function show(state) {
        initialState.hidden = state !== 'initial';
        resultsBox.hidden = state !== 'results';
        emptyState.hidden = state !== 'empty';
        errorState.hidden = state !== 'error';
        input.setAttribute('aria-expanded', state === 'results' ? 'true' : 'false');
        setActive(visibleOptions(), state === 'results' ? 0 : -1, false);
    }

    function visibleOptions() {
        return [...list.querySelectorAll('[data-search-option]')].filter((option) => option.offsetParent !== null);
    }

    function setActive(options, index, scroll = true) {
        options.forEach((option) => option.setAttribute('aria-selected', 'false'));
        activeIndex = index;
        const active = options[index];
        if (!active) {
            input.removeAttribute('aria-activedescendant');
            return;
        }
        active.setAttribute('aria-selected', 'true');
        if (!active.id) {
            active.id = 'site-search-option-' + Math.random().toString(36).slice(2, 8);
        }
        input.setAttribute('aria-activedescendant', active.id);
        if (scroll) {
            active.scrollIntoView({ block: 'nearest' });
        }
    }

    function announce(text) {
        status.textContent = text || '';
    }

    // ---------------------------------------------------------------- récents

    function renderRecent() {
        if (!recentBox || !recentList) {
            return;
        }
        const recent = storage('get');
        recentBox.hidden = recent.length === 0;
        recentList.replaceChildren(...recent.map((term) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'search-chip';
            button.setAttribute('role', 'option');
            button.setAttribute('aria-selected', 'false');
            button.dataset.searchOption = '';
            button.dataset.searchFill = term;
            button.textContent = term;
            return button;
        }));
    }

    function rememberRecent(term) {
        term = (term || '').trim();
        if (term.length < MIN_CHARS) {
            return;
        }
        const recent = storage('get').filter((item) => item.toLowerCase() !== term.toLowerCase());
        recent.unshift(term);
        storage('set', recent.slice(0, 3));
    }

    function storage(action, value) {
        try {
            if (action === 'get') {
                const raw = window.localStorage.getItem(RECENT_KEY);
                const parsed = raw ? JSON.parse(raw) : [];
                return Array.isArray(parsed) ? parsed.filter((item) => typeof item === 'string') : [];
            }
            if (action === 'set') {
                window.localStorage.setItem(RECENT_KEY, JSON.stringify(value));
            }
            if (action === 'remove') {
                window.localStorage.removeItem(RECENT_KEY);
            }
        } catch (error) {
            // localStorage indisponible (navigation privée, iframe) : pas de récents.
        }
        return [];
    }

    // ---------------------------------------------------------------- suivi

    function notePending(query, total) {
        const now = Date.now();
        if (pending && pending.query !== query && now - pending.shownAt >= SETTLE) {
            send(pending, 'replace');
        }
        pending = query ? { query, results_count: total, shownAt: now } : null;
    }

    function flushPending(reason, extra) {
        if (!pending) {
            return;
        }
        const entry = { ...pending, ...(extra || {}) };
        pending = null;
        send(entry, reason);
    }

    function send(entry) {
        if (!trackUrl || !entry.query) {
            return;
        }
        const payload = JSON.stringify({
            query: entry.query,
            locale,
            source: 'palette',
            results_count: entry.results_count,
            clicked_id: entry.clicked_id || null,
            clicked_type: entry.clicked_type || null,
            clicked_position: entry.clicked_position || null,
        });
        try {
            if (navigator.sendBeacon && navigator.sendBeacon(trackUrl, new Blob([payload], { type: 'text/plain' }))) {
                return;
            }
            fetch(trackUrl, { method: 'POST', body: payload, headers: { 'Content-Type': 'text/plain' }, keepalive: true, credentials: 'same-origin' }).catch(() => {});
        } catch (error) {
            // Le suivi ne doit jamais gêner la recherche.
        }
    }

    // ---------------------------------------------------------------- texte

    function highlightTerms(query) {
        const terms = tokenize(query);
        const all = new Set(terms);
        terms.forEach((term) => (SYNONYMS.get(term) || []).forEach((synonym) => all.add(synonym)));
        return [...all].filter((term) => term.length >= 2);
    }

    function highlight(text, terms) {
        text = String(text || '');
        if (!terms.length) {
            return [document.createTextNode(text)];
        }
        const folded = fold(text);
        if (folded.length !== text.length) {
            return [document.createTextNode(text)];
        }
        const pattern = new RegExp('(^|[^a-z0-9])(' + terms.map(escapeRegExp).join('|') + ')', 'gi');
        const nodes = [];
        let cursor = 0;
        let match;
        while ((match = pattern.exec(folded)) !== null) {
            const start = match.index + match[1].length;
            const end = start + match[2].length;
            if (start > cursor) {
                nodes.push(document.createTextNode(text.slice(cursor, start)));
            }
            const mark = document.createElement('mark');
            mark.textContent = text.slice(start, end);
            nodes.push(mark);
            cursor = end;
        }
        if (cursor < text.length) {
            nodes.push(document.createTextNode(text.slice(cursor)));
        }
        return nodes;
    }

    function snippet(text, terms, length) {
        text = String(text || '');
        if (text.length <= length) {
            return text;
        }
        const folded = fold(text);
        let position = -1;
        terms.forEach((term) => {
            const found = folded.indexOf(term);
            if (found !== -1 && (position === -1 || found < position)) {
                position = found;
            }
        });
        if (position === -1 || position < length * 0.6) {
            return trimEnd(text.slice(0, length - 1)) + '…';
        }
        let start = Math.max(0, position - Math.floor(length * 0.3));
        const space = text.indexOf(' ', start);
        if (space !== -1 && space - start < 20) {
            start = space + 1;
        }
        return '…' + trimEnd(text.slice(start, start + length - 2)) + '…';
    }

    function trimEnd(text) {
        const space = text.lastIndexOf(' ');
        if (space > text.length * 0.6) {
            text = text.slice(0, space);
        }
        return text.replace(/[\s,;:.]+$/, '');
    }

    function tokenize(text) {
        return fold(text).split(/[^a-z0-9]+/).filter((token) => token && !STOP_WORDS.has(token)).map(stem);
    }

    return { open, close, prefetch, isOpen: () => dialog.open };
}

// Racine légère, identique à SearchText::stem() : « loyers » → « loyer ».
function stem(word) {
    if (word.length > 3 && !/^\d+$/.test(word) && /[sx]$/.test(word)) {
        return word.slice(0, -1);
    }
    return word;
}

function fold(text) {
    return String(text || '')
        .toLowerCase()
        .replace(/[’‘`´]/g, "'")
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '');
}

function format(template, values) {
    if (!template) {
        return '';
    }
    return String(template).replace(/:(\w+)/g, (match, key) => (key in values ? String(values[key]) : match));
}

function parseJson(raw, fallback) {
    try {
        return raw ? JSON.parse(raw) : fallback;
    } catch (error) {
        return fallback;
    }
}

function escapeRegExp(text) {
    return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}
