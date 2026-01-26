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

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mt-6">
        <form id="llm-settings-form" method="POST" action="{{ route('settings.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">LLM Configuration</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Configure whether each agent should use LLM or heuristic processing. 
                    <span class="font-medium">Global</span> uses the default configuration from <code class="text-xs bg-gray-100 dark:bg-gray-700 px-1 py-0.5 rounded">AI_USE_LLM</code>.
                </p>
                @php
                    $globalLLMEnabled = config('neuron-ai.use_llm', false);
                @endphp
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    Current global setting: <span class="font-medium">{{ $globalLLMEnabled ? 'LLM enabled' : 'LLM disabled' }}</span>
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
                    @php
                        $currentValue = $settings->{'use_llm_' . $field};
                        $selectValue = $currentValue === null ? 'global' : ($currentValue ? 'llm' : 'heuristic');
                    @endphp
                    <div class="flex items-center justify-between py-4 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                        <div class="flex-1">
                            <label for="use_llm_{{ $field }}" class="text-base font-medium text-gray-900 dark:text-white">
                                {{ $label }}
                            </label>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Choose processing method for {{ $label }}
                            </p>
                        </div>
                        <select name="use_llm_{{ $field }}" 
                                id="use_llm_{{ $field }}"
                                data-label="{{ $label }}"
                                class="llm-settings-select ml-4 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 px-3 py-2 min-w-[140px]">
                            <option value="global" @if($selectValue === 'global') selected @endif>Global</option>
                            <option value="llm" @if($selectValue === 'llm') selected @endif>LLM</option>
                            <option value="heuristic" @if($selectValue === 'heuristic') selected @endif>Heuristic</option>
                        </select>
                    </div>
                @endforeach
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('settings-form');
            const llmForm = document.getElementById('llm-settings-form');
            const checkboxes = document.querySelectorAll('.settings-checkbox');
            const llmSelects = document.querySelectorAll('.llm-settings-select');
            let saveTimeout = null;
            let changedElement = null;

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Store which checkbox was changed
                    changedElement = this;

                    // Clear any existing timeout
                    if (saveTimeout) {
                        clearTimeout(saveTimeout);
                    }

                    // Debounce: wait 300ms before saving to avoid multiple requests
                    saveTimeout = setTimeout(() => {
                        saveSettings(changedElement, 'active');
                    }, 300);
                });
            });

            llmSelects.forEach(select => {
                select.addEventListener('change', function() {
                    // Store which select was changed
                    changedElement = this;

                    // Clear any existing timeout
                    if (saveTimeout) {
                        clearTimeout(saveTimeout);
                    }

                    // Debounce: wait 300ms before saving to avoid multiple requests
                    saveTimeout = setTimeout(() => {
                        saveLLMSettings(changedElement);
                    }, 300);
                });
            });

            function saveSettings(changedElement, type) {
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
                        if (typeof showToast === 'function' && changedElement) {
                            const label = changedElement.getAttribute('data-label');
                            const isEnabled = changedElement.checked;
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

            function saveLLMSettings(changedSelect) {
                const formData = new FormData();
                
                // Add CSRF token
                formData.append('_token', llmForm.querySelector('input[name="_token"]').value);
                formData.append('_method', 'PUT');
                
                // Add all select values
                const llmFields = [
                    'use_llm_interpreter',
                    'use_llm_classifier',
                    'use_llm_validator',
                    'use_llm_prioritizer',
                    'use_llm_decision_maker',
                    'use_llm_linear_writer',
                ];

                llmFields.forEach(field => {
                    const select = document.getElementById(field);
                    if (select) {
                        const value = select.value;
                        // Convert 'global' to empty string (null), 'llm' to '1', 'heuristic' to '0'
                        if (value === 'global') {
                            formData.append(field, '');
                        } else if (value === 'llm') {
                            formData.append(field, '1');
                        } else {
                            formData.append(field, '0');
                        }
                    }
                });

                fetch(llmForm.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': llmForm.querySelector('input[name="_token"]').value,
                    },
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show toast notification with specific action
                        if (typeof showToast === 'function' && changedSelect) {
                            const label = changedSelect.getAttribute('data-label');
                            const value = changedSelect.value;
                            let message = '';
                            if (value === 'global') {
                                message = `${label} using global LLM setting`;
                            } else if (value === 'llm') {
                                message = `${label} set to use LLM`;
                            } else {
                                message = `${label} set to use heuristic`;
                            }
                            showToast(message, 'success');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error saving LLM settings:', error);
                    if (typeof showToast === 'function') {
                        showToast('Error saving LLM settings', 'error');
                    }
                });
            }
        });
    </script>
@endsection
