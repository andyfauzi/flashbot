<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    /**
     * Audit logs stored in landlord DB only.
     */
    protected $connection = 'landlord';

    protected $fillable = [
        'action',
        'actor_id',
        'target',
        'ip',
        'user_agent',
        'details'
    ];

    protected $casts = [
        'details' => 'array',
    ];
}
