<!DOCTYPE html>
<html lang="th">
<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
 <title>{{ $title ?? 'DRFIS' }} - Durian Research & Farm Intelligence System</title>
 <meta name="csrf-token" content="{{ csrf_token() }}">
 <meta name="theme-color" content="#2e7d32">
 <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">

 {{--
 Material Dashboard 2 (Creative Tim, MIT license, v3.2.0) pinned via
 jsdelivr's GitHub proxy - no Composer/npm involved, same CDN-link
 pattern Sprint 1 already used for plain Bootstrap 5. See the project
 doc for why (Composer/Packagist is blocked from the build side, but
 these links are loaded by the browser, not by this project's build).
 --}}
 <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap">
 <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0">
 <link id="pagestyle" href="https://cdn.jsdelivr.net/gh/creativetimofficial/material-dashboard@v3.2.0/assets/css/material-dashboard.min.css" rel="stylesheet">
 <link rel="stylesheet" href="{{ asset('vendor/choices/choices.min.css') }}?v={{ filemtime(public_path('vendor/choices/choices.min.css')) }}">
 {{-- DRFIS brand override: green/white/gold palette + Kanit font, see the file for why. --}}
 <link rel="stylesheet" href="{{ asset('css/drfis-theme.css') }}?v={{ filemtime(public_path('css/drfis-theme.css')) }}">
 {{-- Dashboard kit (KPI tiles, chart cards, tabs) - see the file's doc-comment. --}}
 <link rel="stylesheet" href="{{ asset('css/drfis-dashboard.css') }}?v={{ filemtime(public_path('css/drfis-dashboard.css')) }}">
 {{--
 flatpickr (pinned, same unpkg.com CDN pattern as the Leaflet include
 in resources/views/components/gps-picker.blade.php - see
 public/js/date-picker.js for why every input[type=date] needs this:
 the native date picker's visible text format is controlled by the
 browser/OS locale, not by this app, so it does not reliably show
 dd/mm/yyyy on its own.
 --}}
 <link rel="stylesheet" href="https://unpkg.com/flatpickr@4.6.13/dist/flatpickr.min.css">
</head>
<body class="g-sidenav-show bg-gray-100">
 @auth
 <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 bg-white my-2" id="sidenav-main">
 <div class="sidenav-header">
 <i class="material-symbols-rounded p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" id="iconSidenav">close</i>
 <a class="navbar-brand px-4 py-3 m-0" href="{{ route('dashboard') }}">
 <span class="drfis-brand-mark" aria-hidden="true"></span>
 <span class="ms-2 drfis-brand">DRFIS</span>
 </a>
 </div>
 <hr class="horizontal dark mt-0 mb-2">
 <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
 <ul class="navbar-nav">
 {{-- 23 ก.ย. round: the public overview + 6 internal Dashboard views
 are now one merged Dashboard with its own tab bar (see
 resources/views/dashboards/partials/header.blade.php),
 so the sidenav has a single entry instead of 7 links.
 It stays highlighted on every tab. Which tabs a user
 sees is decided by the header partial (Farmer/Innovator
 see only the public overview, as before). --}}
 <li class="nav-item">
 <a class="nav-link {{ request()->routeIs('dashboard', 'dashboards.*') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('dashboard') }}">
 <i class="material-symbols-rounded opacity-5">dashboard</i>
 <span class="nav-link-text ms-1">ระบบภาพรวมและวิเคราะห์</span>
 </a>
 </li>
 {{-- 24 ก.ย.: เมนูแบบง่ายสำหรับเกษตรกร/นวัตกร (บทบาทระดับครัวเรือน) - 6 ข้อ ภาษาพูด ไม่มีรหัส F
 เจ้าหน้าที่/นักวิจัยเห็นเมนูเต็มด้านล่างเหมือนเดิม; สิทธิ์จริงยังคุมด้วย Policy ไม่ได้เปลี่ยน --}}
 @php
 $myHousehold = auth()->user()->household;
 @endphp
 @if (auth()->user()->hasAnyRole(App\Models\Role::OWN_HOUSEHOLD))
 @php
 $farmerGroups = collect([
 ['สวนของฉัน', 'home', 'farmer-garden', array_values(array_filter([
 $myHousehold ? ['households.show', 'households.*,farms.*,plots.*', 'home_work', 'ข้อมูลบ้านและสวนของฉัน'] : null,
 ]))],
 ['บันทึกข้อมูลสวน', 'edit_note', 'farmer-records', [
 ['farm-activities.index', 'farm-activities.*', 'agriculture', 'บันทึกกิจกรรมในสวน'],
 ['harvest-records.index', 'harvest-records.*', 'scale', 'บันทึกผลผลิตที่เก็บเกี่ยว'],
 ['branch-disposal-records.index', 'branch-disposal-records.*', 'park', 'บันทึกกิ่งและเศษไม้'],
 ['product-usages.index', 'product-usages.*', 'recycling', 'บันทึกการใช้ผลิตภัณฑ์ชีวมวล'],
 ]],
 ['ผลลัพธ์ของสวน', 'monitoring', 'farmer-results', [
 ['cfp.index', 'cfp.*', 'eco', 'ผลคาร์บอนของทุเรียน'],
 ]],
 ])->filter(fn ($group) => count($group[3]) > 0);
 @endphp
 @foreach ($farmerGroups as [$groupName, $groupIcon, $groupId, $items])
 <x-nav-group :name="$groupName" :icon="$groupIcon" :id="$groupId" :items="$items" />
 @endforeach
 @else
 @php
 $navGroups = [
 ['ทะเบียนข้อมูล', 'folder_open', 'nav-registry', [
 ['households.index', 'households.*,farms.*,plots.*', 'home_work', 'ระบบครัวเรือน สวน และแปลง', ['App\\Models\\Household']],
 ['crop-seasons.index', 'crop-seasons.*', 'event_repeat', 'ฤดูการผลิต', ['App\\Models\\Household', 'App\\Models\\CropSeason']],
 ['farmer-groups.index', 'farmer-groups.*', 'groups', 'กลุ่มเกษตรกร', ['App\\Models\\FarmerGroup']],
 ['villages.index', 'villages.*', 'location_on', 'หมู่บ้านในประเทศไทย', ['App\\Models\\Village']],
 ]],
 ['ระบบการจัดการสวน', 'agriculture', 'nav-farm', [
 ['farm-activities.index', 'farm-activities.*', 'edit_note', 'บันทึกกิจกรรมในสวน', ['App\\Models\\HouseholdBaseline']],
 ['durian-phenology-records.index', 'durian-phenology-records.*', 'spa', 'ติดตามระยะพัฒนาการทุเรียน', ['App\\Models\\HouseholdBaseline']],
 ['harvest-records.index', 'harvest-records.*', 'scale', 'บันทึกผลผลิตที่เก็บเกี่ยว', ['App\\Models\\HouseholdBaseline']],
 ]],
 ['ระบบคาร์บอนทุเรียน', 'eco', 'nav-carbon', [
 ['cfp.index', 'cfp.*', 'analytics', 'ผลการประเมินคาร์บอน', ['App\\Models\\HouseholdBaseline']],
 ['branch-disposal-records.index', 'branch-disposal-records.*', 'park', 'การจัดการกิ่งและเศษไม้', ['App\\Models\\HouseholdBaseline']],
 ['carbon-activities.index', 'carbon-activities.*', 'co2', 'กิจกรรมลดการปล่อยคาร์บอน', ['App\\Models\\CarbonActivity']],
 ['emission-factors.index', 'emission-factors.*', 'science', 'ค่าสัมประสิทธิ์การปล่อยก๊าซ', ['App\\Models\\CarbonActivity', 'App\\Models\\EmissionFactor']],
 ]],
 ['ระบบชีวมวลและเทคโนโลยี', 'compost', 'nav-biomass', [
 ['technologies.index', 'technologies.*', 'precision_manufacturing', 'ประเภทเทคโนโลยี', ['App\\Models\\Technology']],
 ['technology-assets.index', 'technology-assets.*', 'inventory_2', 'ครุภัณฑ์และเครื่องจักร', ['App\\Models\\Technology']],
 ['kiln-batches.index', 'kiln-batches.*', 'local_fire_department', 'บันทึกการเดินเตา', ['App\\Models\\Technology']],
 ['products.index', 'products.*', 'inventory', 'ผลิตภัณฑ์ชีวมวล', ['App\\Models\\Technology']],
 ['inventory-transactions.index', 'inventory-transactions.*', 'receipt_long', 'บัญชีรับ–จ่ายสต็อก', ['App\\Models\\Technology']],
 ['product-usages.index', 'product-usages.*', 'recycling', 'การใช้ผลิตภัณฑ์ในแปลง', ['App\\Models\\HouseholdBaseline']],
 ]],
 ['ระบบต้นทุน การขาย และตลาด', 'payments', 'nav-market', [
 ['production-costs.index', 'production-costs.*', 'calculate', 'วิเคราะห์ต้นทุนการผลิต', ['App\\Models\\Buyer']],
 ['economic-impacts.index', 'economic-impacts.*', 'trending_up', 'วิเคราะห์ผลกระทบทางเศรษฐกิจ', ['App\\Models\\Buyer']],
 ['sales.index', 'sales.*', 'sell', 'บันทึกยอดขาย', ['App\\Models\\Buyer']],
 ['buyers.index', 'buyers.*', 'storefront', 'ทะเบียนผู้ซื้อ', ['App\\Models\\Buyer']],
 ['market-validations.index', 'market-validations.*', 'query_stats', 'ข้อมูลความต้องการตลาด', ['App\\Models\\Buyer']],
 ]],
 ['ระบบวิจัยและการประเมิน', 'science', 'nav-research', [
 ['household-baselines.index', 'household-baselines.*', 'fact_check', 'ข้อมูลพื้นฐานครัวเรือน', ['App\\Models\\HouseholdBaseline']],
 ['demonstration-comparison-groups.index', 'demonstration-comparison-groups.*', 'balance', 'แปลงสาธิตและชุดเปรียบเทียบ', ['App\\Models\\HouseholdBaseline']],
 ['alp-assessments.index', 'alp-assessments.*', 'psychology', 'ประเมินการยอมรับเทคโนโลยี', ['App\\Models\\HouseholdBaseline']],
 ]],
 ['ระบบนวัตกรชุมชน', 'emoji_events', 'nav-innovator', [
 ['innovator-evaluations.index', 'innovator-evaluations.*', 'military_tech', 'ประเมินนวัตกรชุมชน', ['App\\Models\\Innovator']],
 ['innovators.index', 'innovators.*', 'badge', 'ทะเบียนนวัตกรชุมชน', ['App\\Models\\Innovator']],
 ['competency-assessments.index', 'competency-assessments.*', 'school', 'ประเมินสมรรถนะนวัตกร', ['App\\Models\\Innovator']],
 ['knowledge-transfers.index', 'knowledge-transfers.*', 'record_voice_over', 'บันทึกการถ่ายทอดความรู้', ['App\\Models\\Innovator']],
 ]],
 ['รายงานและส่งออกข้อมูล', 'description', 'nav-reports', [
 ['reports.index', 'reports.*', 'download', 'ศูนย์รายงานและส่งออกข้อมูล', ['App\\Models\\Household']],
 ]],
 ['ตั้งค่าและดูแลระบบ', 'settings', 'nav-admin', [
 ['users.index', 'users.*', 'manage_accounts', 'ผู้ใช้งานและสิทธิ์', ['App\\Models\\User']],
 ['audit-logs.index', 'audit-logs.*', 'history', 'ประวัติการเปลี่ยนแปลงระบบ', ['App\\Models\\AuditLog']],
 ]],
 ];
 @endphp
 @foreach ($navGroups as [$groupName, $groupIcon, $groupId, $groupItems])
 @php
 $visible = collect($groupItems)->filter(function ($item) {
 foreach ($item[4] as $gate) {
 if (! auth()->user()->can('viewAny', $gate)) return false;
 }
 return true;
 })->map(fn ($item) => array_slice($item, 0, 4))->values();
 @endphp
 @if ($visible->isNotEmpty())
 <x-nav-group :name="$groupName" :icon="$groupIcon" :id="$groupId" :items="$visible" />
 @endif
 @endforeach
 @endif
 </ul>
 </div>
 </aside>

 <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
 <nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
 <div class="container-fluid py-1 px-3">
 <nav aria-label="breadcrumb">
 <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
 <li class="breadcrumb-item text-sm"><span class="opacity-5 text-dark">DRFIS</span></li>
 <li class="breadcrumb-item text-sm text-dark active" aria-current="page">{{ $title ?? 'Dashboard' }}</li>
 </ol>
 </nav>
 <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
 <ul class="navbar-nav justify-content-end ms-auto">
 <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
 <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
 <div class="sidenav-toggler-inner">
 <i class="sidenav-toggler-line"></i>
 <i class="sidenav-toggler-line"></i>
 <i class="sidenav-toggler-line"></i>
 </div>
 </a>
 </li>
 <li class="nav-item dropdown pe-2 d-flex align-items-center">
 <a href="javascript:;" class="nav-link text-body font-weight-bold px-0" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
 <i class="material-symbols-rounded me-1">account_circle</i>
 <span class="d-sm-inline d-none">{{ auth()->user()->name }}</span>
 </a>
 <ul class="dropdown-menu dropdown-menu-end px-2 py-3" aria-labelledby="userMenuDropdown">
 <li>
 <form method="POST" action="{{ route('logout') }}">
 @csrf
 <button type="submit" class="dropdown-item border-radius-md">ออกจากระบบ</button>
 </form>
 </li>
 </ul>
 </li>
 </ul>
 </div>
 </div>
 </nav>

 <div class="container-fluid py-2">
 @if (session('status'))
 <div class="alert alert-success text-white" role="alert">{{ session('status') }}</div>
 @endif

 {{-- Evidence EXIF GPS check (Blueprint section 9, 4th bullet) -
 a warning, never a rejection, since mobile GPS can be
 inaccurate - shown separately from the generic 'status'
 flash above so it reads as a caution, not a confirmation. --}}
 @if (session('gps_warning'))
 <div class="alert alert-warning text-dark" role="alert">{{ session('gps_warning') }}</div>
 @endif

 @if (session('farmer_credentials'))
 {{--
 One-time display of an auto-generated Farmer login
 (household registration - HouseholdController::store()
 - or a manual "รีเซ็ตรหัสผ่าน" reset). The plaintext
 password is never stored, so this is the only place it
 is ever shown - tell whoever registered the household
 to copy it down now.
 --}}
 <div class="alert alert-warning text-dark" role="alert">
 <strong>สร้าง/รีเซ็ตบัญชีเกษตรกรแล้ว</strong> — กรุณาบันทึกไว้ทันที (รหัสผ่านนี้จะไม่แสดงซ้ำอีก)<br>
 อีเมล: <strong>{{ session('farmer_credentials')['email'] }}</strong>
 &nbsp;·&nbsp;
 รหัสผ่าน: <strong>{{ session('farmer_credentials')['password'] }}</strong>
 </div>
 @endif

 @unless (request()->routeIs('dashboard', 'dashboards.*'))
 <section class="app-page-visual" aria-label="{{ $title ?? 'ระบบจัดการสวนทุเรียน' }}">
 <img src="{{ asset('images/durian-smart-orchard-hero.webp') }}"
 alt="" width="1600" height="900" loading="lazy">
 <div class="app-page-visual__shade"></div>
 <div class="app-page-visual__content">
 <span><i class="material-symbols-rounded" aria-hidden="true">eco</i> DRFIS · จังหวัดศรีสะเกษ</span>
 <h1>{{ $title ?? 'ระบบจัดการสวนทุเรียน' }}</h1>
 </div>
 </section>
 @endunless

 {{ $slot }}
 </div>
 </main>
 @endauth

 {{--
 The public dashboard (routes/web.php - GET /dashboard has no `auth`
 middleware) is the one page rendered with this layout while logged
 out, so this is the only place a guest ever reaches this branch.
 Every other page still sits behind the `auth` middleware and always
 hits the @auth branch above unchanged - the login page itself is a
 separate standalone document (resources/views/auth/login.blade.php),
 not built on this layout at all.
 --}}
 @guest
 <nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 mt-2 shadow-none border-radius-xl bg-white">
 <div class="container-fluid py-2 px-3 d-flex justify-content-between align-items-center">
 <span class="ms-1 drfis-brand">DRFIS</span>
 <a href="{{ route('login') }}" class="btn btn-sm bg-gradient-success mb-0">เข้าสู่ระบบ</a>
 </div>
 </nav>

 <div class="container-fluid py-2">
 {{ $slot }}
 </div>
 @endguest

 <script src="https://cdn.jsdelivr.net/gh/creativetimofficial/material-dashboard@v3.2.0/assets/js/core/popper.min.js"></script>
 <script src="https://cdn.jsdelivr.net/gh/creativetimofficial/material-dashboard@v3.2.0/assets/js/core/bootstrap.min.js"></script>
 <script src="https://cdn.jsdelivr.net/gh/creativetimofficial/material-dashboard@v3.2.0/assets/js/plugins/perfect-scrollbar.min.js"></script>
 <script src="https://cdn.jsdelivr.net/gh/creativetimofficial/material-dashboard@v3.2.0/assets/js/material-dashboard.min.js"></script>
 {{-- Fixes pre-filled edit-form labels overlapping their value - see the file for why. --}}
 <script src="{{ asset('js/field-fill.js') }}"></script>
 {{-- Forces every date field to display dd/mm/yyyy regardless of browser/OS locale - see the file for why. --}}
 <script src="https://unpkg.com/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
 <script src="{{ asset('js/date-picker.js') }}"></script>
 <script src="{{ asset('vendor/choices/choices.min.js') }}?v={{ filemtime(public_path('vendor/choices/choices.min.js')) }}"></script>
 <script src="{{ asset('js/searchable-select.js') }}?v={{ filemtime(public_path('js/searchable-select.js')) }}"></script>
 @stack('scripts')
 <script>
 if ('serviceWorker' in navigator) {
 window.addEventListener('load', function () {
 navigator.serviceWorker.register('{{ asset('sw.js') }}');
 });
 }
 </script>
</body>
</html>
