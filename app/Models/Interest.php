<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    protected $fillable = ['guest_id', 'interestable_id', 'interestable_type'];

    public function interestable()
    {
        return $this->morphTo();
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }
}
