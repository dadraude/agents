<?php

namespace App\AI\Traits;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Log;

trait ChecksBypass
{
    /**
     * Check if this agent should be bypassed.
     */
    protected function shouldBypass(): bool
    {
        $settings = AppSetting::get();
        $isBypassed = $settings->isBypassed($this->name());

        if ($isBypassed) {
            Log::info('Agent bypassed', [
                'agent' => $this->name(),
            ]);
        }

        return $isBypassed;
    }
}
