<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentRun extends Model
{
    protected $fillable = [
        'input_text',
        'state_json',
        'trace_json',
        'status',
        'linear_issue_id',
        'linear_issue_url',
    ];

    protected function casts(): array
    {
        return [
            'state_json' => 'array',
            'trace_json' => 'array',
        ];
    }
}
