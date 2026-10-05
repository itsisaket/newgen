<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechnologyAssignment extends Model
{
    protected $fillable = ['technology_asset_id', 'household_id', 'assigned_date', 'returned_date', 'status', 'assigned_by'];

    protected function casts(): array
    {
        return [
            'assigned_date' => 'date',
            'returned_date' => 'date',
        ];
    }

    public function technologyAsset()
    {
        return $this->belongsTo(TechnologyAsset::class);
    }

    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
