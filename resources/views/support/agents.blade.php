@extends('layouts.demo')

@section('content')
    <div class="mb-6">
        <a href="{{ route('support.show', $ticket->id) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 flex items-center gap-2 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to ticket
        </a>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Agent Pipeline</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Viewing ticket processing {{ $ticket->id ?? 'N/A' }} by AI agents
        </p>
    </div>

    @if(!$workflowResult)
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
            <p class="text-yellow-800 dark:text-yellow-200">
                No workflow results available. Go back to ticket details and process it with agents.
            </p>
        </div>
    @else
        <div class="space-y-6">
            <!-- Processing Summary -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Processing Summary</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Final Status</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                            <span class="px-2 py-1 rounded-full text-xs
                                @if(($workflowResult['status'] ?? '') === 'processed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                                @elseif(($workflowResult['status'] ?? '') === 'escalated') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300
                                @elseif(($workflowResult['status'] ?? '') === 'needs_more_info') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                                @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $workflowResult['status'] ?? 'unknown')) }}
                            </span>
                        </dd>
                    </div>
                    @if(isset($workflowResult['state']['type']))
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Detected Type</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ ucfirst($workflowResult['state']['type'] ?? 'N/A') }}</dd>
                        </div>
                    @endif
                    @if(isset($workflowResult['state']['area']))
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Area</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ strtoupper($workflowResult['state']['area'] ?? 'N/A') }}</dd>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Agent Timeline -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Agent Execution</h2>
                <div class="space-y-6">
                    @php
                        $agentOrder = ['Interpreter', 'Classifier', 'Validator', 'Prioritizer', 'DecisionMaker', 'LinearWriter'];
                        $trace = $workflowResult['state']['trace'] ?? [];
                        $traceByAgent = [];
                        foreach ($trace as $entry) {
                            $agentName = $entry['agent'] ?? 'Unknown';
                            $traceByAgent[$agentName] = $entry;
                        }
                    @endphp

                    @foreach ($agentOrder as $agentName)
                        @php
                            $entry = $traceByAgent[$agentName] ?? null;
                        @endphp
                        @if($entry)
                            <div class="relative pl-8 pb-6 border-l-2 border-gray-200 dark:border-gray-700 last:border-0 last:pb-0">
                                <div class="absolute -left-2 top-0 w-4 h-4 bg-blue-600 rounded-full border-2 border-white dark:border-gray-800"></div>
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $agentName }}</h3>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ isset($entry['ts']) ? \Carbon\Carbon::parse($entry['ts'])->format('H:i:s') : 'N/A' }}
                                    </span>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 mt-3">
                                    <pre class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap font-mono">{{ json_encode($entry['data'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                </div>
                            </div>
                        @endif
                    @endforeach

                    @if(empty($trace))
                        <p class="text-sm text-gray-500 dark:text-gray-400">No trace available</p>
                    @endif
                </div>
            </div>

            <!-- Complete State -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Complete State</h2>
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                    <pre class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap font-mono overflow-x-auto">{{ json_encode($workflowResult['state'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>

            <!-- Decisions and Results -->
            @if(isset($workflowResult['state']['shouldEscalate']) && $workflowResult['state']['shouldEscalate'])
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                    <h3 class="text-base font-semibold text-blue-900 dark:text-blue-200 mb-2">Ticket Escalated</h3>
                    <p class="text-sm text-blue-800 dark:text-blue-300">
                        {{ $workflowResult['state']['decisionReason'] ?? 'The ticket has been escalated to agents.' }}
                    </p>
                    @if(isset($workflowResult['state']['linearIssueUrl']))
                        <a href="{{ $workflowResult['state']['linearIssueUrl'] }}" target="_blank" class="mt-3 inline-flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                            View issue in Linear
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    @endif
                </div>
            @endif

            @if(isset($workflowResult['state']['isSufficient']) && !$workflowResult['state']['isSufficient'])
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
                    <h3 class="text-base font-semibold text-yellow-900 dark:text-yellow-200 mb-2">Insufficient Information</h3>
                    <p class="text-sm text-yellow-800 dark:text-yellow-300 mb-2">
                        The ticket needs more information to be processed:
                    </p>
                    <ul class="list-disc list-inside text-sm text-yellow-800 dark:text-yellow-300">
                        @foreach(($workflowResult['state']['missingInfo'] ?? []) as $missing)
                            <li>{{ ucfirst(str_replace('_', ' ', $missing)) }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif
@endsection
