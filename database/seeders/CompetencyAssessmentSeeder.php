<?php

namespace Database\Seeders;

use App\Models\CompetencyAssessment;
use App\Models\CompetencyIndicator;
use App\Models\Innovator;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * F09 - demo T0 (observer, before) + T1 (self, after) rounds for every
 * innovator InnovatorEvaluationSeeder promoted, so the "รายบุคคล รายมิติ"
 * progression Blueprint 12.2 describes has real before/after numbers to
 * show. Must run after InnovatorEvaluationSeeder (needs innovators rows)
 * and CompetencyIndicatorSeeder (needs the indicator master list).
 */
class CompetencyAssessmentSeeder extends Seeder
{
    public function run(): void
    {
        $observer = User::where('email', 'field.officer1@drfis.local')->first()
            ?? User::where('email', 'admin@drfis.local')->first();

        if (! $observer) {
            return;
        }

        $indicators = CompetencyIndicator::where('is_active', true)->get();

        if ($indicators->isEmpty()) {
            return;
        }

        Innovator::whereNotNull('household_id')->each(function (Innovator $innovator) use ($observer, $indicators) {
            $this->recordRound($innovator, 'T0', 'observer', $observer, '2026-06-01', $indicators, fn () => rand(40, 60));
            $this->recordRound($innovator, 'T1', 'self', null, '2026-08-25', $indicators, fn () => rand(70, 95));
        });
    }

    /**
     * @param  \Illuminate\Support\Collection<int, CompetencyIndicator>  $indicators
     */
    private function recordRound(
        Innovator $innovator,
        string $round,
        string $assessorType,
        ?User $assessor,
        string $date,
        $indicators,
        \Closure $scoreFor,
    ): void {
        $existing = CompetencyAssessment::where('innovator_id', $innovator->id)
            ->where('round', $round)
            ->where('assessment_date', $date)
            ->first();

        if ($existing) {
            return;
        }

        // A 'self' round is scored by the innovator's own login when one
        // exists (mirrors CompetencyAssessmentController::store()'s rule
        // that a self-assessment's assessor is always the actor
        // themselves), falling back to the observer account for the demo
        // households that don't have a linked Farmer login.
        $assessorUser = $assessorType === 'self'
            ? ($innovator->household?->user ?? $assessor)
            : $assessor;

        $recordedBy = $assessorUser ?? $assessor;

        if (! $recordedBy) {
            return;
        }

        $assessment = CompetencyAssessment::create([
            'innovator_id' => $innovator->id,
            'round' => $round,
            'assessment_date' => $date,
            'assessor_type' => $assessorType,
            'assessor_id' => $assessorUser?->id,
            'notes' => 'ข้อมูลตัวอย่างสำหรับสาธิตระบบ',
            'recorded_by' => $recordedBy->id,
        ]);

        foreach ($indicators as $indicator) {
            $assessment->scores()->create([
                'competency_indicator_id' => $indicator->id,
                'score' => min($scoreFor(), $indicator->max_score),
            ]);
        }
    }
}
