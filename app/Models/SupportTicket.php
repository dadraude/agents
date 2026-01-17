<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $title
 * @property string $description
 * @property string $status
 * @property string $severity
 * @property string $priority
 * @property string $product
 * @property string $channel
 * @property string|null $assigned_to
 * @property string $customer_name
 * @property string $customer_email
 * @property string $customer_phone
 * @property string|null $environment_device
 * @property string|null $environment_os
 * @property string|null $environment_app_version
 * @property \Illuminate\Support\Carbon|null $sla_deadline
 * @property \Illuminate\Support\Carbon|null $resolved_at
 * @property string|null $linear_issue_id
 * @property string|null $linear_issue_url
 */
class SupportTicket extends Model
{
    /** @use HasFactory<\Database\Factories\SupportTicketFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'title',
        'description',
        'status',
        'severity',
        'priority',
        'product',
        'channel',
        'assigned_to',
        'customer_name',
        'customer_email',
        'customer_phone',
        'environment_device',
        'environment_os',
        'environment_app_version',
        'sla_deadline',
        'resolved_at',
        'linear_issue_id',
        'linear_issue_url',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'sla_deadline' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Get the incident runs for this support ticket.
     */
    public function incidentRuns()
    {
        return $this->hasMany(IncidentRun::class, 'support_ticket_id');
    }
}
