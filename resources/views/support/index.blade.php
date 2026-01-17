@extends('layouts.demo')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Support Ticket Processor</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Manage and process support tickets with AI agents
        </p>
    </div>

    <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex gap-6" aria-label="Tabs">
            <a href="{{ route('support.index', ['tab' => 'pending']) }}" 
               class="@if($activeTab === 'pending') border-blue-500 text-blue-600 dark:text-blue-400 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 @endif whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors">
                Pending
            </a>
            <a href="{{ route('support.index', ['tab' => 'processed']) }}" 
               class="@if($activeTab === 'processed') border-blue-500 text-blue-600 dark:text-blue-400 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 @endif whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors">
                Processed
            </a>
        </nav>
    </div>

    <form id="tickets-form" method="POST" action="{{ route('support.processBatch') }}">
        @csrf
        @if($activeTab !== 'processed')
            <div class="mb-4 flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="select-all" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Select all</span>
                </label>
                <button type="submit" id="process-selected" disabled class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors">
                    Process selected (<span id="selected-count">0</span>)
                </button>
            </div>
        @endif

        <div class="grid gap-4">
            @forelse ($tickets as $ticket)
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition-colors">
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3 flex-1">
                                @if($activeTab !== 'processed')
                                    <input type="checkbox" name="ticket_ids[]" value="{{ $ticket->id }}" class="ticket-checkbox mt-1 w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                @endif
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <a href="{{ route('support.show', $ticket->id) }}" class="font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                            {{ $ticket->id }}
                                        </a>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                            @if($ticket->status === 'new') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300
                                            @elseif($ticket->status === 'in_review') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                                            @elseif($ticket->status === 'processed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $ticket->status ?? 'unknown')) }}
                                        </span>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                            @if($ticket->severity === 'critical') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300
                                            @elseif($ticket->severity === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300
                                            @elseif($ticket->severity === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                            @endif">
                                            {{ ucfirst($ticket->severity ?? 'normal') }}
                                        </span>
                                    </div>
                                    <a href="{{ route('support.show', $ticket->id) }}" class="block">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 hover:text-blue-600 dark:hover:text-blue-400">
                                            {{ $ticket->title ?? 'No title' }}
                                        </h3>
                                    </a>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                        {{ $ticket->description ?? 'No description' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $ticket->created_at->diffForHumans() }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                {{ $ticket->customer_name ?? 'Unknown client' }}
                            </span>
                            @if($ticket->product)
                                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">
                                    {{ strtoupper($ticket->product) }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-12 text-center">
                    <p class="text-gray-500 dark:text-gray-400">No tickets available</p>
                </div>
            @endforelse
        </div>
    </form>

    @if($tickets->hasPages())
        <div class="mt-6">
            {{ $tickets->links() }}
        </div>
    @endif

    @if($activeTab !== 'processed')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const selectAllCheckbox = document.getElementById('select-all');
                const ticketCheckboxes = document.querySelectorAll('.ticket-checkbox');
                const processButton = document.getElementById('process-selected');
                const selectedCountSpan = document.getElementById('selected-count');

                if (!selectAllCheckbox || !processButton) {
                    return;
                }

                function updateSelectedCount() {
                    const selectedCount = document.querySelectorAll('.ticket-checkbox:checked').length;
                    selectedCountSpan.textContent = selectedCount;
                    processButton.disabled = selectedCount === 0;
                }

                selectAllCheckbox.addEventListener('change', function() {
                    ticketCheckboxes.forEach(checkbox => {
                        checkbox.checked = selectAllCheckbox.checked;
                    });
                    updateSelectedCount();
                });

                ticketCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        updateSelectedCount();
                        const allChecked = Array.from(ticketCheckboxes).every(cb => cb.checked);
                        const someChecked = Array.from(ticketCheckboxes).some(cb => cb.checked);
                        selectAllCheckbox.checked = allChecked;
                        selectAllCheckbox.indeterminate = someChecked && !allChecked;
                    });
                });

                document.getElementById('tickets-form').addEventListener('submit', function(e) {
                    const selectedCount = document.querySelectorAll('.ticket-checkbox:checked').length;
                    if (selectedCount === 0) {
                        e.preventDefault();
                        alert('Please select at least one ticket to process.');
                        return false;
                    }
                });

                updateSelectedCount();
            });
        </script>
    @endif
@endsection
