<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plot extends Model
{
    protected $fillable = [
        'farm_id', 'plot_code', 'area_rai', 'tree_count', 'durian_variety_id',
        'planting_year', 'first_bearing_year', 'economic_life_years', 'irrigation_type', 'status',
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function durianVariety()
    {
        return $this->belongsTo(DurianVariety::class);
    }

    public function farmActivities()
    {
        return $this->hasMany(FarmActivity::class);
    }

    public function branchDisposalRecords()
    {
        return $this->hasMany(BranchDisposalRecord::class);
    }

    public function harvestRecords()
    {
        return $this->hasMany(HarvestRecord::class);
    }

    /**
     * F14-lite phenology observation log (see the durian_phenology_records
     * migration's doc-comment).
     */
    public function phenologyRecords()
    {
        return $this->hasMany(DurianPhenologyRecord::class);
    }

    /**
     * F05 - which demonstration_comparison_groups (if any) this plot is
     * currently/has been enrolled into as treatment or control.
     */
    public function demonstrationPlots()
    {
        return $this->hasMany(DemonstrationPlot::class)->latest('enrolled_at');
    }

    /**
     * F04 - product usages (biomass product applied to this plot) - see
     * the product_usages migration (Sprint 3: Technology & Biomass round).
     */
    public function productUsages()
    {
        return $this->hasMany(ProductUsage::class);
    }

    public function evidences()
    {
        return $this->morphMany(Evidence::class, 'evidenceable');
    }

    /**
     * Convenience accessor for plot -> farm -> household, e.g. for
     * AreaScopeService-style filters (Blueprint 4.1). This is a plain
     * accessor, not an Eloquent relation - hasOneThrough doesn't fit here
     * because the foreign keys run the other way (plots.farm_id and
     * farms.household_id both point "up", not "down" as hasOneThrough
     * expects). Eager-load via `with('farm.household')` instead of this
     * accessor when querying many plots.
     */
    public function household(): ?Household
    {
        return $this->farm?->household;
    }
}
