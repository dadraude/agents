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
            'use_llm_interpreter' => ['sometimes', 'nullable', 'boolean'],
            'use_llm_classifier' => ['sometimes', 'nullable', 'boolean'],
            'use_llm_validator' => ['sometimes', 'nullable', 'boolean'],
            'use_llm_prioritizer' => ['sometimes', 'nullable', 'boolean'],
            'use_llm_decision_maker' => ['sometimes', 'nullable', 'boolean'],
            'use_llm_linear_writer' => ['sometimes', 'nullable', 'boolean'],
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

        // Process use_llm fields: empty string = null, "1" = true, "0" = false
        $llmFields = [
            'use_llm_interpreter',
            'use_llm_classifier',
            'use_llm_validator',
            'use_llm_prioritizer',
            'use_llm_decision_maker',
            'use_llm_linear_writer',
        ];

        foreach ($llmFields as $field) {
            if ($this->has($field)) {
                $value = $this->input($field);
                // Convert empty string to null, "1" to true, "0" to false
                if ($value === '' || $value === null) {
                    $this->merge([$field => null]);
                } else {
                    $this->merge([
                        $field => $value === '1' || $value === 1 || $value === true || $value === 'true',
                    ]);
                }
            }
        }
    }
}
