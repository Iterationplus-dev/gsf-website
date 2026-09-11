<?php

namespace App\Http\Requests;

use App\Models\MembershipApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MembershipApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'contact_name' => ['required', 'string', 'max:150'],
            'contact_phone' => ['required', 'string', 'max:40'],
            'organisation_name' => ['required', 'string', 'max:200'],
            'organisation_address' => ['required', 'string', 'max:500'],
            'telephone' => ['nullable', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:254'],
            'cooperative_type' => ['required', Rule::in(array_keys(MembershipApplication::TYPES))],
            'organisation_website' => ['nullable', 'url', 'max:255'],
            'member_count' => ['required', 'integer', 'min:'.MembershipApplication::MINIMUM_MEMBERS, 'max:100000'],
            'ethnic_group' => ['nullable', 'string', 'max:120'],
            'message' => ['nullable', 'string', 'max:5000'],
            'consent' => ['accepted'],
            'website' => ['nullable', 'max:0'],
            'submission_key' => ['required', 'uuid'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'member_count.min' => 'A co-operative society must have at least '.MembershipApplication::MINIMUM_MEMBERS.' members to apply.',
        ];
    }
}
