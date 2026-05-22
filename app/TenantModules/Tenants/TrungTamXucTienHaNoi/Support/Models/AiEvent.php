<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models;

use Illuminate\Database\Eloquent\Model;

class AiEvent extends Model
{
    protected $connection = 'tenant';

    protected $primaryKey = 'event_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'event_id',
        'event_type',
        'status',
        'mode',
        'documents_uploaded',
        'documents_failed',
        'new_slot',
        'job_id',
        'doc_id',
        'source_filename',
        'chunk_count',
        'duration_s',
        'embedding_tokens',
        'cost_usd_total',
        'payload_json',
        'received_at',
    ];

    protected $casts = [
        'payload_json' => 'array',
        'received_at' => 'datetime',
    ];
}
