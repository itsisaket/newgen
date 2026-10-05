<?php

namespace App\Models;

use App\Support\WorkflowStatus;
use Illuminate\Database\Eloquent\Model;

class FarmActivity extends Model
{
    protected $fillable = [
        'client_uuid', 'plot_id', 'crop_season_id', 'activity_type_id', 'activity_date',
        'material_id', 'quantity', 'unit_cost', 'labor_hours', 'labor_cost', 'total_cost',
        'status', 'rejection_reason', 'recorded_by', 'original_record_id',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'quantity' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'labor_hours' => 'decimal:2',
            'labor_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    protected $attributes = [
        'status' => WorkflowStatus::DRAFT,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $activity) {
            $activity->client_uuid ??= (string) \Illuminate\Support\Str::uuid();
        });
    }

    public function plot()
    {
        return $this->belongsTo(Plot::class);
    }

    public function cropSeason()
    {
        return $this->belongsTo(CropSeason::class);
    }

    public function activityType()
    {
        return $this->belongsTo(ActivityType::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function evidences()
    {
        return $this->morphMany(Evidence::class, 'evidenceable');
    }
}
