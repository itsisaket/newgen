<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\ResearchExportController;
use App\Http\Controllers\RevisionController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\FarmActivityController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\HarvestRecordController;
use App\Http\Controllers\HouseholdBaselineController;
use App\Http\Controllers\EconomicImpactController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\CropSeasonController;
use App\Http\Controllers\AlpAssessmentController;
use App\Http\Controllers\BranchDisposalRecordController;
use App\Http\Controllers\CfpController;
use App\Http\Controllers\HouseholdBaselineInputController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\CompetencyAssessmentController;
use App\Http\Controllers\MarketValidationController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\InnovatorController;
use App\Http\Controllers\InnovatorEvaluationController;
use App\Http\Controllers\KnowledgeTransferController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\VillageController;
use App\Http\Controllers\FarmerGroupController;
use App\Http\Controllers\PhenologyRecordController;
use App\Http\Controllers\PlotController;
use App\Http\Controllers\ProductionCostController;
use App\Http\Controllers\InventoryTransactionController;
use App\Http\Controllers\KilnBatchController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductUsageController;
use App\Http\Controllers\TechnologyAssetController;
use App\Http\Controllers\TechnologyAssignmentController;
use App\Http\Controllers\TechnologyController;
use App\Http\Controllers\UserAreaAssignmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CarbonActivityController;
use App\Http\Controllers\EmissionFactorController;
use App\Http\Controllers\DemonstrationComparisonGroupController;
use App\Http\Controllers\InternalDashboardController;
use App\Http\Controllers\DemonstrationPlotController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Public overview dashboard (aggregate counts/breakdowns only, no
// per-person data) - deliberately outside the `auth` middleware group so
// it works both logged out (public landing page) and logged in (still
// reachable from the sidenav "Dashboard" link, which is why
// layouts/app.blade.php has both an @auth and a @guest rendering branch).
Route::get('/dashboard', DashboardController::class)->name('dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Internal Dashboard suite (Blueprint หัวข้อ 14 / Sprint 5 "Dashboard
    // เต็มรูปแบบ 6 มุมมอง") - distinct from the public DashboardController
    // above (aggregate-only, no login needed). See
    // InternalDashboardController's doc-comment for access rules.
    Route::get('dashboards/research', [InternalDashboardController::class, 'research'])
        ->name('dashboards.research');
    Route::get('dashboards/farm-management', [InternalDashboardController::class, 'farmManagement'])
        ->name('dashboards.farm-management');
    Route::get('dashboards/cost', [InternalDashboardController::class, 'cost'])
        ->name('dashboards.cost');
    Route::get('dashboards/biomass', [InternalDashboardController::class, 'biomass'])
        ->name('dashboards.biomass');
    Route::get('dashboards/forecast', [InternalDashboardController::class, 'forecast'])
        ->name('dashboards.forecast');
    Route::get('dashboards/carbon', [InternalDashboardController::class, 'carbon'])
        ->name('dashboards.carbon');

    // Household / Farm / Plot registration (Blueprint section 3.1/7.1).
    // Master/reference data - Household -> Farm -> Plot is the hierarchy
    // that F01/F13/F14/etc. all point back to. This was missing entirely
    // before: F01 and F13 could only pick a household/plot that already
    // existed via a dropdown - there was no way to register a new one.
    // No destroy on any of these (same convention as users - `status`
    // tracks active/inactive/withdrawn instead of a hard delete).
    Route::resource('households', HouseholdController::class)->except(['destroy']);
    // 1 ครัวเรือน = 1 เกษตรกร (login Farmer) - see
    // App\Support\FarmerAccountProvisioner / HouseholdController::store().
    // Password reset is here (not a resource action) since it's a single
    // one-off button on households/show.blade.php, not its own CRUD screen.
    Route::post('households/{household}/reset-farmer-password', [HouseholdController::class, 'resetFarmerPassword'])
        ->name('households.reset-farmer-password');
    // ฤดูผลิต (Crop Season) master data - previously seeder-only, no CRUD UI
    // at all (see claude project doc DRFIS-Workflow-Menu-Analysis-23Sep.md ข้อ
    // 3/4). No destroy - same "status tracks state" convention as
    // Household/Technology; see CropSeasonPolicy for why create/update is
    // Super Admin/Project Admin only.
    Route::resource('crop-seasons', CropSeasonController::class)
        ->only(['index', 'create', 'store', 'edit', 'update']);
    Route::resource('villages', VillageController::class)
        ->only(['index', 'create', 'store', 'edit', 'update']);
    Route::resource('farmer-groups', FarmerGroupController::class)
        ->only(['index', 'create', 'store', 'edit', 'update']);

    // F02/F06 cross-household overview pages (23 ก.ย. round) - previously
    // production-cost/economic-impact only existed per-household via the
    // routes just below; these give a list view across every household in
    // the staff member's area instead of opening them one at a time.
    Route::get('production-costs', [ProductionCostController::class, 'index'])
        ->name('production-costs.index');
    Route::get('economic-impacts', [EconomicImpactController::class, 'index'])
        ->name('economic-impacts.index');

    // F02-lite - Production & Cost report (Blueprint section 6 F02) - see
    // App\Services\ProductionCostService's doc-comment. A GET report page,
    // not a resource - there's nothing to create/edit here, it's computed
    // fresh from F13 (farm_activities) every time it's opened.
    Route::get('households/{household}/production-cost', [ProductionCostController::class, 'show'])
        ->name('households.production-cost');
    // F06 - Economic Impact report (Blueprint 11) - see
    // EconomicImpactController's doc-comment.
    Route::get('households/{household}/economic-impact', [EconomicImpactController::class, 'show'])
        ->name('households.economic-impact');
    Route::resource('farms', FarmController::class)->except(['destroy']);
    Route::resource('plots', PlotController::class)->except(['destroy']);
    // Step 4 of the auto-chaining registration flow (household -> farm ->
    // plot -> research tools) - see PlotController::store()/registrationComplete().
    Route::get('plots/{plot}/registration-complete', [PlotController::class, 'registrationComplete'])
        ->name('plots.registration-complete');

    // F05 - Demonstration Plot (Blueprint has no Data Dictionary for this
    // module - see claude project doc DRFIS-Sprint5-Design-16Sep.md ส่วน B
    // / ส่วน D for the schema this implements). The comparison group
    // (experiment) is its own resource; enrolling a plot into one is
    // plot-locked like farm-activities above - see
    // DemonstrationPlotController's doc-comment.
    Route::resource('demonstration-comparison-groups', DemonstrationComparisonGroupController::class)
        ->only(['index', 'create', 'store', 'show']);
    Route::post('demonstration-comparison-groups/{demonstration_comparison_group}/complete', [DemonstrationComparisonGroupController::class, 'complete'])
        ->name('demonstration-comparison-groups.complete');

    Route::resource('demonstration-plots', DemonstrationPlotController::class)
        ->only(['create', 'store']);
    Route::post('demonstration-plots/{demonstration_plot}/end', [DemonstrationPlotController::class, 'end'])
        ->name('demonstration-plots.end');

    // จังหวัด -> อำเภอ -> ตำบล cascading select data (JSON), backing
    // public/js/cascading-location.js on the household/farm forms above -
    // see App\Http\Controllers\LocationController's doc-comment for why
    // this is AJAX instead of inlining the full dataset into every page.
    Route::get('locations/districts', [LocationController::class, 'districts'])->name('locations.districts');
    Route::get('locations/tambons', [LocationController::class, 'tambons'])->name('locations.tambons');
    Route::get('locations/villages', [LocationController::class, 'villages'])->name('locations.villages');

    // F01 - Household Baseline (Blueprint section 6). Workflow actions
    // (submit/verify/approve/reject) all go through WorkflowService -
    // see app/Http/Controllers/HouseholdBaselineController.php.
    Route::resource('household-baselines', HouseholdBaselineController::class)
        ->except(['destroy']);
    // CFP - Baseline เชิงปริมาณ: รายการปัจจัยต่อปีของครัวเรือน (แก้ได้เฉพาะขณะร่าง)
    Route::post('household-baselines/{household_baseline}/inputs', [HouseholdBaselineInputController::class, 'store'])
        ->name('household-baselines.inputs.store');
    Route::delete('household-baselines/{household_baseline}/inputs/{input}', [HouseholdBaselineInputController::class, 'destroy'])
        ->name('household-baselines.inputs.destroy');
    Route::post('household-baselines/{household_baseline}/submit', [HouseholdBaselineController::class, 'submit'])
        ->name('household-baselines.submit');
    Route::post('household-baselines/{household_baseline}/verify', [HouseholdBaselineController::class, 'verify'])
        ->name('household-baselines.verify');
    Route::post('household-baselines/{household_baseline}/approve', [HouseholdBaselineController::class, 'approve'])
        ->name('household-baselines.approve');
    Route::post('household-baselines/{household_baseline}/reject', [HouseholdBaselineController::class, 'reject'])
        ->name('household-baselines.reject');

    // F14-lite - Durian Phenology observation log (Blueprint section 10.1).
    // Plot-locked create, same convention as farm-activities - see
    // PhenologyRecordController's doc-comment. No workflow actions (no
    // submit/verify/approve/reject) - see the migration's doc-comment.
    Route::resource('durian-phenology-records', PhenologyRecordController::class)
        ->only(['index', 'create', 'store', 'show']);

    // F08/F14-lite - Harvest recording (Blueprint Appendix C.2
    // harvest_records). Plot-locked create like F13, but DOES go through
    // WorkflowService (submit/verify/approve/reject) - see
    // HarvestRecordController's doc-comment for why.
    Route::resource('harvest-records', HarvestRecordController::class)
        ->only(['index', 'create', 'store', 'show']);
    Route::post('harvest-records/{harvest_record}/submit', [HarvestRecordController::class, 'submit'])
        ->name('harvest-records.submit');
    Route::post('harvest-records/{harvest_record}/verify', [HarvestRecordController::class, 'verify'])
        ->name('harvest-records.verify');
    Route::post('harvest-records/{harvest_record}/approve', [HarvestRecordController::class, 'approve'])
        ->name('harvest-records.approve');
    Route::post('harvest-records/{harvest_record}/reject', [HarvestRecordController::class, 'reject'])
        ->name('harvest-records.reject');

    // CFP - การจัดการกิ่ง/เศษไม้ตามเส้นทางกำจัด (Plot-locked create + Workflow เหมือน harvest-records)
    Route::resource('branch-disposal-records', BranchDisposalRecordController::class)
        ->only(['index', 'create', 'store', 'show']);
    Route::post('branch-disposal-records/{branch_disposal_record}/submit', [BranchDisposalRecordController::class, 'submit'])
        ->name('branch-disposal-records.submit');
    Route::post('branch-disposal-records/{branch_disposal_record}/verify', [BranchDisposalRecordController::class, 'verify'])
        ->name('branch-disposal-records.verify');
    Route::post('branch-disposal-records/{branch_disposal_record}/approve', [BranchDisposalRecordController::class, 'approve'])
        ->name('branch-disposal-records.approve');
    Route::post('branch-disposal-records/{branch_disposal_record}/reject', [BranchDisposalRecordController::class, 'reject'])
        ->name('branch-disposal-records.reject');

    // CFP - รอบการผลิต + คำนวณ + ผลลัพธ์/Sensitivity (ถึงประตูสวน)
    Route::get('cfp', [CfpController::class, 'index'])->name('cfp.index');
    Route::get('cfp/plots/{plot}', [CfpController::class, 'plot'])->name('cfp.plot');
    Route::post('cfp/plots/{plot}/cycles', [CfpController::class, 'openCycle'])->name('cfp.cycles.open');
    Route::post('cfp/plots/{plot}/cycles/{cycle}/close', [CfpController::class, 'closeCycle'])->name('cfp.cycles.close');
    Route::post('cfp/plots/{plot}/calculate', [CfpController::class, 'calculate'])->name('cfp.calculate');
    Route::get('cfp/calculations/{calculation}', [CfpController::class, 'show'])->name('cfp.show');

    // F13 - Farm Activity Log (Blueprint section 7.2).
    Route::resource('farm-activities', FarmActivityController::class)
        ->only(['index', 'create', 'store', 'show']);
    Route::post('farm-activities/{farm_activity}/submit', [FarmActivityController::class, 'submit'])
        ->name('farm-activities.submit');
    Route::post('farm-activities/{farm_activity}/verify', [FarmActivityController::class, 'verify'])
        ->name('farm-activities.verify');
    Route::post('farm-activities/{farm_activity}/approve', [FarmActivityController::class, 'approve'])
        ->name('farm-activities.approve');
    Route::post('farm-activities/{farm_activity}/reject', [FarmActivityController::class, 'reject'])
        ->name('farm-activities.reject');

    // F09-lite - Innovator Evaluation (simplified pass/fail gate for the
    // เกษตรกร -> นวัตกรชุมชน role upgrade - see
    // InnovatorEvaluationController's doc-comment for why this isn't the
    // full Blueprint F07/F09 module yet). No edit/destroy - a recorded
    // result is a historical record, same convention as F13.
    Route::resource('innovator-evaluations', InnovatorEvaluationController::class)
        ->only(['index', 'create', 'store', 'show']);

    // Sprint 4: F07 ALP + F09 Competency + F11 Knowledge Transfer master
    // registry (Blueprint Appendix C.3) - see the innovators migration's
    // doc-comment for why this replaces the "Innovator = User with role"
    // assumption from the 15 ก.ย. F09-lite round.
    Route::resource('innovators', InnovatorController::class)
        ->only(['index', 'create', 'store', 'show']);

    // F11 Knowledge Transfer (Blueprint Appendix C.5). Innovator-locked
    // create, no workflow - see KnowledgeTransferController's doc-comment.
    Route::resource('knowledge-transfers', KnowledgeTransferController::class)
        ->only(['index', 'create', 'store', 'show']);

    // F07 - ALP Assessment (Blueprint 12.1). Household-locked create, no
    // workflow - see AlpAssessmentController's doc-comment.
    Route::resource('alp-assessments', AlpAssessmentController::class)
        ->only(['index', 'create', 'store', 'show']);

    // F09 - Innovator Competency (Blueprint 12.2). Innovator-locked create,
    // no workflow - see CompetencyAssessmentController's doc-comment.
    Route::resource('competency-assessments', CompetencyAssessmentController::class)
        ->only(['index', 'create', 'store', 'show']);

    // F08 full - buyers master data (Blueprint Appendix C.5) + Market
    // Validation (buyer-locked demand) + Sales (household-locked actual
    // revenue - the source EconomicImpactService reads from for F06).
    Route::resource('buyers', BuyerController::class)
        ->only(['index', 'create', 'store', 'show']);
    Route::resource('market-validations', MarketValidationController::class)
        ->only(['index', 'create', 'store', 'show']);
    Route::resource('sales', SaleController::class)
        ->only(['index', 'create', 'store', 'show']);

    // F10 - Evidence (polymorphic upload attached from any module's show view).
    Route::post('evidences', [EvidenceController::class, 'store'])->name('evidences.store');
    Route::get('evidences/{evidence}/file', [EvidenceController::class, 'download'])->name('evidences.download');
    Route::delete('evidences/{evidence}', [EvidenceController::class, 'destroy'])->name('evidences.destroy');

    Route::post('workflow/{type}/{id}/request-revision', [RevisionController::class, 'store'])
        ->whereIn('type', ['household-baseline', 'farm-activity', 'harvest-record', 'kiln-batch', 'carbon-activity', 'branch-disposal-record'])
        ->whereNumber('id')
        ->name('workflow.revisions.store');

    Route::prefix('reports/exports')->name('reports.exports.')->group(function () {
        Route::get('research.csv', [ResearchExportController::class, 'csv'])->name('csv');
        Route::get('research.xlsx', [ResearchExportController::class, 'xlsx'])->name('xlsx');
        Route::get('summary.pdf', [ResearchExportController::class, 'pdf'])->name('pdf');
    });
    Route::get('reports', [ResearchExportController::class, 'index'])->name('reports.index');
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // Sprint 3: Technology & Biomass (Blueprint section 8 / roadmap
    // section 22 Sprint 3) - F12 Technology Management, F03 Kiln
    // Production Log, F04 Bio-product Utilization, and the Inventory
    // Ledger (section 8.3) that ties them together.

    // F12 master data - technology TYPES, then physical assets under a
    // type, then allocation of an asset to a household. No destroy on any
    // of these (same "status tracks state, no hard delete" convention as
    // Household/Farm/Plot).
    Route::resource('technologies', TechnologyController::class)
        ->only(['index', 'create', 'store', 'show']);
    Route::resource('technology-assets', TechnologyAssetController::class)
        ->only(['index', 'create', 'store', 'show']);
    Route::get('technology-assignments/create', [TechnologyAssignmentController::class, 'create'])
        ->name('technology-assignments.create');
    Route::post('technology-assignments', [TechnologyAssignmentController::class, 'store'])
        ->name('technology-assignments.store');
    Route::post('technology-assignments/{technology_assignment}/return', [TechnologyAssignmentController::class, 'returnAsset'])
        ->name('technology-assignments.return');

    // F04/Inventory master data - product TYPES (น้ำส้มควันไม้/ไบโอชาร์/ถ่านชาร์จ/
    // ถ่านกัมมันต์ ฯลฯ).
    Route::resource('products', ProductController::class)
        ->only(['index', 'create', 'store', 'show']);

    // F03 - Kiln Production Log. Household-locked create; workflow
    // actions go through WorkflowService like F13/F08-harvest - see
    // KilnBatchController's doc-comment for why approve() is also where
    // the production inventory_transactions get written.
    Route::resource('kiln-batches', KilnBatchController::class)
        ->only(['index', 'create', 'store', 'show']);
    Route::post('kiln-batches/{kiln_batch}/submit', [KilnBatchController::class, 'submit'])
        ->name('kiln-batches.submit');
    Route::post('kiln-batches/{kiln_batch}/verify', [KilnBatchController::class, 'verify'])
        ->name('kiln-batches.verify');
    Route::post('kiln-batches/{kiln_batch}/approve', [KilnBatchController::class, 'approve'])
        ->name('kiln-batches.approve');
    Route::post('kiln-batches/{kiln_batch}/reject', [KilnBatchController::class, 'reject'])
        ->name('kiln-batches.reject');

    // F15 - Carbon Activity Monitoring (Blueprint Appendix C.6). Same
    // household-locked create + WorkflowService shape as F03 kiln-batches
    // above - see CarbonActivityController's doc-comment. Emission
    // factor master data (Super Admin/Project Admin only, see
    // EmissionFactorPolicy) is a separate resource, not household-scoped.
    Route::resource('carbon-activities', CarbonActivityController::class)
        ->only(['index', 'create', 'store', 'show']);
    Route::post('carbon-activities/{carbon_activity}/submit', [CarbonActivityController::class, 'submit'])
        ->name('carbon-activities.submit');
    Route::post('carbon-activities/{carbon_activity}/verify', [CarbonActivityController::class, 'verify'])
        ->name('carbon-activities.verify');
    Route::post('carbon-activities/{carbon_activity}/approve', [CarbonActivityController::class, 'approve'])
        ->name('carbon-activities.approve');
    Route::post('carbon-activities/{carbon_activity}/reject', [CarbonActivityController::class, 'reject'])
        ->name('carbon-activities.reject');

    Route::resource('emission-factors', EmissionFactorController::class)
        ->only(['index', 'create', 'store']);

    // F04 - product usage on a plot (plot-locked create, like F13/F08-harvest).
    Route::resource('product-usages', ProductUsageController::class)
        ->only(['index', 'create', 'store', 'show']);

    // Inventory ledger (Blueprint 8.3) - household-scoped history plus a
    // manual-movement form for sale/transfer/loss/adjustment (production
    // and farm_use are always written by kiln-batches/product-usages
    // instead - see InventoryTransactionController's doc-comment).
    Route::get('inventory-transactions', [InventoryTransactionController::class, 'index'])
        ->name('inventory-transactions.index');
    Route::get('inventory-transactions/create', [InventoryTransactionController::class, 'create'])
        ->name('inventory-transactions.create');
    Route::post('inventory-transactions', [InventoryTransactionController::class, 'store'])
        ->name('inventory-transactions.store');

    // M01 - User & Security (Blueprint Appendix A "ผู้ใช้งานและสิทธิ์").
    // Super Admin only - enforced by UserPolicy, not by route middleware,
    // so the 403 page (not a redirect) is what a non-Super-Admin sees.
    Route::resource('users', UserController::class)
        ->only(['index', 'create', 'store', 'edit', 'update']);
    Route::post('users/{user}/area-assignments', [UserAreaAssignmentController::class, 'store'])
        ->name('users.area-assignments.store');
    Route::delete('users/{user}/area-assignments/{assignment}', [UserAreaAssignmentController::class, 'destroy'])
        ->name('users.area-assignments.destroy');
});

// Remaining F02, F03-F12, F14-F15 module routes are added incrementally
// per the Sprint roadmap (Blueprint section 22).
