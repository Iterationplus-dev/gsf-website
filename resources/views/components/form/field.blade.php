@props([
    'name',
    'label',
    'type' => 'text',
    'required' => false,
    'hint' => null,
    'value' => null,
    'placeholder' => null,
    'rows' => 5,
    'autocomplete' => null,
    'options' => [],
    'prompt' => 'Please choose',
])

@php
    $id = $name.'-'.Str::random(4);
    $errorId = $id.'-error';
    $hintId = $id.'-hint';
    $invalid = $errors->has($name);

    // Screen readers need the hint and the error announced with the field, and
    // aria-describedby is the only thing that ties them together.
    $describedBy = collect([$hint ? $hintId : null, $invalid ? $errorId : null])->filter()->implode(' ');
@endphp

<div>
    <label for="{{ $id }}" class="field-label">
        {{ $label }}
        @if ($required)
            <span class="text-accent-600" aria-hidden="true">*</span>
            <span class="sr-only">(required)</span>
        @endif
    </label>

    @if ($hint)
        <p id="{{ $hintId }}" class="mb-1.5 text-sm text-muted">{{ $hint }}</p>
    @endif

    @if ($type === 'select')
        <select
            id="{{ $id }}"
            name="{{ $name }}"
            @if ($required) required @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            aria-invalid="{{ $invalid ? 'true' : 'false' }}"
            {{ $attributes->class(['field-input', 'border-error-600' => $invalid]) }}
        >
            <option value="">{{ $prompt }}</option>
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) old($name, $value) === (string) $optionValue)>{{ $optionLabel }}</option>
            @endforeach
        </select>
    @elseif ($type === 'textarea')
        <textarea
            id="{{ $id }}"
            name="{{ $name }}"
            rows="{{ $rows }}"
            @if ($required) required @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            aria-invalid="{{ $invalid ? 'true' : 'false' }}"
            {{ $attributes->class(['field-input', 'border-error-600' => $invalid]) }}
        >{{ old($name, $value) }}</textarea>
    @else
        <input
            id="{{ $id }}"
            type="{{ $type }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            @if ($required) required @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            aria-invalid="{{ $invalid ? 'true' : 'false' }}"
            {{ $attributes->class(['field-input', 'border-error-600' => $invalid]) }}
        >
    @endif

    @error($name)
        <p id="{{ $errorId }}" class="field-error">
            <svg viewBox="0 0 20 20" class="mt-0.5 size-4 shrink-0" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm.75 4v5h-1.5V6h1.5Zm0 7v1.5h-1.5V13h1.5Z" clip-rule="evenodd"/></svg>
            <span>{{ $message }}</span>
        </p>
    @enderror
</div>
