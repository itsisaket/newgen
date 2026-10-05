<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * F05 - see the demonstration_comparison_groups migration's doc-comment.
 * One experiment ("เปรียบเทียบเตาชีวมวลรุ่น A ปี 2569") that demonstration_plots
 * rows enroll individual plots into as treatment/control.
 */
class DemonstrationComparisonGroup extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUSES = [self::STATUS_ACTIVE, self::STATUS_COMPLETED];

    protected $fillable = [
        'name', 'crop_season_id', 'technology_id', 'objective', 'status', 'created_by',
    ];

    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
    ];

    public function cropSeason()
    {
        return $this->belongsTo(CropSeason::class);
    }

    public function technology()
    {
        return $this->belongsTo(Technology::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function demonstrationPlots()
    {
        return $this->hasMany(DemonstrationPlot::class, 'comparison_group_id')->latest('enrolled_at');
    }
}
