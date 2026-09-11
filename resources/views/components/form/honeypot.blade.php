{{--
    Bot protection that costs a legitimate visitor nothing.

    `website` must stay empty: the validation rule is `max:0`, so anything that
    fills every field it finds is rejected. `submission_key` is generated once
    per rendered form and is unique in the database, so a double-clicked submit
    button or a browser retry updates the same record instead of creating a
    second enquiry or a second donation.

    The honeypot is hidden from assistive technology as well as from sight, so
    a screen reader never presents it and never trips the trap.
--}}
<div class="hidden" aria-hidden="true">
    <label for="website-{{ $id ?? 'field' }}">Leave this field empty</label>
    <input type="text" id="website-{{ $id ?? 'field' }}" name="website" value="" tabindex="-1" autocomplete="off">
</div>

<input type="hidden" name="submission_key" value="{{ old('submission_key', Str::uuid()) }}">
