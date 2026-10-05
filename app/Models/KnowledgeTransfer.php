<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * F11 Knowledge Transfer (Blueprint Appendix C.5). Sprint 5 round - see
 * the migration's doc-comment
 * (2026_09_16_000001_create_knowledge_transfers_table.php) for why
 * evidence is attached through the polymorphic `evidences` table instead
 * of the Blueprint's literal `evidence_id` column, and why `recorded_by`
 * was added.
 */
class KnowledgeTransfer extends Model
{
    protected $fillable = [
        'innovator_id', 'transfer_date', 'topic', 'recipient_count',
        'recipient_type', 'location', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'transfer_date' => 'date',
        ];
    }

    public function innovator()
    {
        return $this->belongsTo(Innovator::class);
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
