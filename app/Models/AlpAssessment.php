<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlpAssessment extends Model
{
    protected $fillable = [
        'household_id', 'technology_id', 'assessment_date', 'alp_level', 'assessor_id', 'notes', 'recorded_by',
    ];

    /**
     * Blueprint 12.1's 5-level ALP scale (รับรู้ -> ทดลอง -> ใช้ได้เอง ->
     * ปรับใช้/แก้ปัญหา -> ถ่ายทอด/ขยายผล).
     */
    public const LEVEL_LABELS = [
        1 => 'รับรู้ (Awareness)',
        2 => 'ทดลอง (Trial)',
        3 => 'ใช้ได้เอง (Independent Use)',
        4 => 'ปรับใช้/แก้ปัญหา (Adaptation)',
        5 => 'ถ่ายทอด/ขยายผล (Transfer/Scale-out)',
    ];

    protected function casts(): array
    {
        return [
            'assessment_date' => 'date',
            'alp_level' => 'integer',
        ];
    }

    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    public function technology()
    {
        return $this->belongsTo(Technology::class);
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function levelLabel(): string
    {
        return self::LEVEL_LABELS[$this->alp_level] ?? (string) $this->alp_level;
    }
}
