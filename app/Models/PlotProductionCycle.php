<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * รอบการผลิตระดับแปลงสำหรับ CFP - ดู doc-comment ของ migration
 * (2026_09_24_000005) สำหรับนิยามการเปิด/ปิดรอบ
 */
class PlotProductionCycle extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    public const STANDARD_CUTOFF_MONTH_DAY = '08-31';

    protected $fillable = [
        'plot_id', 'crop_season_id', 'start_date', 'end_date', 'last_harvest_date',
        'close_basis', 'status', 'closed_by', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'last_harvest_date' => 'date',
            'closed_at' => 'datetime',
        ];
    }

    public function plot()
    {
        return $this->belongsTo(Plot::class);
    }

    public function cropSeason()
    {
        return $this->belongsTo(CropSeason::class);
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    /** กิจกรรมวันที่ $date อยู่ในรอบนี้หรือไม่ (รอบเปิด = ไม่มีวันสิ้นสุด) */
    public function contains($date): bool
    {
        $d = \Illuminate\Support\Carbon::parse($date)->startOfDay();

        return $d->gte($this->start_date) && ($this->end_date === null || $d->lte($this->end_date));
    }
}
