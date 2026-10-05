<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KilnBatchOutput extends Model
{
    protected $fillable = ['kiln_batch_id', 'product_id', 'output_quantity', 'unit', 'quality_grade'];

    protected function casts(): array
    {
        return ['output_quantity' => 'decimal:2'];
    }

    public function kilnBatch()
    {
        return $this->belongsTo(KilnBatch::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
