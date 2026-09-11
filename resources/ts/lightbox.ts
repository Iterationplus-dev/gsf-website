/**
 * Accessible image lightbox for project galleries and award certificates.
 *
 * Certificate scans are the reason this exists: they are unreadable at gallery
 * size, so the enlarged view loads the full-resolution original rather than a
 * display variant. The dialog element gives focus trapping and Escape handling
 * for free; arrow keys move between images in the same gallery.
 */

interface GalleryItem {
    readonly full: string;
    readonly alt: string;
    readonly caption: string;
}

let dialog: HTMLDialogElement | null = null;
let image: HTMLImageElement;
let caption: HTMLElement;
let counter: HTMLElement;
let items: GalleryItem[] = [];
let index = 0;

function build(): HTMLDialogElement {
    const element = document.createElement('dialog');
    element.className =
        'backdrop:bg-ink/80 m-auto w-full max-w-5xl bg-transparent p-4 text-white';
    element.innerHTML = `
        <figure class="flex flex-col gap-3">
            <img alt="" class="mx-auto max-h-[75vh] w-auto rounded-md bg-white object-contain" />
            <figcaption class="flex items-center justify-between gap-4 text-sm">
                <span data-lightbox-caption class="text-white/90"></span>
                <span data-lightbox-counter class="shrink-0 text-white/70"></span>
            </figcaption>
        </figure>
        <div class="mt-4 flex justify-center gap-2">
            <button type="button" data-lightbox-prev class="rounded-md border border-white/40 px-4 py-2 text-sm font-medium hover:bg-white/10">Previous</button>
            <button type="button" data-lightbox-next class="rounded-md border border-white/40 px-4 py-2 text-sm font-medium hover:bg-white/10">Next</button>
            <button type="button" data-lightbox-close class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-ink">Close</button>
        </div>
    `;

    document.body.append(element);

    image = element.querySelector('img') as HTMLImageElement;
    caption = element.querySelector('[data-lightbox-caption]') as HTMLElement;
    counter = element.querySelector('[data-lightbox-counter]') as HTMLElement;

    element.querySelector('[data-lightbox-close]')?.addEventListener('click', () => element.close());
    element.querySelector('[data-lightbox-prev]')?.addEventListener('click', () => show(index - 1));
    element.querySelector('[data-lightbox-next]')?.addEventListener('click', () => show(index + 1));

    element.addEventListener('keydown', (event: KeyboardEvent) => {
        if (event.key === 'ArrowLeft') {
            show(index - 1);
        }

        if (event.key === 'ArrowRight') {
            show(index + 1);
        }
    });

    // Clicking the backdrop closes, but clicking the figure itself must not.
    element.addEventListener('click', (event) => {
        if (event.target === element) {
            element.close();
        }
    });

    return element;
}

function show(next: number): void {
    if (items.length === 0) {
        return;
    }

    index = (next + items.length) % items.length;

    const item = items[index] as GalleryItem;

    image.src = item.full;
    image.alt = item.alt;
    caption.textContent = item.caption;
    counter.textContent = `${index + 1} of ${items.length}`;
}

/** Every trigger sharing this trigger's `data-lightbox` name, in document order. */
function galleryOf(trigger: HTMLElement): HTMLElement[] {
    const name = trigger.dataset.lightbox ?? '';

    return Array.from(document.querySelectorAll<HTMLElement>(`[data-lightbox="${CSS.escape(name)}"]`));
}

export function startLightbox(): void {
    document.addEventListener('click', (event) => {
        const trigger = (event.target as HTMLElement | null)?.closest<HTMLElement>('[data-lightbox]');

        if (!trigger) {
            return;
        }

        event.preventDefault();

        const gallery = galleryOf(trigger);

        dialog ??= build();
        items = gallery.map((element) => ({
            full: element.dataset.lightboxFull ?? '',
            alt: element.dataset.lightboxAlt ?? '',
            caption: element.dataset.lightboxCaption ?? '',
        }));

        show(gallery.indexOf(trigger));
        dialog.showModal();
    });
}
