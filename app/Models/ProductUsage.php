<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUsage extends Model
{
    protected $fillable = ['inventory_transaction_id', 'plot_id', 'usage_rate', 'application_method', 'application_date'];

    protected function casts(): array
    {
        return [
            'application_date' => 'date',
            'usage_rate' => 'decimal:2',
        ];
    }

    public function inventoryTransaction()
    {
        return $this->belongsTo(InventoryTransaction::class);
    }

    public function plot()
    {
        return $this->belongsTo(Plot::class);
    }
}
