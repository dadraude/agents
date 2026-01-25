<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'active_interpreter' => ['sometimes', 'boolean'],
            'active_classifier' => ['sometimes', 'boolean'],
            'active_validator' => ['sometimes', 'boolean'],
            'active_prioritizer' => ['sometimes', 'boolean'],
            'active_decision_maker' => ['sometimes', 'boolean'],
            'active_linear_writer' => ['sometimes', 'boolean'],
        ];
    }
}
