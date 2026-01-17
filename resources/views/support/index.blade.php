@extends('layouts.demo')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Tickets de Suport</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Gestiona i processa els tickets de suport amb agents d'IA
        </p>
    </div>

    <div class="grid gap-4">
        @forelse ($tickets as $ticket)
            <a href="{{ route('support.show', $ticket['id']) }}" class="block bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition-colors">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    {{ $ticket['id'] }}
                                </span>
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if(($ticket['status'] ?? '') === 'nou') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300
                                    @elseif(($ticket['status'] ?? '') === 'en_revisio') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                                    @elseif(($ticket['status'] ?? '') === 'processat') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $ticket['status'] ?? 'desconegut')) }}
                                </span>
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if(($ticket['severity'] ?? '') === 'critica') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300
                                    @elseif(($ticket['severity'] ?? '') === 'alta') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300
                                    @elseif(($ticket['severity'] ?? '') === 'mitjana') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                    @endif">
                                    {{ ucfirst($ticket['severity'] ?? 'normal') }}
                                </span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                {{ $ticket['title'] ?? 'Sense títol' }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                {{ $ticket['description'] ?? 'Sense descripció' }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ \Carbon\Carbon::parse($ticket['created_at'] ?? now())->diffForHumans() }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            {{ $ticket['customer']['name'] ?? 'Client desconegut' }}
                        </span>
                        @if(isset($ticket['product']))
                            <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">
                                {{ strtoupper($ticket['product']) }}
                            </span>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-12 text-center">
                <p class="text-gray-500 dark:text-gray-400">No hi ha tickets disponibles</p>
            </div>
        @endforelse
    </div>
@endsection
