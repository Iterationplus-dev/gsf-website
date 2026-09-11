<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'], 'email' => ['required', 'email', 'max:254'], 'phone' => ['nullable', 'string', 'max:40'],
            'amount' => ['required', 'regex:/^\\d{1,8}(\\.\\d{1,2})?$/', 'numeric', 'min:1', 'max:10000000'],
            'currency' => ['required', Rule::in(config('foundation.currencies'))],
            'campaign_id' => ['nullable', Rule::exists('campaigns', 'id')->where('active', true)],
            'anonymous' => ['sometimes', 'boolean'], 'message' => ['nullable', 'string', 'max:1000'],
            'consent' => ['accepted'], 'website' => ['nullable', 'max:0'], 'submission_key' => ['required', 'uuid'],
        ];
    }
}
