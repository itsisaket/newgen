<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * F05 - one plot's enrollment (treatment or control) into one
 * DemonstrationComparisonGroup - see that model's / the migration's
 * doc-comment.
 */
class DemonstrationPlot extends Model
{
    public const GROUP_TYPE_TREATMENT = 'treatment';

    public const GROUP_TYPE_CONTROL = 'control';

    public const GROUP_TYPES = [self::GROUP_TYPE_TREATMENT, self::GROUP_TYPE_CONTROL];

    protected $fillable = [
        'comparison_group_id', 'plot_id', 'group_type', 'enrolled_at', 'ended_at', 'notes', 'enrolled_by',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'date',
            'ended_at' => 'date',
        ];
    }

    public function comparisonGroup()
    {
        return $this->belongsTo(DemonstrationComparisonGroup::class, 'comparison_group_id');
    }

    public function plot()
    {
        return $this->belongsTo(Plot::class);
    }

    public function enrolledBy()
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }
}
