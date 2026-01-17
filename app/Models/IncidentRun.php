<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentRun extends Model
{
    protected $fillable = [
        'support_ticket_id',
        'input_text',
        'state_json',
        'trace_json',
        'status',
        'linear_issue_id',
        'linear_issue_url',
    ];

    /**
     * Get the support ticket that owns this incident run.
     */
    public function supportTicket()
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    protected function casts(): array
    {
        return [
            'state_json' => 'array',
            'trace_json' => 'array',
        ];
    }
}
