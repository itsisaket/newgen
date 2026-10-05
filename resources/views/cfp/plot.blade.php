<x-app-layout :title="'CFP แปลง '.$plot->plot_code">
 <div class="row">
 <div class="col-12">
 @if (session('status'))
 <div class="alert alert-success text-white text-sm mt-3">{{ session('status') }}</div>
 @endif
 @if ($errors->any())
 <div class="alert alert-danger text-white text-sm mt-3">
 @foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach
 </div>
 @endif

 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 px-3 d-flex justify-content-between">
 <h6 class="text-white mb-0">CFP แปลง {{ $plot->plot_code }} — {{ $plot->farm->household->head_name }}</h6>
 <a href="{{ route('cfp.index') }}" class="text-white text-xs">← กลับ</a>
 </div>
 </div>
 <div class="card-body">
 <div class="alert alert-light border text-sm mb-4">
 <strong>ใช้งาน 3 ขั้นตอน</strong>
 <ol class="mb-0 ps-3 text-xs">
 <li><strong>เริ่มรอบ</strong> — วันแรกของรอบ คือวันถัดจากเก็บทุเรียนลูกสุดท้ายของรอบที่แล้ว</li>
 <li><strong>ลงบันทึกตามปกติ</strong> — กิจกรรมสวน ผลผลิตที่เก็บเกี่ยว การจัดการกิ่ง (ต้องได้รับอนุมัติแล้วจึงถูกนำมาคิด)</li>
 <li><strong>จบรอบ แล้วกดคำนวณ</strong> — ระบบบอกว่าทุเรียน 1 กก. ปล่อยก๊าซเรือนกระจกเท่าไร</li>
 </ol>
 </div>
 @if ($pending && array_sum($pending) > 0)
 <div class="alert alert-warning text-white text-sm">
 มีรายการที่ยังไม่อนุมัติในรอบนี้ ซึ่งระบบยังไม่นำมาคิด:
 @foreach ($pending as $label => $n) @if ($n) {{ $label }} {{ $n }} รายการ @endif @endforeach
 — กรุณาส่งให้เจ้าหน้าที่ตรวจและอนุมัติก่อนคำนวณ
 </div>
 @endif
 <h6 class="text-sm">รอบการผลิต</h6>
 <p class="text-xs text-secondary">
 รอบเริ่มวันถัดจากเก็บเกี่ยวผลสุดท้ายของรอบก่อน และปิดที่วันเก็บเกี่ยวผลสุดท้ายจริง (31 ส.ค. เป็นเพียงวันปิดมาตรฐานเบื้องต้น)
 </p>
 <div class="table-responsive">
 <table class="table align-items-center mb-3">
 <thead><tr>
 <th class="text-xxs text-secondary">เริ่ม</th><th class="text-xxs text-secondary">สิ้นสุด</th>
 <th class="text-xxs text-secondary">สถานะ</th><th class="text-xxs text-secondary">เกณฑ์ปิด</th>
 </tr></thead>
 <tbody>
 @forelse ($cycles as $c)
 <tr>
 <td class="text-xs">{{ $c->start_date->format('d/m/Y') }}</td>
 <td class="text-xs">{{ $c->end_date?->format('d/m/Y') ?? '—' }}</td>
 <td class="text-xs">{{ $c->isClosed() ? 'ปิดแล้ว' : 'เปิดอยู่' }}</td>
 <td class="text-xs">{{ $c->close_basis === 'standard_cutoff' ? 'วันปิดมาตรฐาน' : ($c->close_basis ? 'เก็บเกี่ยวสุดท้ายจริง' : '—') }}</td>
 </tr>
 @empty
 <tr><td colspan="4" class="text-xs text-secondary">ยังไม่มีรอบการผลิต</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>

 @if ($canManage)
 @if ($cycles->isEmpty() || ! $open)
 <form method="POST" action="{{ route('cfp.cycles.open', $plot) }}" class="row g-2 align-items-end mb-4">
 @csrf
 <div class="col-md-4">
 <label class="form-label text-xs">วันเริ่มรอบแรก
 @if ($suggest)<span class="text-secondary">(ข้อเสนอ: วันถัดจากเก็บเกี่ยวสุดท้ายฤดู 2568)</span>@endif
 </label>
 <input type="date" name="start_date" value="{{ old('start_date', $suggest) }}" class="form-control border px-2" required>
 </div>
 <div class="col-auto"><button class="btn btn-sm bg-gradient-dark mb-0">เริ่มรอบ</button></div>
 </form>
 @else
 <form method="POST" action="{{ route('cfp.cycles.close', [$plot, $open]) }}" class="row g-2 align-items-end mb-4">
 @csrf
 <div class="col-md-3">
 <label class="form-label text-xs">จบรอบที่เริ่ม {{ $open->start_date->format('d/m/Y') }} — วันที่เก็บทุเรียนลูกสุดท้าย</label>
 <input type="date" name="last_harvest_date" value="{{ old('last_harvest_date', $closeSuggest) }}" class="form-control border px-2" required>
 </div>
 <div class="col-md-3">
 <label class="form-label text-xs">นับวันจบจาก</label>
 <select name="close_basis" class="form-control border px-2">
 <option value="actual_last_harvest">วันเก็บลูกสุดท้ายจริง (แนะนำ)</option>
 <option value="standard_cutoff">วันมาตรฐาน 31 ส.ค.</option>
 </select>
 </div>
 <div class="col-auto"><button class="btn btn-sm bg-gradient-dark mb-0" onclick="return confirm('จบรอบนี้และเริ่มรอบถัดไป?')">จบรอบ</button></div>
 </form>
 @endif

 <h6 class="text-sm">คำนวณค่าคาร์บอนฟุตพริ้นท์</h6>
 <form method="POST" action="{{ route('cfp.calculate', $plot) }}" class="row g-2 align-items-end mb-4">
 @csrf
 <div class="col-md-4">
 <label class="form-label text-xs">รอบการผลิต</label>
 <select name="cycle_id" class="form-control border px-2">
 <option value="">— ระบุช่วงวันที่เอง —</option>
 @foreach ($cycles as $c)
 <option value="{{ $c->id }}">{{ $c->start_date->format('d/m/Y') }} → {{ $c->end_date?->format('d/m/Y') ?? 'ปัจจุบัน' }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-3"><label class="form-label text-xs">หรือ วันเริ่ม</label><input type="date" name="start_date" class="form-control border px-2"></div>
 <div class="col-md-3"><label class="form-label text-xs">วันสิ้นสุด</label><input type="date" name="end_date" class="form-control border px-2"></div>
 <div class="col-auto"><button class="btn btn-sm bg-gradient-success mb-0">คำนวณและบันทึก</button></div>
 </form>
 @endif

 <h6 class="text-sm">ผลการคำนวณ</h6>
 <div class="table-responsive">
 <table class="table align-items-center mb-0">
 <thead><tr>
 <th class="text-xxs text-secondary">ช่วงเวลา</th>
 <th class="text-xxs text-secondary text-end">ผลผลิต (กก.)</th>
 <th class="text-xxs text-secondary text-end">ค่าที่ได้ (kgCO2e ต่อ กก.)</th>
 <th class="text-xxs text-secondary text-end">ความครบถ้วนข้อมูล</th>
 <th class="text-xxs text-secondary">สถานะ</th><th></th>
 </tr></thead>
 <tbody>
 @forelse ($calcs as $k)
 <tr>
 <td class="text-xs">{{ $k->period_start->format('d/m/Y') }} → {{ $k->period_end->format('d/m/Y') }}</td>
 <td class="text-xs text-end">{{ number_format($k->harvest_kg, 2) }}</td>
 <td class="text-xs text-end">{{ $k->cfp_per_kg !== null ? number_format($k->cfp_per_kg, 4) : '—' }}</td>
 <td class="text-xs text-end">{{ $k->data_quality_score }}/100</td>
 <td class="text-xs">{{ $k->status }}</td>
 <td class="text-end"><a href="{{ route('cfp.show', $k) }}" class="text-xs font-weight-bold text-secondary">รายละเอียด</a></td>
 </tr>
 @empty
 <tr><td colspan="6" class="text-xs text-secondary">ยังไม่มีผลคำนวณ</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
