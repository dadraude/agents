<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ShowLLMCalls extends Command
{
    protected $signature = 'agents:llm-calls {--lines=100 : Number of lines to read from log file}';

    protected $description = 'Show LLM calls from the log file. Filters for entries that used LLM (AI) instead of heuristic methods.';

    public function handle(): int
    {
        $logPath = storage_path('logs/laravel.log');

        if (! File::exists($logPath)) {
            $this->error('Log file not found: '.$logPath);

            return Command::FAILURE;
        }

        $lines = (int) $this->option('lines');
        $logContent = File::get($logPath);
        $logLines = explode("\n", $logContent);
        $recentLines = array_slice($logLines, -$lines);

        $this->info("Searching for LLM calls in last {$lines} lines of log...\n");

        $llmCalls = [];
        $currentEntry = null;

        foreach ($recentLines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            // Check for LLM-related log entries
            if (str_contains($line, '"method":"llm"') ||
                str_contains($line, 'Using LLM for agent') ||
                str_contains($line, 'LLM call initiated') ||
                str_contains($line, 'LLM call completed') ||
                str_contains($line, '🤖 Using LLM')) {
                $llmCalls[] = $line;
            }
        }

        if (empty($llmCalls)) {
            $this->warn('No LLM calls found in the recent log entries.');
            $this->line('');
            $this->line('This could mean:');
            $this->line('  - LLM is not enabled (check AI_USE_LLM in .env)');
            $this->line('  - LLM is not configured (check API keys in .env)');
            $this->line('  - All agents are using heuristic methods');
            $this->line('');
            $this->line('To see all agent executions (including heuristic), check the full log file.');

            return Command::SUCCESS;
        }

        $this->info('Found '.count($llmCalls).' LLM-related log entries:');
        $this->line('');

        foreach ($llmCalls as $call) {
            // Extract timestamp if present
            if (preg_match('/\[([^\]]+)\]/', $call, $matches)) {
                $timestamp = $matches[1];
                $this->line("<fg=cyan>{$timestamp}</>");
            }

            // Highlight key information
            if (str_contains($call, 'Using LLM for agent')) {
                $this->line('  <fg=green>🤖 LLM Enabled</>');
            } elseif (str_contains($call, 'LLM call initiated')) {
                $this->line('  <fg=yellow>→ LLM Call Started</>');
            } elseif (str_contains($call, 'LLM call completed')) {
                $this->line('  <fg=green>✓ LLM Call Completed</>');
            }

            // Extract and display JSON context
            if (preg_match('/\{.*\}/', $call, $jsonMatches)) {
                $json = json_decode($jsonMatches[0], true);
                if ($json) {
                    if (isset($json['agent'])) {
                        $this->line("  Agent: <fg=magenta>{$json['agent']}</>");
                    }
                    if (isset($json['provider'])) {
                        $this->line("  Provider: <fg=blue>{$json['provider']}</>");
                    }
                    if (isset($json['model'])) {
                        $this->line("  Model: <fg=blue>{$json['model']}</>");
                    }
                    if (isset($json['execution_time_ms'])) {
                        $this->line("  Execution Time: <fg=yellow>{$json['execution_time_ms']}ms</>");
                    }
                }
            }

            $this->line('');
        }

        return Command::SUCCESS;
    }
}
