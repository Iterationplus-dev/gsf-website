/**
 * Impact counters.
 *
 * The final figure is rendered server-side, so the number is correct and
 * readable before any script runs and remains correct if none ever does. This
 * only animates towards a value that is already on the page, and does nothing
 * at all when the visitor has asked for reduced motion.
 */

const DURATION_MS = 1400;

function formatLike(template: string, value: number): string {
    const decimals = template.includes('.') ? (template.split('.')[1]?.length ?? 0) : 0;

    return value.toLocaleString(undefined, {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });
}

function animate(element: HTMLElement): void {
    const final = element.textContent ?? '';
    const target = Number.parseFloat(final.replace(/[^0-9.]/g, ''));

    if (!Number.isFinite(target) || target <= 0) {
        return;
    }

    const start = performance.now();

    const step = (now: number): void => {
        const progress = Math.min((now - start) / DURATION_MS, 1);
        // Ease-out cubic: fast to begin with, settling onto the real figure.
        const eased = 1 - Math.pow(1 - progress, 3);

        element.textContent = progress === 1 ? final : formatLike(final, target * eased);

        if (progress < 1) {
            requestAnimationFrame(step);
        }
    };

    requestAnimationFrame(step);
}

export function startCounters(root: ParentNode = document): void {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    const elements = root.querySelectorAll<HTMLElement>('[data-counter]:not([data-counter-done])');

    if (elements.length === 0) {
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (!entry.isIntersecting) {
                    continue;
                }

                const element = entry.target as HTMLElement;
                element.dataset.counterDone = 'true';
                observer.unobserve(element);
                animate(element);
            }
        },
        { threshold: 0.4 },
    );

    elements.forEach((element) => observer.observe(element));
}
