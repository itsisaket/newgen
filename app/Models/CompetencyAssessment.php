<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetencyAssessment extends Model
{
    protected $fillable = [
        'innovator_id', 'round', 'assessment_date', 'assessor_type', 'assessor_id', 'notes', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'assessment_date' => 'date',
        ];
    }

    public function innovator()
    {
        return $this->belongsTo(Innovator::class);
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scores()
    {
        return $this->hasMany(CompetencyScore::class);
    }
}
