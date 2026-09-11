<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['type' => ['required', Rule::in(['contact', 'partnership', 'volunteer'])], 'name' => ['required', 'string', 'max:150'], 'email' => ['required', 'email', 'max:254'], 'phone' => ['nullable', 'string', 'max:40'], 'organization' => ['nullable', 'string', 'max:200'], 'message' => ['required', 'string', 'min:10', 'max:5000'], 'consent' => ['accepted'], 'website' => ['nullable', 'max:0'], 'submission_key' => ['required', 'uuid']];
    }
}
