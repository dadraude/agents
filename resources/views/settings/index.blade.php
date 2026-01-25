@extends('layouts.demo')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Settings</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Configure agent activation settings
        </p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <form method="POST" action="{{ route('settings.update') }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Agent Activation Settings</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        Enable or disable agents in the workflow. Disabled agents will be bypassed.
                    </p>
                </div>

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
                                   @if($settings->{'active_' . $field}) checked @endif
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 flex justify-end gap-4">
                <a href="{{ route('support.index') }}" 
                   class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
@endsection
