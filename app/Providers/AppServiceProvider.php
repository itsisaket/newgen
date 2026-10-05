<?php

namespace App\Providers;

use App\Contracts\SmsSender;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Support\Sms\LogSmsSender;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Observers\AuditObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Backlog item - see App\Support\Sms\LogSmsSender's doc-comment.
        // Swap this binding for a real gateway implementation later;
        // nothing else in the codebase references LogSmsSender directly.
        $this->app->bind(SmsSender::class, LogSmsSender::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([
            \App\Models\Household::class,
            \App\Models\Farm::class,
            \App\Models\Plot::class,
            \App\Models\HouseholdBaseline::class,
            \App\Models\HouseholdBaselineInput::class,
            \App\Models\FarmActivity::class,
            \App\Models\HarvestRecord::class,
            \App\Models\BranchDisposalRecord::class,
            \App\Models\KilnBatch::class,
            \App\Models\KilnBatchOutput::class,
            \App\Models\ProductUsage::class,
            \App\Models\InventoryTransaction::class,
            \App\Models\Technology::class,
            \App\Models\TechnologyAsset::class,
            \App\Models\TechnologyAssignment::class,
            \App\Models\Buyer::class,
            \App\Models\MarketValidation::class,
            \App\Models\Sale::class,
            \App\Models\AlpAssessment::class,
            \App\Models\CompetencyAssessment::class,
            \App\Models\KnowledgeTransfer::class,
            \App\Models\CarbonActivity::class,
            \App\Models\EmissionFactor::class,
            \App\Models\Evidence::class,
        ] as $model) {
            $model::observe(AuditObserver::class);
        }

        // Explicit even though App\Models\User -> App\Policies\UserPolicy
        // already matches Laravel's auto-discovery convention, so M01's
        // authorization rules (Blueprint section 4) are easy to find.
        Gate::policy(User::class, UserPolicy::class);

        // Backlog item - Material Dashboard-themed pagination links
        // instead of Laravel's default Tailwind markup (which doesn't
        // match this app's Bootstrap-based theme at all) - see
        // resources/views/vendor/pagination/material-dashboard.blade.php.
        // Only defaultView (full ->paginate()) is overridden - nothing in
        // this codebase uses ->simplePaginate() today.
        Paginator::defaultView('vendor.pagination.material-dashboard');
    }
}
