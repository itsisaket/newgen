<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = ['province_id', 'name_th', 'name_en'];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function tambons()
    {
        return $this->hasMany(Tambon::class);
    }
}
