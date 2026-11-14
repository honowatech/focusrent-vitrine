/**
 * Amorçage de la recherche : loupe du header, champ du menu mobile,
 * Ctrl+K / Cmd+K et « / ». La palette (search-palette.js) et MiniSearch
 * ne sont chargés qu'à la première ouverture ou au survol d'un déclencheur,
 * pour ne rien ajouter au premier rendu de la page.
 */

const dialog = document.getElementById('site-search');

if (dialog) {
    let palette = null;

    const load = () => {
        if (!palette) {
            palette = import('./search-palette.js').then((module) => {
                const api = module.setup(dialog);
                api.prefetch().catch(() => {});
                return api;
            });
        }
        return palette;
    };

    const open = (from) => {
        load().then((api) => api.open(from)).catch(() => {
            // Sans palette, le lien mène à la page de résultats.
            if (from instanceof HTMLAnchorElement && from.href) {
                window.location.assign(from.href);
            }
        });
    };

    const isMac = /Mac|iPhone|iPad|iPod/.test(navigator.platform || '');
    document.querySelectorAll('[data-search-kbd]').forEach((kbd) => {
        kbd.textContent = isMac ? '⌘ K' : 'Ctrl K';
    });

    document.querySelectorAll('[data-search-open]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.button === 1) {
                return;
            }
            event.preventDefault();
            open(trigger);
        });
        trigger.addEventListener('pointerenter', load, { once: true });
        trigger.addEventListener('focus', load, { once: true });
    });

    document.addEventListener('keydown', (event) => {
        const typing = isTypingTarget(event.target);

        if ((event.ctrlKey || event.metaKey) && !event.altKey && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            if (dialog.open) {
                load().then((api) => api.close());
            } else {
                open(document.activeElement);
            }
            return;
        }

        if (event.key === '/' && !dialog.open && !typing && !event.ctrlKey && !event.metaKey && !event.altKey) {
            event.preventDefault();
            open(document.activeElement);
        }
    });
}

function isTypingTarget(target) {
    if (!(target instanceof HTMLElement)) {
        return false;
    }
    const tag = target.tagName;
    return tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || target.isContentEditable;
}
