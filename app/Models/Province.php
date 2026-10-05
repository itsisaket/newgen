<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $fillable = ['name_th', 'name_en'];

    public function districts()
    {
        return $this->hasMany(District::class);
    }
}
