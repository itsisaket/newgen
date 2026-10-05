<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * F09-lite pass/fail record - see the migration's doc-comment
 * (2026_09_15_000012_create_innovator_evaluations_table.php) for why this
 * exists instead of the full Blueprint F07/F09 modules.
 */
class InnovatorEvaluation extends Model
{
    protected $fillable = [
        'household_id',
        'evaluated_by',
        'evaluation_date',
        'score',
        'result',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'evaluation_date' => 'date',
            'score' => 'decimal:2',
        ];
    }

    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}
