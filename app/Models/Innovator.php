<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * F07/F09/F11 Sprint 4 round - see the migration's doc-comment
 * (2026_09_15_000026_create_innovators_table.php) for why this is a real
 * table distinct from Household/User, per Blueprint Appendix C.3.
 */
class Innovator extends Model
{
    protected $fillable = [
        'household_id', 'innovator_evaluation_id', 'name', 'phone', 'level', 'registered_at',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'date',
        ];
    }

    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    public function qualifyingEvaluation()
    {
        return $this->belongsTo(InnovatorEvaluation::class, 'innovator_evaluation_id');
    }

    /**
     * F07 - ALP Assessment history is recorded per household (Blueprint's
     * alp_assessments.household_id), not per innovator - see
     * AlpAssessmentController's doc-comment for why. This convenience
     * accessor is only meaningful when the innovator has a household.
     */
    public function alpAssessments()
    {
        return $this->hasManyThrough(
            AlpAssessment::class,
            Household::class,
            'id',            // households.id
            'household_id',  // alp_assessments.household_id
            'household_id',  // innovators.household_id
            'id'              // households.id
        );
    }

    public function competencyAssessments()
    {
        return $this->hasMany(CompetencyAssessment::class)->latest('assessment_date');
    }

    /**
     * F11 Knowledge Transfer (Sprint 5 round) - see
     * KnowledgeTransferController's doc-comment.
     */
    public function knowledgeTransfers()
    {
        return $this->hasMany(KnowledgeTransfer::class)->latest('transfer_date');
    }
}
