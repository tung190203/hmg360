<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalDocument extends Model
{
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
