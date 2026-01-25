@extends('layouts.demo')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Settings</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Configure agent activation settings
        </p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <form id="settings-form" method="POST" action="{{ route('settings.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Agent Activation Settings</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Enable or disable agents in the workflow. Disabled agents will be bypassed.
                </p>
            </div>

            <div class="space-y-0">
                @foreach([
                    'interpreter' => 'Interpreter',
                    'classifier' => 'Classifier',
                    'validator' => 'Validator',
                    'prioritizer' => 'Prioritizer',
                    'decision_maker' => 'Decision Maker',
                    'linear_writer' => 'Linear Writer',
                ] as $field => $label)
                    <div class="flex items-center justify-between py-4 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                        <div class="flex-1">
                            <label for="active_{{ $field }}" class="text-base font-medium text-gray-900 dark:text-white">
                                {{ $label }}
                            </label>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Enable {{ $label }} in the workflow
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   name="active_{{ $field }}" 
                                   id="active_{{ $field }}"
                                   value="1"
                                   data-label="{{ $label }}"
                                   @if($settings->{'active_' . $field}) checked @endif
                                   class="sr-only peer settings-checkbox">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                @endforeach
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('settings-form');
            const checkboxes = document.querySelectorAll('.settings-checkbox');
            let saveTimeout = null;
            let changedCheckbox = null;

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Store which checkbox was changed
                    changedCheckbox = this;

                    // Clear any existing timeout
                    if (saveTimeout) {
                        clearTimeout(saveTimeout);
                    }

                    // Debounce: wait 300ms before saving to avoid multiple requests
                    saveTimeout = setTimeout(() => {
                        saveSettings(changedCheckbox);
                    }, 300);
                });
            });

            function saveSettings(changedCheckbox) {
                const formData = new FormData();
                
                // Add CSRF token
                formData.append('_token', document.querySelector('input[name="_token"]').value);
                formData.append('_method', 'PUT');
                
                // Add all checkbox values explicitly (unchecked checkboxes don't send values)
                const activeFields = [
                    'active_interpreter',
                    'active_classifier',
                    'active_validator',
                    'active_prioritizer',
                    'active_decision_maker',
                    'active_linear_writer',
                ];

                activeFields.forEach(field => {
                    const checkbox = document.getElementById(field);
                    if (checkbox) {
                        // Send '1' if checked, '0' if unchecked
                        formData.append(field, checkbox.checked ? '1' : '0');
                    }
                });

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    },
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show toast notification with specific action
                        if (typeof showToast === 'function' && changedCheckbox) {
                            const label = changedCheckbox.getAttribute('data-label');
                            const isEnabled = changedCheckbox.checked;
                            const message = isEnabled ? `${label} enabled` : `${label} disabled`;
                            showToast(message, 'success');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error saving settings:', error);
                    if (typeof showToast === 'function') {
                        showToast('Error saving settings', 'error');
                    }
                });
            }
        });
    </script>
@endsection
