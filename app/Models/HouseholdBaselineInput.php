<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** F01 Baseline เชิงปริมาณ (CFP) - ดู doc-comment ของ migration 2026_09_24_000008 */
class HouseholdBaselineInput extends Model
{
    public const KIND_FERTILIZER = 'fertilizer';

    public const KIND_PESTICIDE = 'pesticide';

    public const KIND_ELECTRICITY = 'electricity';

    public const KIND_DIESEL = 'diesel';

    public const KIND_GASOLINE = 'gasoline';

    public const KIND_WATER = 'water';

    public const KIND_BRANCH_ROUTE = 'branch_route';

    public const KIND_LABELS = [
        self::KIND_FERTILIZER => 'ปุ๋ย (ต่อชนิด)',
        self::KIND_PESTICIDE => 'สารเคมีกำจัดศัตรูพืช (ต่อชนิด)',
        self::KIND_ELECTRICITY => 'ไฟฟ้า',
        self::KIND_DIESEL => 'น้ำมันดีเซล',
        self::KIND_GASOLINE => 'น้ำมันเบนซิน',
        self::KIND_WATER => 'น้ำประปา',
        self::KIND_BRANCH_ROUTE => 'เส้นทางจัดการกิ่ง (ต่อเส้นทาง)',
    ];

    /** หน่วยตายตัวของชนิดที่ไม่ผูกกับ material (ปุ๋ย/สารเคมีใช้หน่วยของ material) */
    public const FIXED_UNITS = [
        self::KIND_ELECTRICITY => 'kWh',
        self::KIND_DIESEL => 'ลิตร',
        self::KIND_GASOLINE => 'ลิตร',
        self::KIND_WATER => 'ลูกบาศก์เมตร',
        self::KIND_BRANCH_ROUTE => 'กก. (น้ำหนักสด)',
    ];

    protected $fillable = [
        'household_baseline_id', 'input_kind', 'material_id', 'disposal_route',
        'quantity', 'unit', 'moisture_pct', 'note',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'moisture_pct' => 'decimal:2',
        ];
    }

    public function baseline()
    {
        return $this->belongsTo(HouseholdBaseline::class, 'household_baseline_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function kindLabel(): string
    {
        return self::KIND_LABELS[$this->input_kind] ?? $this->input_kind;
    }

    /** ชื่อรายการสำหรับแสดงผล: ชื่อ material หรือชื่อเส้นทางกิ่ง หรือชนิดปัจจัย */
    public function itemLabel(): string
    {
        if ($this->material) {
            return $this->material->name;
        }

        if ($this->disposal_route) {
            return BranchDisposalRecord::ROUTE_LABELS[$this->disposal_route] ?? $this->disposal_route;
        }

        return $this->kindLabel();
    }
}
