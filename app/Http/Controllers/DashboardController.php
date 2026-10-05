<?php

namespace App\Http\Controllers;

use App\Models\CropSeason;
use App\Models\Farm;
use App\Models\FarmActivity;
use App\Models\FarmerGroup;
use App\Models\Household;
use App\Models\HouseholdBaseline;
use App\Models\Plot;
use App\Models\Role;
use App\Models\User;

/**
 * Public overview dashboard - reachable without login (routes/web.php has
 * no `auth` middleware on this route) so it works as a general-purpose
 * "state of the program" page for anyone (funders, district officers,
 * farmer groups) without needing an account.
 *
 * Deliberately aggregate-only: every query here is a count/sum/group-by,
 * never a list of individual households/farms by name - a household's
 * head_name is personal data (Blueprint 19 / PDPA is exactly why
 * id_card_number is encrypted on that same model), so this page never
 * exposes it. Anyone wanting per-record detail still has to log in and
 * use the ทะเบียนข้อมูลหลัก screens, which stay behind `auth`.
 */
class DashboardController extends Controller
{
    public function __invoke()
    {
        $householdStatusCounts = Household::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Regional breakdown is keyed off Farm's own province/district/
        // tambon columns (added a previous sprint) rather than trying to
        // derive a household's location through farmer_group/village,
        // since not every household has one of those set - a farm's
        // location is guaranteed structured data going forward.
        //
        // Grouped by อำเภอ/ตำบล, not จังหวัด (user confirmed 15 ก.ย.) -
        // this program only operates within จังหวัดศรีสะเกษ, so a
        // province-level breakdown always collapses to a single row and
        // tells you nothing; district/tambon are the levels that actually
        // vary within that one province.
        $farmsWithLocation = Farm::with(['district', 'tambon'])->get();

        $byDistrict = $farmsWithLocation
            ->groupBy(fn (Farm $farm) => $farm->district?->name_th ?? 'ไม่ระบุอำเภอ')
            ->map(fn ($farms) => [
                'farms' => $farms->count(),
                'households' => $farms->pluck('household_id')->unique()->count(),
                'area_rai' => (float) $farms->sum('total_area_rai'),
            ])
            ->sortByDesc(fn ($row) => $row['farms']);

        $byTambon = $farmsWithLocation
            ->groupBy(fn (Farm $farm) => $farm->tambon?->name_th ?? 'ไม่ระบุตำบล')
            ->map(fn ($farms) => [
                'farms' => $farms->count(),
                'households' => $farms->pluck('household_id')->unique()->count(),
                'area_rai' => (float) $farms->sum('total_area_rai'),
            ])
            ->sortByDesc(fn ($row) => $row['farms']);

        $byVariety = Plot::with('durianVariety')
            ->get()
            ->groupBy(fn (Plot $plot) => $plot->durianVariety?->name ?? 'ไม่ระบุพันธุ์')
            ->map(fn ($plots) => [
                'plots' => $plots->count(),
                'area_rai' => (float) $plots->sum('area_rai'),
                'tree_count' => (int) $plots->sum('tree_count'),
            ])
            ->sortByDesc(fn ($row) => $row['plots']);

        $baselineStatusCounts = HouseholdBaseline::whereNull('original_record_id')
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $activityStatusCounts = FarmActivity::whereNull('original_record_id')
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('dashboard', [
            // "จำนวนเกษตรกรสมาชิก" is defined as the household count, not a
            // separate figure - each household's หัวหน้าครัวเรือน is the
            // program's farmer member, so the two must always match.
            'farmerMemberCount' => Household::count(),
            // Farmer -> Innovator is an upgrade (assignRole(), never
            // syncRoles() - see InnovatorEvaluationController), so this is
            // a subset of farmerMemberCount above, not a separate pool.
            'innovatorCount' => User::role(Role::INNOVATOR)->count(),
            'householdCount' => Household::count(),
            'householdStatusCounts' => $householdStatusCounts,
            'farmCount' => Farm::count(),
            'totalFarmAreaRai' => (float) Farm::sum('total_area_rai'),
            'plotCount' => Plot::count(),
            'totalPlotAreaRai' => (float) Plot::sum('area_rai'),
            'totalTreeCount' => (int) Plot::sum('tree_count'),
            'farmerGroupCount' => FarmerGroup::count(),
            'activeCropSeason' => CropSeason::where('status', 'active')->first(),
            'byDistrict' => $byDistrict,
            'byTambon' => $byTambon,
            'byVariety' => $byVariety,
            'baselineStatusCounts' => $baselineStatusCounts,
            'activityStatusCounts' => $activityStatusCounts,
        ]);
    }
}
