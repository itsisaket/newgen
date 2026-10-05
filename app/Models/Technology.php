<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Technology extends Model
{
    protected $fillable = ['name', 'type', 'description'];

    public function assets()
    {
        return $this->hasMany(TechnologyAsset::class);
    }
}
