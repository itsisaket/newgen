<?php

namespace App\Models;

use App\Support\WorkflowStatus;
use Illuminate\Database\Eloquent\Model;

/** การจัดการกิ่ง/เศษไม้ตามเส้นทางกำจัด - ดู doc-comment ของ migration 2026_09_24_000006 */
class BranchDisposalRecord extends Model
{
    public const ROUTE_KILN = 'kiln';

    public const ROUTE_OPEN_BURN = 'open_burn';

    public const ROUTE_FIELD_DECOMPOSE = 'field_decompose';

    public const ROUTE_COMPOST = 'compost';

    public const ROUTE_OTHER = 'other';

    public const ROUTE_LABELS = [
        self::ROUTE_KILN => 'นำเข้าเตาผลิตไบโอชาร์/น้ำส้มควันไม้',
        self::ROUTE_OPEN_BURN => 'เผากลางแจ้ง',
        self::ROUTE_FIELD_DECOMPOSE => 'กองทิ้ง/ย่อยสลายในสวน',
        self::ROUTE_COMPOST => 'ทำปุ๋ยหมัก',
        self::ROUTE_OTHER => 'อื่น ๆ (ขาย/ให้ผู้อื่น)',
    ];

    protected $fillable = [
        'client_uuid', 'plot_id', 'disposal_date', 'disposal_route', 'quantity_kg', 'moisture_pct',
        'kiln_batch_id', 'note', 'status', 'rejection_reason', 'recorded_by', 'original_record_id',
    ];

    protected $attributes = [
        'status' => WorkflowStatus::DRAFT,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $record) {
            $record->client_uuid ??= (string) \Illuminate\Support\Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'disposal_date' => 'date',
            'quantity_kg' => 'decimal:2',
            'moisture_pct' => 'decimal:2',
        ];
    }

    public function plot()
    {
        return $this->belongsTo(Plot::class);
    }

    public function kilnBatch()
    {
        return $this->belongsTo(KilnBatch::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function evidences()
    {
        return $this->morphMany(Evidence::class, 'evidenceable');
    }

    public function routeLabel(): string
    {
        return self::ROUTE_LABELS[$this->disposal_route] ?? $this->disposal_route;
    }
}
