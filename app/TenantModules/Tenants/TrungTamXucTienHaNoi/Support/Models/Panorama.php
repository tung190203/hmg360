<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models;

use Illuminate\Database\Eloquent\Model;

class Panorama extends Model
{
    protected $connection = 'tenant';

    protected $table = "panorama";
    protected $fillable = [
        'vrtour_id',
        'ids',
        'title',
        'title_en',
        'content',
        'content_en',
        'audio',
        'audio_en',
        'user_id'
    ];
}
