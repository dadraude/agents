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

        // Ensure all boolean fields are set with default false if not present
        $activeFields = [
            'active_interpreter',
            'active_classifier',
            'active_validator',
            'active_prioritizer',
            'active_decision_maker',
            'active_linear_writer',
        ];

        foreach ($activeFields as $field) {
            if (! isset($validated[$field])) {
                $validated[$field] = false;
            }
        }

        $settings->update($validated);

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
