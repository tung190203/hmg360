<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models;

use Illuminate\Database\Eloquent\Model;

class LegalDocument extends Model
{
    protected $connection = 'tenant';

    protected $table = "legal_document";

    protected $fillable = [
        'vrtour_id',
        'name',
        'name_en',
        'download',
        'user_id',
        'extracted_text',
        'extracted_summary',
        'extracted_language',
        'extracted_at',
    ];
}
