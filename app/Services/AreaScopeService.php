<?php

namespace App\Services;

use App\Models\Household;
use App\Models\Role;
use App\Models\User;
use App\Models\UserAreaAssignment;
use Illuminate\Database\Eloquent\Builder;

/**
 * Blueprint section 4.1 / 21.1: single place that turns a user's
 * user_area_assignments rows into query filters, so no controller has to
 * re-implement the province -> district -> tambon -> village join chain.
 *
 * Wired up for real as of the 15 ก.ย. authorization round - see
 * app/Policies/*.php and the controllers under app/Http/Controllers that
 * call householdIdsFor()/canAccessHousehold(). Before that round this
 * class had the right shape but nothing called it, so every logged-in
 * user (including the Farmer-role logins created for every household)
 * could see/edit every household in the system regardless of role.
 */
class AreaScopeService
{
    /**
     * Super Admin always has full access by definition of the role
     * (Blueprint section 4: "ดูแลระบบทั้งหมด") - checked directly by role
     * rather than only via a SCOPE_ALL assignment row, so a Super Admin
     * account can never lock itself out just because nobody happened to
     * seed/assign that row for it. Other roles (Project Admin, Researcher
     * per the role table) get full access the data-driven way, via an
     * explicit SCOPE_ALL row - see DemoUserSeeder.
     */
    public function hasFullAccess(User $user): bool
    {
        if ($user->hasRole(Role::SUPER_ADMIN)) {
            return true;
        }

        return $user->areaAssignments()
            ->where('scope_type', UserAreaAssignment::SCOPE_ALL)
            ->exists();
    }

    /**
     * Constrain a Household query (or anything joined back to
     * village -> tambon -> district -> province) to what $user may see.
     *
     * Fail CLOSED, not open: a user with zero area_assignments rows (e.g.
     * a Field Officer who hasn't been assigned an area yet, or an
     * Evaluator/Viewer who per Blueprint section 4 isn't geographically
     * scoped at all) sees nothing here, rather than the previous bug
     * where an empty `where(fn () => ...)` group with no orWhere calls
     * added inside it gets silently dropped by the query builder - which
     * meant "no assignment configured" behaved exactly like "assigned to
     * every area", the opposite of what Blueprint 4.1 intends. Found
     * during the 15 ก.ย. review; fixed here.
     */
    public function scopeHouseholds(Builder $query, User $user): Builder
    {
        if ($this->hasFullAccess($user)) {
            return $query;
        }

        $assignments = $user->areaAssignments()->get();

        if ($assignments->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $q) use ($assignments) {
            foreach ($assignments as $assignment) {
                match ($assignment->scope_type) {
                    UserAreaAssignment::SCOPE_FARMER_GROUP => $q->orWhere('farmer_group_id', $assignment->scope_id),
                    UserAreaAssignment::SCOPE_TAMBON => $q->orWhereHas('village', fn ($v) => $v->where('tambon_id', $assignment->scope_id)),
                    UserAreaAssignment::SCOPE_DISTRICT => $q->orWhereHas('village.tambon', fn ($t) => $t->where('district_id', $assignment->scope_id)),
                    UserAreaAssignment::SCOPE_PROVINCE => $q->orWhereHas('village.tambon.district', fn ($d) => $d->where('province_id', $assignment->scope_id)),
                    default => null,
                };
            }
        });
    }

    /**
     * The set of household IDs $user is allowed to see/touch, or null
     * when they have unrestricted access (full access, or - equivalently
     * for this purpose - a Super Admin). Every Policy and every scoped
     * index() query in the app should go through this one method instead
     * of re-deriving the rule, so Household/Farm/Plot/Baseline/Activity/
     * InnovatorEvaluation all agree on who can see what:
     *
     * - Full access (Super Admin, or a SCOPE_ALL assignment) -> null
     *   (no filter - caller applies none).
     * - Farmer/Innovator (Role::OWN_HOUSEHOLD) -> exactly the one
     *   household their login is linked to (or an empty array if their
     *   account somehow isn't linked to one yet - e.g. the generic
     *   farmer@drfis.local/innovator@drfis.local example accounts from
     *   DemoUserSeeder, which exist to show the role in M01 but aren't
     *   tied to a real household - use the hh-0001@drfis.local-style
     *   accounts from FarmerAccountSeeder to test the Farmer experience
     *   against real data instead).
     * - Everyone else (Field Officer, District Officer, Researcher/
     *   Project Admin without a SCOPE_ALL row, Evaluator, Viewer) ->
     *   whatever scopeHouseholds() resolves from their area assignments,
     *   which is an empty array when they have none (fail closed).
     */
    public function householdIdsFor(User $user): ?array
    {
        if ($this->hasFullAccess($user)) {
            return null;
        }

        if ($user->hasAnyRole(Role::OWN_HOUSEHOLD)) {
            return $user->household ? [$user->household->id] : [];
        }

        return Household::query()
            ->tap(fn (Builder $q) => $this->scopeHouseholds($q, $user))
            ->pluck('id')
            ->all();
    }

    /**
     * Single-record version of householdIdsFor() for Policy view/update
     * checks, where loading every allowed household ID just to check one
     * would be wasteful.
     */
    public function canAccessHousehold(User $user, int $householdId): bool
    {
        if ($this->hasFullAccess($user)) {
            return true;
        }

        if ($user->hasAnyRole(Role::OWN_HOUSEHOLD)) {
            return $user->household?->id === $householdId;
        }

        return Household::query()
            ->whereKey($householdId)
            ->tap(fn (Builder $q) => $this->scopeHouseholds($q, $user))
            ->exists();
    }
}
