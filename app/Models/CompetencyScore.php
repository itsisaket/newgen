<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetencyScore extends Model
{
    protected $fillable = ['competency_assessment_id', 'competency_indicator_id', 'score'];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
        ];
    }

    public function assessment()
    {
        return $this->belongsTo(CompetencyAssessment::class, 'competency_assessment_id');
    }

    public function indicator()
    {
        return $this->belongsTo(CompetencyIndicator::class, 'competency_indicator_id');
    }
}
