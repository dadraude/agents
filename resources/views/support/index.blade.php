@extends('layouts.demo')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Support Ticket Processor</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Manage and process support tickets with AI agents
        </p>
    </div>

    <!-- Search and Filters -->
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <form method="GET" action="{{ route('support.index') }}" class="space-y-4">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="{{ $search ?? '' }}" 
                           placeholder="Search by title or description..."
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Severity Filter -->
                <div>
                    <label for="severity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Severity</label>
                    <select id="severity" 
                            name="severity" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All</option>
                        <option value="critical" @if(($severity ?? '') === 'critical') selected @endif>Critical</option>
                        <option value="high" @if(($severity ?? '') === 'high') selected @endif>High</option>
                        <option value="medium" @if(($severity ?? '') === 'medium') selected @endif>Medium</option>
                        <option value="low" @if(($severity ?? '') === 'low') selected @endif>Low</option>
                    </select>
                </div>

                <!-- Priority Filter -->
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priority</label>
                    <select id="priority" 
                            name="priority" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All</option>
                        @php
                            $priorities = \App\Models\SupportTicket::distinct()->whereNotNull('priority')->pluck('priority')->sort()->values();
                        @endphp
                        @foreach($priorities as $priorityValue)
                            <option value="{{ $priorityValue }}" @if(($priority ?? '') === $priorityValue) selected @endif>{{ ucfirst($priorityValue) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Product Filter -->
                <div>
                    <label for="product" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product</label>
                    <select id="product" 
                            name="product" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All</option>
                        @php
                            $products = \App\Models\SupportTicket::distinct()->whereNotNull('product')->pluck('product')->sort()->values();
                        @endphp
                        @foreach($products as $productValue)
                            <option value="{{ $productValue }}" @if(($product ?? '') === $productValue) selected @endif>{{ strtoupper($productValue) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Actions -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Apply Filters
                    </button>
                    <a href="{{ route('support.index', ['tab' => $activeTab]) }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex gap-6" aria-label="Tabs">
            <a href="{{ route('support.index', array_merge(request()->query(), ['tab' => 'pending'])) }}" 
               class="@if($activeTab === 'pending') border-blue-500 text-blue-600 dark:text-blue-400 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 @endif whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors">
                Pending
                <span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full @if($activeTab === 'pending') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 @else bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 @endif">
                    {{ $pendingCount }}
                </span>
            </a>
            <a href="{{ route('support.index', array_merge(request()->query(), ['tab' => 'needs_review'])) }}" 
               class="@if($activeTab === 'needs_review') border-blue-500 text-blue-600 dark:text-blue-400 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 @endif whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors">
                Needs Review
                <span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full @if($activeTab === 'needs_review') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 @else bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 @endif">
                    {{ $needsReviewCount }}
                </span>
            </a>
            <a href="{{ route('support.index', array_merge(request()->query(), ['tab' => 'completed'])) }}" 
               class="@if($activeTab === 'completed') border-blue-500 text-blue-600 dark:text-blue-400 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 @endif whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors">
                Completed
                <span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full @if($activeTab === 'completed') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 @else bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 @endif">
                    {{ $completedCount }}
                </span>
            </a>
        </nav>
    </div>

    <form id="tickets-form" method="POST" action="{{ route('support.processBatch') }}">
        @csrf
        @if($activeTab === 'pending')
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
                                @if($activeTab === 'pending')
                                    <input type="checkbox" name="ticket_ids[]" value="{{ $ticket->id }}" class="ticket-checkbox mt-1 w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                @endif
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2 flex-wrap">
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
                                        
                                        @if($activeTab === 'needs_review')
                                            @php
                                                $reviewInfo = $ticket->getReviewInfo();
                                            @endphp
                                            @if($reviewInfo)
                                                @if($reviewInfo['type'] === 'escalated')
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300" title="{{ $reviewInfo['reason'] }}">
                                                        ⚠️ Escalated
                                                    </span>
                                                @elseif($reviewInfo['type'] === 'needs_more_info')
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300" title="{{ $reviewInfo['reason'] }}">
                                                        ℹ️ Needs Info
                                                    </span>
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                    <div class="flex items-start justify-between gap-2">
                                        <a href="{{ route('support.show', $ticket->id) }}" class="block flex-1">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 hover:text-blue-600 dark:hover:text-blue-400">
                                                {{ $ticket->title ?? 'No title' }}
                                            </h3>
                                        </a>
                                        @if($ticket->status === 'processed')
                                            <a href="{{ route('support.agents', $ticket->id) }}" class="flex-shrink-0 inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-400 bg-green-100 dark:bg-green-900/30 hover:bg-green-200 dark:hover:bg-green-900/50 rounded transition-colors" title="View processing results">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Results
                                            </a>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                        {{ $ticket->description ?? 'No description' }}
                                    </p>
                                    
                                    @if($activeTab === 'needs_review')
                                        @php
                                            $reviewInfo = $ticket->getReviewInfo();
                                        @endphp
                                        @if($reviewInfo)
                                            <div class="mt-3 p-3 rounded-lg
                                                @if($reviewInfo['type'] === 'escalated') bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800
                                                @else bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800
                                                @endif">
                                                <p class="text-sm font-medium
                                                    @if($reviewInfo['type'] === 'escalated') text-blue-900 dark:text-blue-200
                                                    @else text-yellow-900 dark:text-yellow-200
                                                    @endif mb-1">
                                                    @if($reviewInfo['type'] === 'escalated')
                                                        Escalated to Development
                                                    @else
                                                        Missing Information
                                                    @endif
                                                </p>
                                                <p class="text-xs
                                                    @if($reviewInfo['type'] === 'escalated') text-blue-800 dark:text-blue-300
                                                    @else text-yellow-800 dark:text-yellow-300
                                                    @endif">
                                                    {{ $reviewInfo['reason'] }}
                                                </p>
                                                @if($reviewInfo['type'] === 'escalated' && isset($reviewInfo['data']['linear_issue_url']))
                                                    @if($reviewInfo['data']['linear_issue_url'] === 'dry-run')
                                                        <span class="mt-2 inline-flex items-center gap-1 text-xs text-yellow-600 dark:text-yellow-400">
                                                            Dry run (no API key)
                                                        </span>
                                                    @else
                                                        <a href="{{ $reviewInfo['data']['linear_issue_url'] }}" target="_blank" class="mt-2 inline-flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                                            View Linear Issue
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                            </svg>
                                                        </a>
                                                    @endif
                                                @endif
                                                @if($reviewInfo['type'] === 'needs_more_info' && !empty($reviewInfo['data']))
                                                    <ul class="mt-2 list-disc list-inside text-xs
                                                        @if($reviewInfo['type'] === 'needs_more_info') text-yellow-800 dark:text-yellow-300
                                                        @endif">
                                                        @foreach($reviewInfo['data'] as $missing)
                                                            <li>{{ ucfirst(str_replace('_', ' ', $missing)) }}</li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </div>
                                        @endif
                                    @endif
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

    @if($activeTab === 'pending')
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
                    e.preventDefault();
                    
                    const checkedBoxes = document.querySelectorAll('.ticket-checkbox:checked');
                    const selectedCount = checkedBoxes.length;
                    
                    if (selectedCount === 0) {
                        alert('Please select at least one ticket to process.');
                        return false;
                    }
                    
                    // Get selected ticket IDs
                    const ticketIds = Array.from(checkedBoxes).map(cb => cb.value);
                    console.log('Processing tickets:', ticketIds);
                    
                    // Show progress overlay with streaming
                    showProgressOverlay(ticketIds);
                });

                updateSelectedCount();
            });

            function showProgressOverlay(ticketIds) {
                // Get ticket titles for display
                const ticketElements = ticketIds.map(id => {
                    const checkbox = document.querySelector(`.ticket-checkbox[value="${id}"]`);
                    const ticketCard = checkbox?.closest('.bg-white, .dark\\:bg-gray-800');
                    const titleElement = ticketCard?.querySelector('h3');
                    return {
                        id: id,
                        title: titleElement?.textContent?.trim() || `Ticket #${id}`,
                    };
                });

                // Create overlay with progress tracking
                const overlay = document.createElement('div');
                overlay.id = 'progress-overlay';
                overlay.className = 'fixed inset-0 bg-black/50 dark:bg-black/70 z-50 flex items-center justify-center p-4 overflow-y-auto';
                
                const ticketProgressHtml = ticketElements.map(ticket => `
                    <div class="ticket-progress-item" data-ticket-id="${ticket.id}">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">${ticket.title}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Ticket #${ticket.id}</p>
                            </div>
                            <div class="ticket-status ml-4">
                                <span class="text-xs px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">Waiting...</span>
                            </div>
                        </div>
                        <div class="ticket-agents mt-2 space-y-1"></div>
                    </div>
                `).join('');

                overlay.innerHTML = `
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-3xl w-full mx-4 my-8">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Processing Tickets</h3>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    <span class="processed-count">0</span> / <span class="total-count">${ticketIds.length}</span> completed
                                </div>
                            </div>
                            <div class="space-y-4 max-h-96 overflow-y-auto">
                                ${ticketProgressHtml}
                            </div>
                            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <button id="close-overlay" class="hidden w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(overlay);

                // Disable form elements
                document.getElementById('process-selected').disabled = true;
                document.getElementById('select-all').disabled = true;
                document.querySelectorAll('.ticket-checkbox').forEach(cb => cb.disabled = true);

                // Connect to stream
                connectToBatchStream(ticketIds, overlay);
            }

            function connectToBatchStream(ticketIds, overlay) {
                const formData = new FormData();
                formData.append('_token', document.querySelector('input[name="_token"]').value);
                ticketIds.forEach(id => formData.append('ticket_ids[]', id));

                fetch('{{ route("support.processBatchStream") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'text/event-stream',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to start stream');
                    }

                    const reader = response.body.getReader();
                    const decoder = new TextDecoder();
                    let buffer = '';

                    const agentOrder = ['Interpreter', 'Classifier', 'Validator', 'Prioritizer', 'DecisionMaker', 'LinearWriter'];

                    function readStream() {
                        reader.read().then(({ done, value }) => {
                            if (done) {
                                return;
                            }

                            buffer += decoder.decode(value, { stream: true });
                            const parts = buffer.split('\n\n');
                            buffer = parts.pop() || '';

                            parts.forEach(part => {
                                if (!part.trim()) return;

                                let eventType = null;
                                let data = '';

                                part.split('\n').forEach(line => {
                                    if (line.startsWith('event: ')) {
                                        eventType = line.substring(7).trim();
                                    } else if (line.startsWith('data: ')) {
                                        data += (data ? '\n' : '') + line.substring(6);
                                    }
                                });

                                if (eventType && data) {
                                    try {
                                        const parsed = JSON.parse(data);

                                        if (eventType === 'batch-start') {
                                            overlay.querySelector('.total-count').textContent = parsed.totalTickets;
                                        } else if (eventType === 'ticket-start') {
                                            updateTicketStatus(overlay, parsed.ticketId, 'processing', 'Processing...');
                                        } else if (eventType === 'ticket-agent-progress') {
                                            updateTicketAgentProgress(overlay, parsed.ticketId, parsed.agent, parsed.status, parsed.decision);
                                        } else if (eventType === 'ticket-complete') {
                                            updateTicketStatus(overlay, parsed.ticketId, 'completed', 'Completed');
                                            overlay.querySelector('.processed-count').textContent = parsed.processedCount;
                                        } else if (eventType === 'ticket-error') {
                                            updateTicketStatus(overlay, parsed.ticketId, 'error', 'Error: ' + parsed.error);
                                        } else if (eventType === 'batch-complete') {
                                            overlay.querySelector('.processed-count').textContent = parsed.processedCount;
                                            const closeButton = overlay.querySelector('#close-overlay');
                                            closeButton.classList.remove('hidden');
                                            closeButton.addEventListener('click', () => {
                                                overlay.remove();
                                                window.location.href = parsed.redirectUrl;
                                            });
                                        }
                                    } catch (e) {
                                        console.error('Error parsing event data:', e, data);
                                    }
                                }
                            });

                            readStream();
                        }).catch(error => {
                            console.error('Stream error:', error);
                            updateBatchError(overlay, 'Error reading stream: ' + error.message);
                        });
                    }

                    readStream();
                })
                .catch(error => {
                    console.error('Error connecting to stream:', error);
                    updateBatchError(overlay, 'Failed to connect to processing stream');
                });
            }

            function updateTicketStatus(overlay, ticketId, status, statusText) {
                const ticketItem = overlay.querySelector(`[data-ticket-id="${ticketId}"]`);
                if (!ticketItem) return;

                const statusEl = ticketItem.querySelector('.ticket-status span');
                if (statusEl) {
                    statusEl.textContent = statusText;
                    statusEl.className = 'text-xs px-2 py-1 rounded-full';
                    if (status === 'processing') {
                        statusEl.className += ' bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300';
                    } else if (status === 'completed') {
                        statusEl.className += ' bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300';
                    } else if (status === 'error') {
                        statusEl.className += ' bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300';
                    } else {
                        statusEl.className += ' bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400';
                    }
                }
            }

            function updateTicketAgentProgress(overlay, ticketId, agentName, status, decision) {
                const ticketItem = overlay.querySelector(`[data-ticket-id="${ticketId}"]`);
                if (!ticketItem) return;

                let agentsContainer = ticketItem.querySelector('.ticket-agents');
                if (!agentsContainer) {
                    agentsContainer = document.createElement('div');
                    agentsContainer.className = 'ticket-agents mt-2 space-y-1';
                    ticketItem.appendChild(agentsContainer);
                }

                let agentEl = agentsContainer.querySelector(`[data-agent="${agentName}"]`);
                if (!agentEl) {
                    agentEl = document.createElement('div');
                    agentEl.className = 'flex items-center gap-2 text-xs';
                    agentEl.setAttribute('data-agent', agentName);
                    agentsContainer.appendChild(agentEl);
                }

                const statusClass = status === 'processing' 
                    ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'
                    : status === 'completed'
                    ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
                    : status === 'bypassed'
                    ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300'
                    : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400';

                agentEl.innerHTML = `
                    <span class="w-2 h-2 rounded-full ${status === 'processing' ? 'bg-blue-500 animate-pulse' : status === 'completed' ? 'bg-green-500' : status === 'bypassed' ? 'bg-yellow-500' : 'bg-gray-400'}"></span>
                    <span class="font-medium">${agentName}:</span>
                    <span class="px-2 py-0.5 rounded ${statusClass}">${status}</span>
                    ${decision ? `<span class="text-gray-600 dark:text-gray-400 italic">${decision}</span>` : ''}
                `;
            }

            function updateBatchError(overlay, errorMessage) {
                const errorEl = document.createElement('div');
                errorEl.className = 'mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg';
                errorEl.innerHTML = `
                    <p class="text-sm text-red-800 dark:text-red-200">${errorMessage}</p>
                    <button onclick="this.closest('#progress-overlay').remove(); window.location.reload();" class="mt-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Close
                    </button>
                `;
                overlay.querySelector('.space-y-4').appendChild(errorEl);
            }
        </script>
    @endif
@endsection
