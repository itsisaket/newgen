<?php

namespace App\Http\Requests;

use App\Models\DurianPhenologyRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePhenologyRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plot_id' => ['required', 'exists:plots,id'],
            'crop_season_id' => ['required', 'exists:crop_seasons,id'],
            'stage' => ['required', Rule::in(DurianPhenologyRecord::STAGES)],
            'observed_date' => ['required', 'date'],
            'fruit_count' => ['nullable', 'integer', 'min:0'],
            'expected_avg_weight_kg' => ['nullable', 'numeric', 'min:0', 'max:99.99'],
            // Blueprint section 16 "Date Validation: ลำดับ Phenology และ
            // Harvest ต้องถูกต้อง" - expected harvest can't be before the
            // observation that predicts it.
            'expected_harvest_date' => ['nullable', 'date', 'after_or_equal:observed_date'],
        ];
    }
}
