@props(['text' => null])

<div>
    <label class="flex items-start gap-3 text-sm text-muted">
        <input type="checkbox" name="consent" value="1" required
            @checked(old('consent'))
            aria-describedby="consent-description"
            class="mt-0.5 size-5 shrink-0 rounded border-line text-primary-700 focus:ring-primary-600">
        <span id="consent-description">
            {{ $text ?? 'I agree that Global Support Foundation may use the details I have provided to respond to me.' }}
            Read our <a href="{{ route('pages.show', 'privacy-policy') }}" class="font-medium text-primary-700 underline">privacy notice</a>.
        </span>
    </label>

    @error('consent')
        <p class="field-error">
            <svg viewBox="0 0 20 20" class="mt-0.5 size-4 shrink-0" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm.75 4v5h-1.5V6h1.5Zm0 7v1.5h-1.5V13h1.5Z" clip-rule="evenodd"/></svg>
            <span>Please confirm you agree before submitting.</span>
        </p>
    @enderror
</div>
