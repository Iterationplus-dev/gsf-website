import { startConsent } from './consent';
import { startCounters } from './counters';
import { startLightbox } from './lightbox';

/**
 * Progressive enhancement only. Every page renders and functions without this
 * bundle; Alpine (bundled with Livewire) handles disclosure and menu state, so
 * nothing here duplicates it.
 *
 * Consent is the one place this matters: without the bundle the banner never
 * appears, but neither does any third party, because blocked scripts and embeds
 * are only ever activated from here. Absent script is absent consent.
 */
document.addEventListener('DOMContentLoaded', () => {
    startConsent();
    startCounters();
    startLightbox();
});

// Livewire replaces DOM fragments; re-scan for counters that arrive with them.
document.addEventListener('livewire:navigated', () => startCounters());
