<?php

namespace App\Models;

use App\Support\WorkflowStatus;
use Illuminate\Database\Eloquent\Model;

/**
 * F15 - Carbon Activity Monitoring (Blueprint Appendix C.6). Same
 * Draft -> Submitted -> Verified -> Approved workflow as KilnBatch - see
 * the carbon_activities migration's doc-comment.
 *
 * CATEGORIES/CATEGORY_LABELS: Blueprint Appendix C.6 does not enumerate a
 * literal category list (only "กิจกรรมลดคาร์บอน" in the abstract) - this is
 * a Sprint 5 proposed default set covering the reduction activities this
 * project's own modules already track evidence for (biochar via F03,
 * durian-adjacent agroforestry/fertilizer practices via F13). Adjustable
 * later: adding a category is a one-line change here plus a matching
 * emission_factors row, no migration needed.
 */
class CarbonActivity extends Model
{
    public const CATEGORY_BIOCHAR_APPLICATION = 'biochar_application';

    public const CATEGORY_ORGANIC_FERTILIZER = 'organic_fertilizer_use';

    public const CATEGORY_REDUCED_CHEMICAL_FERTILIZER = 'reduced_chemical_fertilizer';

    public const CATEGORY_COVER_CROP = 'cover_crop_planting';

    public const CATEGORY_AGROFORESTRY = 'agroforestry_tree_planting';

    public const CATEGORY_RENEWABLE_ENERGY = 'renewable_energy_use';

    public const CATEGORIES = [
        self::CATEGORY_BIOCHAR_APPLICATION,
        self::CATEGORY_ORGANIC_FERTILIZER,
        self::CATEGORY_REDUCED_CHEMICAL_FERTILIZER,
        self::CATEGORY_COVER_CROP,
        self::CATEGORY_AGROFORESTRY,
        self::CATEGORY_RENEWABLE_ENERGY,
    ];

    public const CATEGORY_LABELS = [
        self::CATEGORY_BIOCHAR_APPLICATION => 'การใส่ถ่านชีวภาพลงดิน (Biochar)',
        self::CATEGORY_ORGANIC_FERTILIZER => 'การใช้ปุ๋ยอินทรีย์ทดแทนปุ๋ยเคมี',
        self::CATEGORY_REDUCED_CHEMICAL_FERTILIZER => 'การลดการใช้ปุ๋ยเคมี',
        self::CATEGORY_COVER_CROP => 'การปลูกพืชคลุมดิน',
        self::CATEGORY_AGROFORESTRY => 'การปลูกไม้ยืนต้นร่วม (วนเกษตร)',
        self::CATEGORY_RENEWABLE_ENERGY => 'การใช้พลังงานทดแทน',
    ];

    protected $fillable = [
        'household_id', 'category', 'activity_date', 'quantity', 'unit', 'description',
        'status', 'rejection_reason', 'recorded_by', 'original_record_id',
    ];

    protected $attributes = [
        'status' => WorkflowStatus::DRAFT,
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'quantity' => 'decimal:4',
        ];
    }

    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function originalRecord()
    {
        return $this->belongsTo(self::class, 'original_record_id');
    }

    public function calculation()
    {
        return $this->hasOne(CarbonCalculation::class);
    }

    public function evidences()
    {
        return $this->morphMany(Evidence::class, 'evidenceable');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORY_LABELS[$this->category] ?? $this->category;
    }
}
