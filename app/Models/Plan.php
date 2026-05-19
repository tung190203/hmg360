<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = "plan";
    public function project()
    {
        return $this->hasOne(Project::class, 'id', 'vrtour_id');
    }
}
