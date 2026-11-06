<?php

namespace App\Http\Requests;

use App\Models\EarlyAccessSignup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEarlyAccessRequest extends FormRequest
{
    // Where non-JavaScript visitors land after a validation error.
    protected $redirect = '/#early-access';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => is_string($this->email) ? mb_strtolower(trim($this->email)) : $this->email,
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
            'name' => ['nullable', 'string', 'max:120'],
            'interest' => ['required', Rule::in(EarlyAccessSignup::INTERESTS)],
            'consent' => ['accepted'],
            'website' => ['nullable', 'string'], // honeypot, handled in the controller
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'That email address does not look right.',
            'consent.accepted' => 'Please confirm you agree so we can email you.',
            'interest.in' => 'Please choose one of the options.',
        ];
    }
}
