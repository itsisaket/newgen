<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechnologyAsset extends Model
{
    protected $fillable = ['technology_id', 'asset_code', 'acquired_date', 'condition_status'];

    protected function casts(): array
    {
        return ['acquired_date' => 'date'];
    }

    public function technology()
    {
        return $this->belongsTo(Technology::class);
    }

    public function assignments()
    {
        return $this->hasMany(TechnologyAssignment::class)->latest('assigned_date');
    }

    public function kilnBatches()
    {
        return $this->hasMany(KilnBatch::class);
    }

    /**
     * The household currently holding this asset, if any - see
     * TechnologyAssignmentController's doc-comment for why at most one
     * row can be active at a time.
     */
    public function currentAssignment(): ?TechnologyAssignment
    {
        return $this->assignments->firstWhere('status', 'active');
    }
}
