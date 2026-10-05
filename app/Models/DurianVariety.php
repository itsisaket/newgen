<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DurianVariety extends Model
{
    protected $fillable = ['name'];

    public function plots()
    {
        return $this->hasMany(Plot::class);
    }
}
