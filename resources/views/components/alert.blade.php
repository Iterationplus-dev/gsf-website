@props(['message' => null])

@php
    $success = $message ?? session('success');
    $failures = $errors->any();
@endphp

@if ($success)
    {{-- role="status" announces the result without stealing focus. --}}
    <div {{ $attributes->class(['flex items-start gap-3 rounded-md border border-success-600/30 bg-success-600/5 p-4 text-sm text-ink']) }} role="status">
        <svg viewBox="0 0 20 20" class="mt-0.5 size-5 shrink-0 text-success-600" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.7-9.3-4.2 4.2a1 1 0 0 1-1.4 0l-2-2 1.4-1.4L8.8 10.8l3.5-3.5 1.4 1.4Z" clip-rule="evenodd"/></svg>
        <p>{{ $success }}</p>
    </div>
@elseif ($failures)
    <div {{ $attributes->class(['flex items-start gap-3 rounded-md border border-error-600/30 bg-error-600/5 p-4 text-sm text-ink']) }} role="alert">
        <svg viewBox="0 0 20 20" class="mt-0.5 size-5 shrink-0 text-error-600" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm.75 4v5h-1.5V6h1.5Zm0 7v1.5h-1.5V13h1.5Z" clip-rule="evenodd"/></svg>
        <div>
            <p class="font-semibold">Please check the form.</p>
            <ul class="mt-1 list-disc space-y-0.5 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
