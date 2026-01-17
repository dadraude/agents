@extends('layouts.demo')

@section('content')
    <div class="mb-6">
        <a href="{{ route('support.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 flex items-center gap-2 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Tornar al llistat
        </a>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $ticket['title'] ?? 'Sense títol' }}</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Ticket {{ $ticket['id'] ?? 'N/A' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 text-sm font-medium rounded-full
                    @if(($ticket['status'] ?? '') === 'nou') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300
                    @elseif(($ticket['status'] ?? '') === 'en_revisio') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                    @elseif(($ticket['status'] ?? '') === 'processat') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                    @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                    @endif">
                    {{ ucfirst(str_replace('_', ' ', $ticket['status'] ?? 'desconegut')) }}
                </span>
                <span class="px-3 py-1 text-sm font-medium rounded-full
                    @if(($ticket['severity'] ?? '') === 'critica') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300
                    @elseif(($ticket['severity'] ?? '') === 'alta') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300
                    @elseif(($ticket['severity'] ?? '') === 'mitjana') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                    @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                    @endif">
                    {{ ucfirst($ticket['severity'] ?? 'normal') }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Descripció</h2>
                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $ticket['description'] ?? 'Sense descripció' }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Accions</h2>
                <form action="{{ route('support.process', $ticket['id']) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Processar amb agents d'IA
                    </button>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Informació del ticket</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">ID</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $ticket['id'] ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Prioritat</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ ucfirst($ticket['priority'] ?? 'normal') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Producte</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ strtoupper($ticket['product'] ?? 'N/A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Canal</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ ucfirst($ticket['channel'] ?? 'N/A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Creat</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ \Carbon\Carbon::parse($ticket['created_at'] ?? now())->format('d/m/Y H:i') }}
                        </dd>
                    </div>
                    @if(isset($ticket['sla_deadline']))
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">SLA Deadline</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($ticket['sla_deadline'])->format('d/m/Y H:i') }}
                            </dd>
                        </div>
                    @endif
                    @if(isset($ticket['assigned_to']))
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Assignat a</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $ticket['assigned_to'] }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Client</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Nom</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $ticket['customer']['name'] ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $ticket['customer']['email'] ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Telèfon</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $ticket['customer']['phone'] ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </div>

            @if(isset($ticket['environment']))
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Entorn</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Dispositiu</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $ticket['environment']['device'] ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Sistema Operatiu</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $ticket['environment']['os'] ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Versió App</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $ticket['environment']['app_version'] ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>
            @endif
        </div>
    </div>
@endsection
