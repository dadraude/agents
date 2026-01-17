@extends('layouts.demo')

@section('content')
    <div class="mb-6">
        <a href="{{ route('support.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 flex items-center gap-2 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to list
        </a>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $ticket->title ?? 'No title' }}</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Ticket {{ $ticket->id ?? 'N/A' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 text-sm font-medium rounded-full
                    @if($ticket->status === 'new') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300
                    @elseif($ticket->status === 'in_review') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                    @elseif($ticket->status === 'processed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                    @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                    @endif">
                    {{ ucfirst(str_replace('_', ' ', $ticket->status ?? 'unknown')) }}
                </span>
                <span class="px-3 py-1 text-sm font-medium rounded-full
                    @if($ticket->severity === 'critical') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300
                    @elseif($ticket->severity === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300
                    @elseif($ticket->severity === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                    @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                    @endif">
                    {{ ucfirst($ticket->severity ?? 'normal') }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Description</h2>
                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $ticket->description ?? 'No description' }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Actions</h2>
                <div class="flex flex-col gap-3">
                    @if($ticket->status !== 'processed')
                        <form action="{{ route('support.process', $ticket->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Process with AI agents
                            </button>
                        </form>
                    @endif
                    @php
                        $linearClient = app(\App\Integrations\Linear\LinearClient::class);
                    @endphp
                    @if($linearClient->isConfigured())
                        @if($ticket->linear_issue_url)
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                                <p class="text-sm text-green-800 dark:text-green-200 mb-2">
                                    Linear issue created
                                </p>
                                <a href="{{ $ticket->linear_issue_url }}" target="_blank" class="inline-flex items-center gap-2 text-sm text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 font-medium">
                                    View issue in Linear
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                </a>
                            </div>
                        @else
                            <form action="{{ route('support.createLinear', $ticket->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Create Linear Issue
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Ticket Information</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">ID</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $ticket->id ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Priority</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ ucfirst($ticket->priority ?? 'normal') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Product</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ strtoupper($ticket->product ?? 'N/A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Channel</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ ucfirst($ticket->channel ?? 'N/A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Created</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $ticket->created_at->format('d/m/Y H:i') }}
                        </dd>
                    </div>
                    @if($ticket->sla_deadline)
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">SLA Deadline</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $ticket->sla_deadline->format('d/m/Y H:i') }}
                            </dd>
                        </div>
                    @endif
                    @if($ticket->assigned_to)
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Assigned to</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $ticket->assigned_to }}</dd>
                        </div>
                    @endif
                    @if($ticket->linear_issue_url)
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Linear Issue</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">
                                <a href="{{ $ticket->linear_issue_url }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 inline-flex items-center gap-1">
                                    View in Linear
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                </a>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Client</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Name</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $ticket->customer_name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $ticket->customer_email ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Phone</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $ticket->customer_phone ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </div>

            @if($ticket->environment_device || $ticket->environment_os || $ticket->environment_app_version)
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Environment</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Device</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $ticket->environment_device ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Operating System</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $ticket->environment_os ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">App Version</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $ticket->environment_app_version ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>
            @endif
        </div>
    </div>
@endsection
