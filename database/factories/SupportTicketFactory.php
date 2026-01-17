<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['new', 'in_review', 'processed'];
        $severities = ['critical', 'high', 'medium', 'low'];
        $priorities = ['urgent', 'high', 'normal', 'low'];
        $products = ['pos', 'kds', 'loyalty', 'backoffice', 'infra'];
        $channels = ['email', 'telefon', 'chat'];

        return [
            'id' => 'TKT-'.str_pad((string) fake()->unique()->numberBetween(1, 9999), 3, '0', STR_PAD_LEFT),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement($statuses),
            'severity' => fake()->randomElement($severities),
            'priority' => fake()->randomElement($priorities),
            'product' => fake()->randomElement($products),
            'channel' => fake()->randomElement($channels),
            'assigned_to' => fake()->optional()->name(),
            'customer_name' => fake()->company(),
            'customer_email' => fake()->companyEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'environment_device' => fake()->optional()->randomElement(['iPad', 'Tablet Android', 'PC', 'Terminal TPV', 'Cloud Infrastructure']),
            'environment_os' => fake()->optional()->randomElement(['iOS 14.8', 'iOS 15.2', 'Android 11', 'Windows 10', 'Windows 11', 'macOS 14.2', 'Linux', 'Kubernetes']),
            'environment_app_version' => fake()->optional()->semver(),
            'sla_deadline' => fake()->optional()->dateTimeBetween('now', '+7 days'),
            'resolved_at' => fake()->optional()->dateTimeBetween('-7 days', 'now'),
        ];
    }

    /**
     * Indicate that the ticket is new.
     */
    public function asNew(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'new',
            'resolved_at' => null,
        ]);
    }

    /**
     * Indicate that the ticket is in review.
     */
    public function inReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_review',
            'resolved_at' => null,
        ]);
    }

    /**
     * Indicate that the ticket is processed.
     */
    public function processed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processed',
            'resolved_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the ticket has critical severity.
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'severity' => 'critical',
            'priority' => 'urgent',
        ]);
    }
}
