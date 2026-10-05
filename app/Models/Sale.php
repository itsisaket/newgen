<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'seller_household_id', 'buyer_id', 'product_type', 'product_id',
        'quantity', 'unit_price', 'total_amount', 'sale_date', 'crop_season_id',
        'notes', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'sale_date' => 'date',
        ];
    }

    public function sellerHousehold()
    {
        return $this->belongsTo(Household::class, 'seller_household_id');
    }

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function cropSeason()
    {
        return $this->belongsTo(CropSeason::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
