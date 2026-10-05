<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * F15 master data - see the emission_factors migration's doc-comment for
 * why this is versioned by date range instead of edited in place.
 */
class EmissionFactor extends Model
{
    protected $fillable = [
        'code', 'category', 'factor_value', 'unit', 'gas_basis', 'gwp_version', 'source', 'source_doc_version',
        'effective_from', 'effective_to', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'factor_value' => 'decimal:6',
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The factor version in force for $category on $date - i.e. the row
     * whose effective_from is on/before $date AND (effective_to is null
     * OR effective_to is on/after $date). CarbonCalculationService is the
     * only caller; a null return means no factor has ever been defined
     * for that category as of that date, which the service treats as a
     * hard error rather than silently calculating 0 kgCO2e.
     */
    public static function effectiveFor(string $category, $date): ?self
    {
        return static::where('category', $category)
            ->where('effective_from', '<=', $date)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', $date))
            ->orderByDesc('effective_from')
            ->first();
    }
}
