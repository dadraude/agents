<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use App\Models\AppSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index(): View
    {
        $settings = AppSetting::get();

        return view('settings.index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update the settings.
     */
    public function update(UpdateSettingsRequest $request): RedirectResponse|JsonResponse
    {
        $settings = AppSetting::get();
        $validated = $request->validated();

        // Only update fields that were actually sent in the request
        // This prevents overwriting fields that weren't included (e.g., when only updating LLM settings)
        $updateData = [];

        // Process active fields only if they were sent
        $activeFields = [
            'active_interpreter',
            'active_classifier',
            'active_validator',
            'active_prioritizer',
            'active_decision_maker',
            'active_linear_writer',
        ];

        foreach ($activeFields as $field) {
            if (isset($validated[$field])) {
                $updateData[$field] = $validated[$field];
            }
        }

        // Process use_llm fields - check both validated and raw input
        // They can be null (global), true (LLM), or false (heuristic)
        // Note: null values might not be in $validated due to 'sometimes' rule
        $llmFields = [
            'use_llm_interpreter',
            'use_llm_classifier',
            'use_llm_validator',
            'use_llm_prioritizer',
            'use_llm_decision_maker',
            'use_llm_linear_writer',
        ];

        foreach ($llmFields as $field) {
            // Check if field was sent in the request (even if null)
            if ($request->has($field) || array_key_exists($field, $request->all())) {
                // Get the processed value from the request (after prepareForValidation)
                // This handles null values correctly
                $value = $request->input($field);

                // Convert empty string to null if needed (should already be null from prepareForValidation)
                if ($value === '') {
                    $value = null;
                }

                $updateData[$field] = $value;
            }
        }

        // Only update if there's data to update
        if (! empty($updateData)) {
            $settings->update($updateData);
        }

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully.',
            ]);
        }

        return redirect()->route('settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}
