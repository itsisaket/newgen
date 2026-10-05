<!DOCTYPE html>
<html lang="th">
<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
 <title>เข้าสู่ระบบ - DRFIS</title>
 <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap">
 <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0">
 <link href="https://cdn.jsdelivr.net/gh/creativetimofficial/material-dashboard@v3.2.0/assets/css/material-dashboard.min.css" rel="stylesheet">
 <link rel="stylesheet" href="{{ asset('css/drfis-theme.css') }}?v={{ filemtime(public_path('css/drfis-theme.css')) }}">
</head>
<body class="bg-gray-200">
 <main class="main-content mt-0">
 <div class="page-header align-items-start min-vh-100 durian-login-hero">
 <span class="mask durian-login-mask"></span>
 <div class="container my-auto">
 <div class="row">
 <div class="col-lg-4 col-md-8 col-12 mx-auto">
 <div class="card z-index-0 fadeIn3 fadeInBottom durian-login-card">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-gold shadow-gold border-radius-lg py-3 pe-1">
 <div class="text-center mb-1"><span class="durian-login-kicker">ทุเรียนภูเขาไฟศรีสะเกษ</span></div>
 <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">DRFIS</h4>
 <p class="text-white text-center mb-2 opacity-8 text-xs px-3">
 ระบบบริหารข้อมูลวิจัยและการจัดการสวนทุเรียนอัจฉริยะ
 </p>
 </div>
 </div>
 <div class="card-body">
 <p class="text-center text-sm text-secondary mb-1">จัดการสวน · ติดตามผลผลิต · ลดคาร์บอน</p>
 @if ($errors->any())
 <div class="alert alert-danger text-white">
 <ul class="mb-0 ps-3">
 @foreach ($errors->all() as $error)
 <li>{{ $error }}</li>
 @endforeach
 </ul>
 </div>
 @endif

 <form method="POST" action="{{ route('login') }}" role="form" class="text-start" novalidate>
 @csrf
 <div class="input-group input-group-outline my-3">
 <label class="form-label">อีเมล</label>
 <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
 </div>
 <div class="input-group input-group-outline mb-3">
 <label class="form-label">รหัสผ่าน</label>
 <input type="password" name="password" class="form-control" required>
 </div>
 <div class="form-check form-switch d-flex align-items-center mb-3">
 <input class="form-check-input" type="checkbox" name="remember" id="remember">
 <label class="form-check-label mb-0 ms-3" for="remember">จดจำฉัน</label>
 </div>
 <div class="text-center">
 <button type="submit" class="btn bg-gradient-success w-100 my-4 mb-2">เข้าสู่ระบบ</button>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
 </main>

 <script src="https://cdn.jsdelivr.net/gh/creativetimofficial/material-dashboard@v3.2.0/assets/js/core/popper.min.js"></script>
 <script src="https://cdn.jsdelivr.net/gh/creativetimofficial/material-dashboard@v3.2.0/assets/js/core/bootstrap.min.js"></script>
 {{-- Required whenever a page has .main-content: material-dashboard.min.js
 unconditionally calls `new PerfectScrollbar(...)` on Windows if this
 class is present, and throws a ReferenceError without this file. --}}
 <script src="https://cdn.jsdelivr.net/gh/creativetimofficial/material-dashboard@v3.2.0/assets/js/plugins/perfect-scrollbar.min.js"></script>
 <script src="https://cdn.jsdelivr.net/gh/creativetimofficial/material-dashboard@v3.2.0/assets/js/material-dashboard.min.js"></script>
 {{-- Fixes the email field's label overlapping its value after a failed
 login attempt refills it via old('email') - see the file for why. --}}
 <script src="{{ asset('js/field-fill.js') }}"></script>
</body>
</html>
