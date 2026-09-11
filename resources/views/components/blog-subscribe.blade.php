{{-- Follows the giving band on the blog, where the useful next step is not
     another donation ask but a way to receive the next article. --}}
<section class="border-t border-line bg-surface" aria-labelledby="blog-subscribe">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-12 lg:items-center">
            <div class="lg:col-span-6">
                <p class="eyebrow text-accent-600">Keep reading</p>
                <h2 id="blog-subscribe" class="mt-3 font-serif text-3xl leading-tight font-semibold text-ink">
                    New guidance, sent when we publish it
                </h2>
                <p class="mt-4 leading-relaxed text-muted">
                    Practical writing on forming, registering and running a co-operative — alongside
                    project updates and opportunities to take part. No more than we would want to receive.
                </p>

                <p class="mt-6 text-sm text-muted">
                    Have a question these articles do not answer?
                    <a href="{{ route('pages.show', 'contact-us') }}" class="font-medium text-primary-700 underline">Ask us directly</a>.
                </p>
            </div>

            <div class="lg:col-span-6">
                <div class="card p-6 sm:p-8">
                    <x-newsletter-form tone="light" />
                </div>
            </div>
        </div>
    </div>
</section>
