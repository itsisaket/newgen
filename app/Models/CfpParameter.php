<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** พารามิเตอร์ CFP พร้อมระดับความน่าเชื่อถือ - ดู doc-comment ของ migration 2026_09_24_000007 */
class CfpParameter extends Model
{
    public const CONFIDENCE_MEASURED = 'measured';

    public const CONFIDENCE_LITERATURE = 'literature';

    public const CONFIDENCE_ASSUMED = 'assumed';

    public const CONFIDENCE_UNKNOWN = 'unknown';

    protected $fillable = [
        'code', 'name', 'value', 'value_text', 'unit', 'sensitivity_low', 'sensitivity_high',
        'confidence', 'verification_status', 'source', 'applies_to', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:6',
            'sensitivity_low' => 'decimal:6',
            'sensitivity_high' => 'decimal:6',
        ];
    }

    public static function valueOf(string $code): ?float
    {
        $v = static::where('code', $code)->value('value');

        return $v === null ? null : (float) $v;
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
