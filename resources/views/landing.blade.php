<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - AI Support Ticket Processor</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen">
        <div class="flex flex-col min-h-screen">
            <!-- Hero Section -->
            <main class="flex-1">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20">
                    <div class="text-center mb-12">
                        <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-4">
                            AI-Powered Support Ticket Processing
                        </h1>
                        <p class="text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto mb-8">
                            Automate support ticket processing with a network of specialized AI agents that classify, validate, prioritize, and manage your tickets intelligently.
                        </p>
                        <a href="{{ route('support.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-lg">
                            Get Started
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                    </div>

                    <!-- Features Grid -->
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                        <!-- Interpreter Agent -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 hover:border-blue-300 dark:hover:border-blue-700 transition-colors">
                            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Interpreter Agent</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Analyzes and interprets ticket content to extract key information and understand the problem context.
                            </p>
                        </div>

                        <!-- Classifier Agent -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 hover:border-blue-300 dark:hover:border-blue-700 transition-colors">
                            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Classifier Agent</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Automatically classifies tickets by category, product, and problem type.
                            </p>
                        </div>

                        <!-- Validator Agent -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 hover:border-blue-300 dark:hover:border-blue-700 transition-colors">
                            <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Validator Agent</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Validates that tickets have all necessary information to be processed correctly.
                            </p>
                        </div>

                        <!-- Prioritizer Agent -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 hover:border-blue-300 dark:hover:border-blue-700 transition-colors">
                            <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Prioritizer Agent</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Assigns intelligent priorities to tickets based on severity and problem importance.
                            </p>
                        </div>

                        <!-- Decision Maker Agent -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 hover:border-blue-300 dark:hover:border-blue-700 transition-colors">
                            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Decision Maker Agent</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Makes decisions on how to handle each ticket: process it, escalate it, or request more information.
                            </p>
                        </div>

                        <!-- Linear Writer Agent -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 hover:border-blue-300 dark:hover:border-blue-700 transition-colors">
                            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Linear Writer Agent</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Automatically creates and manages Linear issues for tickets that require development tracking.
                            </p>
                        </div>
                    </div>

                    <!-- How It Works -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-8 mb-12">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">How It Works</h2>
                        <div class="grid md:grid-cols-4 gap-6">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">1</span>
                                </div>
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Select Tickets</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Select the pending tickets you want to process
                                </p>
                            </div>
                            <div class="text-center">
                                <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span class="text-2xl font-bold text-green-600 dark:text-green-400">2</span>
                                </div>
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Automatic Processing</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    AI agents process each ticket in sequence
                                </p>
                            </div>
                            <div class="text-center">
                                <div class="w-16 h-16 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">3</span>
                                </div>
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Review & Validation</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Review the decisions made by the agents
                                </p>
                            </div>
                            <div class="text-center">
                                <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span class="text-2xl font-bold text-purple-600 dark:text-purple-400">4</span>
                                </div>
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Linear Integration</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Tickets are automatically created in Linear when needed
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- CTA Section -->
                    <div class="text-center">
                        <a href="{{ route('support.index') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-lg shadow-lg">
                            Start Processing Tickets
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            © {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
                        </p>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
