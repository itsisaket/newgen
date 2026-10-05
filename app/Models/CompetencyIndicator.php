<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetencyIndicator extends Model
{
    protected $fillable = ['dimension', 'name', 'max_score', 'is_active'];

    protected function casts(): array
    {
        return [
            'max_score' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Blueprint 12.2's 6 dimensions, Thai label matching the Blueprint
     * table exactly.
     */
    public const DIMENSION_LABELS = [
        'knowledge' => 'ความรู้เทคโนโลยี',
        'skill' => 'ทักษะปฏิบัติ',
        'adaptation' => 'การปรับใช้',
        'management' => 'การจัดการและเศรษฐกิจ',
        'market' => 'ตลาด/ผู้ประกอบการ',
        'transfer' => 'การถ่ายทอด',
    ];

    public function scores()
    {
        return $this->hasMany(CompetencyScore::class);
    }

    public function dimensionLabel(): string
    {
        return self::DIMENSION_LABELS[$this->dimension] ?? $this->dimension;
    }
}
