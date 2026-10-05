<?php

namespace App\Http\Requests;

use App\Models\CompetencyIndicator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCompetencyAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'innovator_id' => ['required', 'exists:innovators,id'],
            'round' => ['required', 'in:T0,T1,T2'],
            'assessment_date' => ['required', 'date'],
            // assessor_type/assessor_id are accepted here for STAFF/observer
            // submissions but the Controller overrides both to
            // self/current-user whenever the actor is OWN_HOUSEHOLD - see
            // CompetencyAssessmentPolicy's doc-comment.
            'assessor_type' => ['required', 'in:self,observer'],
            'assessor_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'scores' => ['required', 'array', 'min:1'],
            'scores.*' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Each submitted score must not exceed ITS OWN indicator's max_score
     * (Blueprint's competency_indicators.max_score) - a single "max:100"
     * rule on scores.* isn't enough since indicators can have different
     * ceilings, same reasoning as ValidatesLocationHierarchy's per-row
     * cross-check.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $scores = $this->input('scores', []);

            if (! is_array($scores) || empty($scores)) {
                return;
            }

            $indicators = CompetencyIndicator::whereIn('id', array_keys($scores))->get()->keyBy('id');

            foreach ($scores as $indicatorId => $score) {
                $indicator = $indicators->get((int) $indicatorId);

                if (! $indicator) {
                    $validator->errors()->add("scores.{$indicatorId}", 'ตัวชี้วัดนี้ไม่ถูกต้องหรือถูกปิดใช้งานแล้ว');

                    continue;
                }

                if (is_numeric($score) && (float) $score > (float) $indicator->max_score) {
                    $validator->errors()->add(
                        "scores.{$indicatorId}",
                        "คะแนนต้องไม่เกิน {$indicator->max_score} คะแนน (ตัวชี้วัด: {$indicator->name})"
                    );
                }
            }
        });
    }
}
