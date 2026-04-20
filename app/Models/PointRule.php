<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointRule extends Model
{
    protected $fillable = [
        'rule_name',
        'target_role',
        'trigger_event',
        'condition_operator',
        'condition_time',
        'condition_minute',
        'point_modifier',
        'priority',
        'is_active'
    ];

    // Tambahkan ini supaya 0/1 dari database jadi true/false di Alpine.js
    protected $casts = [
        'is_active' => 'boolean',
        'point_modifier' => 'integer',
        'priority' => 'integer'
    ];
}
