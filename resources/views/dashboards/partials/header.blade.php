{{--
 Shared header for the merged Dashboard (23 ก.ย. round): one page title,
 one filter row (ฤดูผลิต) and one tab bar across ภาพรวม + the 6 internal
 views, replacing the 7 separate sidenav links. The tabs are still 7
 separate routes (each view's queries stay separate - the cost view
 alone runs ProductionCostService per household, too heavy to load on
 every visit to one mega-page), they just read as one Dashboard now.

 Tabs carry the selected crop_season_id along, so switching view keeps
 the same season. The internal tabs are only shown to roles that can
 open them (InternalDashboardController::ensureCanViewDashboards() -
 everyone logged in except Farmer/Innovator); guests and Farmer/
 Innovator see just the public overview, exactly as before.

 Expects: $heading, $subtitle; optional $season/$seasons (season filter).
--}}
@php
 $canSeeInternal = auth()->check() && ! auth()->user()->hasAnyRole(\App\Models\Role::OWN_HOUSEHOLD);
 $seasonQuery = ! empty($season) ? ['crop_season_id' => $season->id] : [];
 $dashTabs = [
 ['route' => 'dashboard', 'label' => 'ภาพรวมโครงการ', 'icon' => 'dashboard'],
 ['route' => 'dashboards.research', 'label' => 'ระบบวิจัยและประเมินผล', 'icon' => 'science'],
 ['route' => 'dashboards.farm-management', 'label' => 'ระบบการจัดการสวน', 'icon' => 'map'],
 ['route' => 'dashboards.cost', 'label' => 'ระบบต้นทุนการผลิต', 'icon' => 'payments'],
 ['route' => 'dashboards.biomass', 'label' => 'ระบบชีวมวลและผลิตภัณฑ์', 'icon' => 'compost'],
 ['route' => 'dashboards.forecast', 'label' => 'ระบบพยากรณ์ผลผลิต', 'icon' => 'insights'],
 ['route' => 'dashboards.carbon', 'label' => 'ระบบคาร์บอน', 'icon' => 'co2'],
 ];
@endphp
<div class="dash-header">
 <div>
 <p class="dash-eyebrow">ระบบภาพรวมและวิเคราะห์</p>
 <h3 class="dash-title">{{ $heading }}</h3>
 @if (! empty($subtitle))
 <p class="dash-sub">{{ $subtitle }}</p>
 @endif
 </div>
 @if (! empty($seasons) && count($seasons) > 0)
 @include('dashboards.partials.season-selector')
 @endif
</div>

@if ($canSeeInternal)
 <nav class="dash-tabs" aria-label="มุมมอง Dashboard">
 @foreach ($dashTabs as $tab)
 @php
 $isActive = request()->routeIs($tab['route']);
 @endphp
 <a href="{{ route($tab['route'], $tab['route'] === 'dashboard' ? [] : $seasonQuery) }}"
 class="dash-tab {{ $isActive ? 'active' : '' }}"
 @if ($isActive) aria-current="page" @endif>
 <i class="material-symbols-rounded" aria-hidden="true">{{ $tab['icon'] }}</i>{{ $tab['label'] }}
 </a>
 @endforeach
 </nav>
@endif

@once
 @push('scripts')
 <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
 <script src="{{ asset('js/drfis-charts.js') }}"></script>
 @endpush
@endonce
