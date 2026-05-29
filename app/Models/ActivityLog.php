<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_log';

    public $timestamps = false; // ClickHouse управляет created_at сам

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
