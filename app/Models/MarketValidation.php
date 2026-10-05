<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketValidation extends Model
{
    protected $fillable = [
        'buyer_id', 'product_type', 'demand_quantity', 'price', 'frequency',
        'has_loi_mou', 'valid_from', 'valid_to', 'notes', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'demand_quantity' => 'decimal:2',
            'price' => 'decimal:2',
            'has_loi_mou' => 'boolean',
            'valid_from' => 'date',
            'valid_to' => 'date',
        ];
    }

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
