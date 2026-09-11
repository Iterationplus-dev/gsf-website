import { startCounters } from './counters';
import { startLightbox } from './lightbox';

/**
 * Progressive enhancement only. Every page renders and functions without this
 * bundle; Alpine (bundled with Livewire) handles disclosure and menu state, so
 * nothing here duplicates it.
 */
document.addEventListener('DOMContentLoaded', () => {
    startCounters();
    startLightbox();
});

// Livewire replaces DOM fragments; re-scan for counters that arrive with them.
document.addEventListener('livewire:navigated', () => startCounters());
