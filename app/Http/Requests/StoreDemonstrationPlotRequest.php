<?php

namespace App\Http\Requests;

use App\Models\DemonstrationPlot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDemonstrationPlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plot_id' => ['required', 'integer', 'exists:plots,id'],
            'comparison_group_id' => [
                'required',
                'integer',
                'exists:demonstration_comparison_groups,id',
                // Mirrors the DB unique(comparison_group_id, plot_id)
                // constraint so a duplicate enrollment fails validation
                // with a Thai message instead of a raw QueryException.
                Rule::unique('demonstration_plots', 'comparison_group_id')
                    ->where(fn ($query) => $query->where('plot_id', $this->input('plot_id'))),
            ],
            'group_type' => ['required', 'string', Rule::in(DemonstrationPlot::GROUP_TYPES)],
            'enrolled_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'comparison_group_id.unique' => 'แปลงนี้ถูกเพิ่มเข้าชุดเปรียบเทียบนี้ไปแล้ว',
        ];
    }
}
