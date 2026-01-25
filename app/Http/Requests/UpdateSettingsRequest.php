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

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $activeFields = [
            'active_interpreter',
            'active_classifier',
            'active_validator',
            'active_prioritizer',
            'active_decision_maker',
            'active_linear_writer',
        ];

        foreach ($activeFields as $field) {
            if ($this->has($field)) {
                $value = $this->input($field);
                // Convert "1" or "0" strings to boolean
                $this->merge([
                    $field => $value === '1' || $value === 1 || $value === true || $value === 'true',
                ]);
            }
        }
    }
}
