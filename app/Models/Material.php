<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = [
        'name', 'unit', 'category', 'cfp_code', 'n_pct', 'p2o5_pct', 'k2o_pct', 'is_urea', 'active_ingredient_pct', 'default_price',
    ];

    protected function casts(): array
    {
        return [
            'n_pct' => 'decimal:2',
            'p2o5_pct' => 'decimal:2',
            'k2o_pct' => 'decimal:2',
            'is_urea' => 'boolean',
            'active_ingredient_pct' => 'decimal:2',
        ];
    }
}
