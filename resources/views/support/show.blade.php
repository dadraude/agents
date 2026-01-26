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
                        <form id="process-form" action="{{ route('support.processStream', $ticket->id) }}" method="POST">
                            @csrf
                            <button type="submit" id="process-button" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
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

    @if($ticket->status !== 'processed')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const processForm = document.getElementById('process-form');
                const processButton = document.getElementById('process-button');
                const agentsViewUrl = '{{ route("support.agents", $ticket->id) }}';
                
                if (processForm && processButton) {
                    processForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        showLoadingOverlay();
                        connectToStream();
                    });
                }

                const agents = [
                    { name: 'Interpreter', step: 1 },
                    { name: 'Classifier', step: 2 },
                    { name: 'Validator', step: 3 },
                    { name: 'Prioritizer', step: 4 },
                    { name: 'DecisionMaker', step: 5 },
                    { name: 'LinearWriter', step: 6 },
                ];

                // Store agent statuses and decisions for confirmation summary
                const agentsStatus = {};
                const agentsDecisions = {};

                function showLoadingOverlay() {
                    // Create overlay
                    const overlay = document.createElement('div');
                    overlay.id = 'loading-overlay';
                    overlay.className = 'fixed inset-0 bg-black/50 dark:bg-black/70 z-50 flex items-center justify-center';
                    
                    const agentsList = agents.map(agent => `
                        <div class="agent-item flex items-center gap-3 p-3 rounded-lg" data-agent="${agent.name}">
                            <div class="agent-icon w-6 h-6 flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" data-icon="pending">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                <div class="hidden spinner" data-icon="processing">
                                    <div class="w-5 h-5 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                                </div>
                                <svg class="hidden w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" data-icon="completed">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">${agent.name}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Pending...</div>
                            </div>
                        </div>
                    `).join('');

                    overlay.innerHTML = `
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-8 shadow-xl max-w-lg w-full mx-4">
                            <div class="flex flex-col gap-6">
                                <div class="text-center">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Processing ticket...</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Please wait while the AI agents analyze and process this ticket.</p>
                                </div>
                                <div class="space-y-2">
                                    ${agentsList}
                                </div>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(overlay);
                    
                    // Disable button
                    processButton.disabled = true;
                    processButton.classList.add('opacity-50', 'cursor-not-allowed');
                }

                function updateAgentStatus(agentName, status, decision = null) {
                    // Store status and decision for confirmation summary
                    agentsStatus[agentName] = status;
                    if (decision) {
                        agentsDecisions[agentName] = decision;
                    }

                    const agentItem = document.querySelector(`[data-agent="${agentName}"]`);
                    if (!agentItem) return;

                    const icons = agentItem.querySelectorAll('[data-icon]');
                    icons.forEach(icon => icon.classList.add('hidden'));

                    const statusText = agentItem.querySelector('.text-xs');
                    const agentContent = agentItem.querySelector('.flex-1');
                    
                    if (status === 'processing') {
                        agentItem.querySelector('[data-icon="processing"]').classList.remove('hidden');
                        statusText.textContent = 'Processing...';
                        statusText.className = 'text-xs text-blue-600 dark:text-blue-400';
                        agentItem.className = 'agent-item flex items-center gap-3 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20';
                        // Remove decision if exists
                        const decisionEl = agentItem.querySelector('.agent-decision');
                        if (decisionEl) decisionEl.remove();
                    } else if (status === 'completed') {
                        agentItem.querySelector('[data-icon="completed"]').classList.remove('hidden');
                        statusText.textContent = 'Completed';
                        statusText.className = 'text-xs text-green-600 dark:text-green-400';
                        agentItem.className = 'agent-item flex items-center gap-3 p-3 rounded-lg bg-green-50 dark:bg-green-900/20';
                        
                        // Add or update decision
                        let decisionEl = agentItem.querySelector('.agent-decision');
                        if (decision) {
                            if (!decisionEl) {
                                decisionEl = document.createElement('div');
                                decisionEl.className = 'agent-decision text-xs text-gray-600 dark:text-gray-400 mt-1 italic';
                                agentContent.appendChild(decisionEl);
                            }
                            decisionEl.textContent = decision;
                        } else if (decisionEl) {
                            decisionEl.remove();
                        }
                    } else if (status === 'bypassed') {
                        agentItem.querySelector('[data-icon="pending"]').classList.remove('hidden');
                        statusText.textContent = 'Bypassed';
                        statusText.className = 'text-xs text-yellow-600 dark:text-yellow-400';
                        agentItem.className = 'agent-item flex items-center gap-3 p-3 rounded-lg bg-yellow-50 dark:bg-yellow-900/20';
                        // Remove decision if exists
                        const decisionEl = agentItem.querySelector('.agent-decision');
                        if (decisionEl) decisionEl.remove();
                    } else if (status === 'skipped') {
                        agentItem.querySelector('[data-icon="pending"]').classList.remove('hidden');
                        statusText.textContent = 'Skipped';
                        statusText.className = 'text-xs text-gray-500 dark:text-gray-400';
                        agentItem.className = 'agent-item flex items-center gap-3 p-3 rounded-lg opacity-50';
                        // Remove decision if exists
                        const decisionEl = agentItem.querySelector('.agent-decision');
                        if (decisionEl) decisionEl.remove();
                    }
                }

                function connectToStream() {
                    const form = document.getElementById('process-form');
                    const formData = new FormData(form);
                    
                    // Use fetch with POST to start the stream
                    fetch(form.action, {
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
                                            
                                            if (eventType === 'agent-progress') {
                                                updateAgentStatus(parsed.agent, parsed.status, parsed.decision || null);
                                            } else if (eventType === 'workflow-complete') {
                                                showConfirmationSummary(parsed);
                                            } else if (eventType === 'workflow-error') {
                                                showError(parsed.error || 'Unknown error occurred');
                                            }
                                        } catch (e) {
                                            console.error('Error parsing event data:', e, data);
                                        }
                                    }
                                });

                                readStream();
                            }).catch(error => {
                                console.error('Stream error:', error);
                                showError('Error reading stream: ' + error.message);
                            });
                        }

                        readStream();
                    })
                    .catch(error => {
                        console.error('Error connecting to stream:', error);
                        showError('Failed to connect to processing stream');
                    });
                }

                function getAgentDecision(agentName, state) {
                    if (!state) return null;
                    
                    switch (agentName) {
                        case 'Interpreter':
                            if (state.intent) return `Intent: ${state.intent}`;
                            if (state.summary) {
                                const summary = state.summary.length > 50 ? state.summary.substring(0, 50) + '...' : state.summary;
                                return summary;
                            }
                            return null;
                        case 'Classifier':
                            const parts = [];
                            if (state.type) parts.push(state.type);
                            if (state.area) parts.push(state.area.toUpperCase());
                            return parts.length > 0 ? parts.join(' • ') : null;
                        case 'Validator':
                            if (state.isSufficient === false) {
                                const missing = state.missingInfo && state.missingInfo.length > 0 
                                    ? state.missingInfo.length + ' missing' 
                                    : 'Insufficient';
                                return `Missing info: ${missing}`;
                            }
                            return 'Sufficient info';
                        case 'Prioritizer':
                            if (state.priorityScore !== null && state.priorityScore !== undefined) {
                                return `Priority: ${state.priorityScore.toFixed(1)}`;
                            }
                            if (state.severity) return `Severity: ${state.severity}/5`;
                            return null;
                        case 'DecisionMaker':
                            if (state.decisionReason) {
                                const reason = state.decisionReason.length > 60 ? state.decisionReason.substring(0, 60) + '...' : state.decisionReason;
                                return reason;
                            }
                            if (state.shouldEscalate) return 'Escalate to agents';
                            return 'Auto-process';
                        case 'LinearWriter':
                            if (state.linearIssueUrl) return 'Issue created';
                            return 'No issue needed';
                        default:
                            return null;
                    }
                }

                function showConfirmationSummary(workflowData) {
                    const overlay = document.getElementById('loading-overlay');
                    if (!overlay) return;

                    const status = workflowData.status || 'unknown';
                    const redirectUrl = workflowData.redirectUrl || agentsViewUrl;
                    const state = workflowData.state || {};
                    
                    // Get status badge classes
                    let statusBadgeClass = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                    let statusText = status;
                    if (status === 'processed') {
                        statusBadgeClass = 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300';
                        statusText = 'Processed';
                    } else if (status === 'escalated') {
                        statusBadgeClass = 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300';
                        statusText = 'Escalated';
                    } else if (status === 'needs_more_info') {
                        statusBadgeClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300';
                        statusText = 'Needs More Info';
                    } else {
                        statusText = status.charAt(0).toUpperCase() + status.slice(1).replace(/_/g, ' ');
                    }

                    // Build agents list HTML
                    const agentsList = agents.map(agent => {
                        const agentStatus = agentsStatus[agent.name] || 'pending';
                        // Use real-time decision if available, otherwise fallback to state
                        const decision = agentsDecisions[agent.name] || getAgentDecision(agent.name, state);
                        let iconHtml = '';
                        let statusLabel = '';
                        let itemClass = 'agent-item flex items-center gap-3 p-3 rounded-lg';
                        
                        if (agentStatus === 'completed') {
                            iconHtml = '<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                            statusLabel = 'Completed';
                            itemClass += ' bg-green-50 dark:bg-green-900/20';
                        } else if (agentStatus === 'bypassed') {
                            iconHtml = '<svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>';
                            statusLabel = 'Bypassed';
                            itemClass += ' bg-yellow-50 dark:bg-yellow-900/20';
                        } else if (agentStatus === 'skipped') {
                            iconHtml = '<svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>';
                            statusLabel = 'Skipped';
                            itemClass += ' opacity-50';
                        } else {
                            iconHtml = '<svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>';
                            statusLabel = 'Pending';
                        }

                        const decisionHtml = decision ? `
                            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1 italic">
                                ${decision}
                            </div>
                        ` : '';

                        return `
                            <div class="${itemClass}">
                                <div class="agent-icon w-6 h-6 flex items-center justify-center flex-shrink-0">
                                    ${iconHtml}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300">${agent.name}</div>
                                        <div class="text-xs ${agentStatus === 'completed' ? 'text-green-600 dark:text-green-400' : agentStatus === 'bypassed' ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-500 dark:text-gray-400'} whitespace-nowrap">${statusLabel}</div>
                                    </div>
                                    ${decisionHtml}
                                </div>
                            </div>
                        `;
                    }).join('');

                    // Check if Linear issue was created (escalated status usually means Linear issue was created)
                    const linearInfo = status === 'escalated' ? `
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                A Linear issue has been created for this ticket.
                            </p>
                        </div>
                    ` : '';

                    overlay.innerHTML = `
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-8 shadow-xl max-w-lg w-full mx-4">
                            <div class="flex flex-col gap-6">
                                <div class="text-center">
                                    <div class="w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Processament completat</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Els agents AI han processat el ticket correctament.</p>
                                    <span class="inline-block px-3 py-1 text-sm font-medium rounded-full ${statusBadgeClass}">
                                        ${statusText}
                                    </span>
                                </div>
                                
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Resum dels agents</h4>
                                    <div class="space-y-2">
                                        ${agentsList}
                                    </div>
                                </div>

                                ${linearInfo}

                                <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <button onclick="window.location.href='${redirectUrl}'" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Confirmar i veure resultats
                                    </button>
                                    <button onclick="document.getElementById('loading-overlay').remove(); document.getElementById('process-button').disabled = false; document.getElementById('process-button').classList.remove('opacity-50', 'cursor-not-allowed');" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                                        Tancar
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                }

                function showError(message) {
                    const overlay = document.getElementById('loading-overlay');
                    if (overlay) {
                        overlay.innerHTML = `
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-8 shadow-xl max-w-md w-full mx-4">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </div>
                                    <div class="text-center">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Error</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">${message}</p>
                                        <button onclick="document.getElementById('loading-overlay').remove(); document.getElementById('process-button').disabled = false; document.getElementById('process-button').classList.remove('opacity-50', 'cursor-not-allowed');" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                            Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                }
            });
        </script>
    @endif
@endsection
