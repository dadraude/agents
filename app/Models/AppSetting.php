<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = [
        'active_interpreter',
        'active_classifier',
        'active_validator',
        'active_prioritizer',
        'active_decision_maker',
        'active_linear_writer',
    ];

    protected $casts = [
        'active_interpreter' => 'boolean',
        'active_classifier' => 'boolean',
        'active_validator' => 'boolean',
        'active_prioritizer' => 'boolean',
        'active_decision_maker' => 'boolean',
        'active_linear_writer' => 'boolean',
    ];

    /**
     * Get the singleton instance of AppSetting.
     * Creates a default record if none exists.
     */
    public static function get(): self
    {
        $setting = self::first();

        if (! $setting) {
            $setting = self::create([
                'active_interpreter' => true,
                'active_classifier' => true,
                'active_validator' => true,
                'active_prioritizer' => true,
                'active_decision_maker' => true,
                'active_linear_writer' => true,
            ]);
        }

        return $setting;
    }

    /**
     * Check if an agent is bypassed by name.
     * An agent is bypassed when it is not active.
     */
    public function isBypassed(string $agentName): bool
    {
        $fieldName = $this->getActiveFieldName($agentName);

        return ! ($this->$fieldName ?? true);
    }

    /**
     * Get the active field name for an agent.
     */
    private function getActiveFieldName(string $agentName): string
    {
        $agentName = strtolower($agentName);
        $fieldMap = [
            'interpreter' => 'active_interpreter',
            'classifier' => 'active_classifier',
            'validator' => 'active_validator',
            'prioritizer' => 'active_prioritizer',
            'decisionmaker' => 'active_decision_maker',
            'decision_maker' => 'active_decision_maker',
            'linearwriter' => 'active_linear_writer',
            'linear_writer' => 'active_linear_writer',
        ];

        return $fieldMap[$agentName] ?? 'active_interpreter';
    }
}
