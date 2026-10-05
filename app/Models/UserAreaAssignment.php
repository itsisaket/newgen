<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Blueprint section 4.1. Prefer resolving this through AreaScopeService
 * (app/Services/AreaScopeService.php) rather than querying it directly
 * from controllers, so every module filters area the same way.
 */
class UserAreaAssignment extends Model
{
    protected $fillable = ['user_id', 'scope_type', 'scope_id', 'created_by'];

    public const SCOPE_PROVINCE = 'province';
    public const SCOPE_DISTRICT = 'district';
    public const SCOPE_TAMBON = 'tambon';
    public const SCOPE_FARMER_GROUP = 'farmer_group';
    public const SCOPE_ALL = 'all';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Human-readable label for the UI - scope_id has no fixed FK (it can
     * point at provinces, districts, tambons, or farmer_groups depending
     * on scope_type), so it's resolved here instead of via an Eloquent
     * relation.
     */
    public function scopeLabel(): string
    {
        if ($this->scope_type === self::SCOPE_ALL || $this->scope_id === null) {
            return 'ทั้งหมด (ทุกพื้นที่)';
        }

        $model = match ($this->scope_type) {
            self::SCOPE_PROVINCE => Province::find($this->scope_id),
            self::SCOPE_DISTRICT => District::find($this->scope_id),
            self::SCOPE_TAMBON => Tambon::find($this->scope_id),
            self::SCOPE_FARMER_GROUP => FarmerGroup::find($this->scope_id),
            default => null,
        };

        if (! $model) {
            return "{$this->scope_type} #{$this->scope_id} (ไม่พบข้อมูล)";
        }

        $labelMap = [
            self::SCOPE_PROVINCE => 'จังหวัด',
            self::SCOPE_DISTRICT => 'อำเภอ',
            self::SCOPE_TAMBON => 'ตำบล',
            self::SCOPE_FARMER_GROUP => 'กลุ่มเกษตรกร',
        ];

        $name = $model->name_th ?? $model->name ?? '';

        return ($labelMap[$this->scope_type] ?? $this->scope_type).': '.$name;
    }
}
