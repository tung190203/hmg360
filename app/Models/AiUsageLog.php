<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiUsageLog extends Model
{
    protected $fillable = [
        'endpoint',
        'user_id',
        'model_used',
        'input_tokens',
        'output_tokens',
        'cost_usd',
        'payload_json',
        'called_at',
    ];

    protected $casts = [
        'payload_json' => 'array',
        'called_at' => 'datetime',
    ];
}
