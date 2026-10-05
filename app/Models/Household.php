<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Household extends Model
{
    protected $fillable = [
        'household_code',
        'head_name',
        'id_card_number_encrypted',
        'phone',
        'farmer_group_id',
        'village_id',
        'registered_at',
        'status',
        // Set only by App\Support\FarmerAccountProvisioner, never from a
        // form - links this household to its one Farmer-role login
        // account (1 ครัวเรือน = 1 เกษตรกร login).
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            // PDPA (Blueprint 19): decrypts transparently on read/write,
            // never stored or logged in plain text.
            'id_card_number_encrypted' => 'encrypted',
            'registered_at' => 'date',
        ];
    }

    public function farmerGroup()
    {
        return $this->belongsTo(FarmerGroup::class);
    }

    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    public function farms()
    {
        return $this->hasMany(Farm::class);
    }

    public function baselines()
    {
        return $this->hasMany(HouseholdBaseline::class);
    }

    /**
     * The one Farmer-role login account belonging to this household (see
     * App\Support\FarmerAccountProvisioner). Nullable - a household
     * registered before this feature existed may not have one until
     * `php artisan db:seed` backfills it.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * F09-lite pass/fail history gating the เกษตรกร -> นวัตกรชุมชน role
     * upgrade (see InnovatorEvaluationController).
     */
    public function innovatorEvaluations()
    {
        return $this->hasMany(InnovatorEvaluation::class)->latest('evaluation_date');
    }

    /**
     * F07/F09/F11 Sprint 4 - the `innovators` registry row for this
     * household, once it has been promoted (see
     * InnovatorEvaluationController::store() and the innovators
     * migration's doc-comment). Null until then.
     */
    public function innovator()
    {
        return $this->hasOne(Innovator::class);
    }

    /**
     * F07 - ALP Assessment history (Blueprint 12.1, alp_assessments.household_id).
     */
    public function alpAssessments()
    {
        return $this->hasMany(AlpAssessment::class)->latest('assessment_date');
    }

    /**
     * F08 full - actual sales recorded against this household (Blueprint
     * ค.5, sales.seller_household_id) - replaces the F14/F08-lite round's
     * harvest_records.buyer_id as the real revenue source for F06.
     */
    public function sales()
    {
        return $this->hasMany(Sale::class, 'seller_household_id')->latest('sale_date');
    }

    /**
     * F12 - technology assets currently/previously allocated to this
     * household (Sprint 3: Technology & Biomass round).
     */
    public function technologyAssignments()
    {
        return $this->hasMany(TechnologyAssignment::class)->latest('assigned_date');
    }

    /**
     * F03 - kiln runs logged against this household (Sprint 3 round).
     */
    public function kilnBatches()
    {
        return $this->hasMany(KilnBatch::class)->latest('batch_date');
    }

    public function carbonActivities()
    {
        return $this->hasMany(CarbonActivity::class)->latest('activity_date');
    }

    /**
     * Inventory ledger rows for products this household has produced/used/
     * sold/transferred (Blueprint 8.3 - Sprint 3 round). See
     * App\Services\InventoryLedgerService.
     */
    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class)->latest('transaction_date');
    }
}
