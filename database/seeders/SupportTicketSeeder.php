<?php

namespace Database\Seeders;

use App\Models\SupportTicket;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class SupportTicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('fixtures/support_tickets.json');

        if (! File::exists($path)) {
            $this->command->warn('JSON file not found. Skipping migration.');

            return;
        }

        $content = File::get($path);
        $tickets = json_decode($content, true) ?? [];

        foreach ($tickets as $ticket) {
            SupportTicket::updateOrCreate(
                ['id' => $ticket['id']],
                [
                    'title' => $ticket['title'],
                    'description' => $ticket['description'],
                    'status' => 'new',
                    'severity' => $ticket['severity'],
                    'priority' => $ticket['priority'],
                    'product' => $ticket['product'],
                    'channel' => $ticket['channel'],
                    'assigned_to' => null,
                    'customer_name' => $ticket['customer']['name'] ?? '',
                    'customer_email' => $ticket['customer']['email'] ?? '',
                    'customer_phone' => $ticket['customer']['phone'] ?? '',
                    'environment_device' => $ticket['environment']['device'] ?? null,
                    'environment_os' => $ticket['environment']['os'] ?? null,
                    'environment_app_version' => $ticket['environment']['app_version'] ?? null,
                    'sla_deadline' => isset($ticket['sla_deadline']) ? $ticket['sla_deadline'] : null,
                    'resolved_at' => null,
                    'linear_issue_id' => null,
                    'linear_issue_url' => null,
                    'created_at' => $ticket['created_at'] ?? now(),
                    'updated_at' => $ticket['updated_at'] ?? now(),
                ]
            );
        }

        $this->command->info('Migrated '.count($tickets).' incidents to the database.');
    }
}
