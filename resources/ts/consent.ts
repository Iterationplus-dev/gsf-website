/**
 * Cookie consent.
 *
 * Nothing outside the necessary category runs until the visitor has asked for
 * it. Third-party scripts are rendered inert as `type="text/plain"` and embeds
 * are rendered with their address in `data-consent-src` instead of `src`; both
 * are only activated here, once the matching category has been granted. Consent
 * is therefore fail-closed — if this module never runs, no third party does
 * either.
 *
 * The preferences dialog is a native `<dialog>`, as in the lightbox, so focus
 * trapping and Escape come from the platform rather than from script.
 */

export type ConsentCategory = 'necessary' | 'functional' | 'analytics' | 'marketing';

type Decisions = Record<ConsentCategory, boolean>;

interface ConsentState {
    readonly version: number;
    readonly at: string;
    readonly categories: Decisions;
}

/**
 * Raise this when the categories change, or when a third party is added to one
 * of them. A stored decision from an older version is treated as absent, so the
 * visitor is asked again rather than being opted in to something new by a
 * consent they gave before it existed.
 */
const CONSENT_VERSION = 1;

const COOKIE_NAME = 'gsf_consent';
const COOKIE_DAYS = 180;

const OPTIONAL = ['functional', 'analytics', 'marketing'] as const;

type OptionalCategory = (typeof OPTIONAL)[number];

/**
 * Cookies each optional category is responsible for, cleared when it is
 * withdrawn. Prefixes, because most third parties suffix an identifier.
 */
const COOKIE_PREFIXES: Record<OptionalCategory, readonly string[]> = {
    functional: [],
    analytics: ['plausible_', '_pk_'],
    marketing: ['VISITOR_INFO', 'YSC', 'PREF', 'IDE', 'test_cookie'],
};

function decisions(granted: boolean): Decisions {
    return { necessary: true, functional: granted, analytics: granted, marketing: granted };
}

function readCookie(name: string): string | null {
    for (const entry of document.cookie.split(';')) {
        const [key, ...rest] = entry.split('=');

        if (key?.trim() === name) {
            return decodeURIComponent(rest.join('='));
        }
    }

    return null;
}

function writeCookie(name: string, value: string): void {
    const expires = new Date(Date.now() + COOKIE_DAYS * 86_400_000).toUTCString();
    const secure = location.protocol === 'https:' ? '; Secure' : '';

    document.cookie = `${name}=${encodeURIComponent(value)}; Expires=${expires}; Path=/; SameSite=Lax${secure}`;
}

/**
 * Best-effort removal of the cookies a withdrawn category had set. Third
 * parties set them against both the bare host and the dot-prefixed domain, and
 * a cookie can only be deleted from the exact pair it was written with, so
 * every plausible combination is tried.
 */
function forgetCookies(prefixes: readonly string[]): void {
    if (prefixes.length === 0) {
        return;
    }

    const domains = ['', location.hostname, `.${location.hostname}`];

    for (const entry of document.cookie.split(';')) {
        const name = entry.split('=')[0]?.trim();

        if (!name || !prefixes.some((prefix) => name.startsWith(prefix))) {
            continue;
        }

        for (const domain of domains) {
            document.cookie = `${name}=; Expires=Thu, 01 Jan 1970 00:00:00 GMT; Path=/${domain ? `; Domain=${domain}` : ''}`;
        }
    }
}

/** The saved decision, or null when there is none this version can honour. */
function storedConsent(): ConsentState | null {
    const raw = readCookie(COOKIE_NAME);

    if (!raw) {
        return null;
    }

    try {
        const parsed: unknown = JSON.parse(raw);

        if (typeof parsed !== 'object' || parsed === null) {
            return null;
        }

        const state = parsed as Partial<ConsentState>;

        if (state.version !== CONSENT_VERSION || typeof state.categories !== 'object' || state.categories === null) {
            return null;
        }

        return {
            version: CONSENT_VERSION,
            at: typeof state.at === 'string' ? state.at : new Date().toISOString(),
            categories: { ...decisions(false), ...state.categories, necessary: true },
        };
    } catch {
        // A cookie we cannot parse is a cookie we cannot honour.
        return null;
    }
}

function persist(categories: Decisions): ConsentState {
    const state: ConsentState = {
        version: CONSENT_VERSION,
        at: new Date().toISOString(),
        categories: { ...categories, necessary: true },
    };

    writeCookie(COOKIE_NAME, JSON.stringify(state));

    return state;
}

/**
 * Activate everything the granted categories allow: blocked scripts become real
 * scripts, and held-back embeds get their address and replace their placeholder.
 * Safe to call repeatedly — each element is unblocked at most once.
 */
function applyConsent(categories: Decisions, root: ParentNode = document): void {
    root.querySelectorAll<HTMLIFrameElement>('iframe[data-consent-src]').forEach((frame) => {
        const category = frame.dataset.consentCategory as ConsentCategory | undefined;
        const source = frame.dataset.consentSrc;

        if (!category || !source || !categories[category]) {
            return;
        }

        frame.src = source;
        frame.removeAttribute('data-consent-src');
        frame.hidden = false;

        const holder = frame.closest('[data-consent-holder]');
        holder?.querySelector<HTMLElement>('[data-consent-placeholder]')?.setAttribute('hidden', 'hidden');
    });

    root.querySelectorAll<HTMLScriptElement>('script[type="text/plain"][data-consent-category]').forEach((blocked) => {
        const category = blocked.dataset.consentCategory as ConsentCategory | undefined;

        if (!category || !categories[category]) {
            return;
        }

        const live = document.createElement('script');

        for (const attribute of Array.from(blocked.attributes)) {
            if (attribute.name !== 'type' && !attribute.name.startsWith('data-consent')) {
                live.setAttribute(attribute.name, attribute.value);
            }
        }

        live.textContent = blocked.textContent;
        blocked.replaceWith(live);
    });

    // Lets stylesheets and tests see what is currently permitted.
    document.documentElement.dataset.consent = OPTIONAL.filter((category) => categories[category]).join(' ') || 'none';
}

/** The decision currently in force, defaulting to nothing optional. */
let current: Decisions = decisions(false);

function save(next: Decisions): void {
    const withdrawn = OPTIONAL.filter((category) => current[category] && !next[category]);

    current = persist(next).categories;

    document.querySelector<HTMLElement>('[data-consent-banner]')?.setAttribute('hidden', 'hidden');
    document.querySelector<HTMLDialogElement>('[data-consent-dialog]')?.close();

    if (withdrawn.length > 0) {
        // A script that is already running cannot be reliably unloaded, so the
        // only honest way to withdraw consent is to clear its cookies and
        // reload into a page that never loads it.
        withdrawn.forEach((category) => forgetCookies(COOKIE_PREFIXES[category]));
        location.reload();

        return;
    }

    applyConsent(current);
}

function syncDialog(dialog: HTMLDialogElement): void {
    dialog.querySelectorAll<HTMLInputElement>('input[type="checkbox"][data-consent-toggle]').forEach((input) => {
        const category = input.dataset.consentToggle as ConsentCategory | undefined;

        if (category && category !== 'necessary') {
            input.checked = current[category];
        }
    });
}

function readDialog(dialog: HTMLDialogElement): Decisions {
    const next = decisions(false);

    dialog.querySelectorAll<HTMLInputElement>('input[type="checkbox"][data-consent-toggle]').forEach((input) => {
        const category = input.dataset.consentToggle as ConsentCategory | undefined;

        if (category && category !== 'necessary') {
            next[category] = input.checked;
        }
    });

    return next;
}

export function startConsent(): void {
    const banner = document.querySelector<HTMLElement>('[data-consent-banner]');
    const dialog = document.querySelector<HTMLDialogElement>('[data-consent-dialog]');

    const saved = storedConsent();

    if (saved) {
        current = saved.categories;
    }

    applyConsent(current);

    // Only ask when there is no decision this version can honour. A saved
    // preference is never re-prompted for until CONSENT_VERSION moves on.
    if (!saved && banner) {
        banner.hidden = false;
    }

    if (dialog) {
        syncDialog(dialog);
    }

    document.addEventListener('click', (event) => {
        const target = event.target as HTMLElement | null;
        const action = target?.closest<HTMLElement>('[data-consent-action]');

        if (!action) {
            return;
        }

        event.preventDefault();

        switch (action.dataset.consentAction) {
            case 'accept':
                save(decisions(true));
                break;

            case 'reject':
                save(decisions(false));
                break;

            case 'save':
                if (dialog) {
                    save(readDialog(dialog));
                }
                break;

            case 'open':
                if (dialog) {
                    syncDialog(dialog);
                    dialog.showModal();
                }
                break;

            case 'close':
                dialog?.close();
                break;

            case 'allow': {
                // A placeholder's own "load this" control: grant just the one
                // category it needs and leave every other decision alone.
                const category = action.dataset.consentCategory as ConsentCategory | undefined;

                if (category) {
                    save({ ...current, [category]: true });
                }

                break;
            }
        }
    });

    // Livewire swaps DOM fragments in; anything blocked that arrives with them
    // still has to honour the decision already made.
    document.addEventListener('livewire:navigated', () => applyConsent(current));
}
