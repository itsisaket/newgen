<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEvidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Size/MIME limits per Blueprint section 9: images up to 5 MB,
     * documents/PDF up to 10 MB. Laravel's `max` rule is in kilobytes.
     */
    public function rules(): array
    {
        return [
            'evidenceable_type' => ['required', 'string', Rule::in([
                \App\Models\FarmActivity::class,
                \App\Models\HouseholdBaseline::class,
                \App\Models\HarvestRecord::class,
                \App\Models\KilnBatch::class,
                // F11 Knowledge Transfer (Sprint 5 round) - see the
                // knowledge_transfers migration's doc-comment for why
                // this uses the polymorphic pattern instead of the
                // Blueprint's literal evidence_id column.
                \App\Models\KnowledgeTransfer::class,
                // F15 Carbon Activity Monitoring (Sprint 5 round).
                \App\Models\CarbonActivity::class,
                // CFP - การจัดการกิ่ง/เศษไม้ตามเส้นทางกำจัด
                \App\Models\BranchDisposalRecord::class,
            ])],
            'evidenceable_id' => ['required', 'integer'],
            'file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:10240',
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $file = $this->file('file');

            if ($file && ! str_contains((string) $file->getMimeType(), 'pdf') && $file->getSize() > 5 * 1024 * 1024) {
                $validator->errors()->add('file', 'ไฟล์รูปภาพต้องมีขนาดไม่เกิน 5MB');
            }
        });
    }
}
