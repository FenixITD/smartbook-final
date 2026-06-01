<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false; // ClickHouse управляет created_at сам

    protected $table = 'activity_log';

    protected $fillable = [
        'id', 'log_name', 'description',
        'subject_type', 'subject_id',
        'causer_name', 'causer_id',
        'properties', 'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
    ];
}
