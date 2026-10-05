<x-app-layout :title="'ผลกระทบเศรษฐกิจ — ภาพรวม'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3 flex-wrap gap-2">
 <h6 class="text-white text-capitalize mb-0">ผลกระทบเศรษฐกิจ (ทุกครัวเรือน)</h6>
 <form method="GET" action="{{ route('economic-impacts.index') }}" class="d-flex align-items-center gap-2">
 <select name="crop_season_id" class="form-control form-control-sm" onchange="this.form.submit()" style="width:auto;">
 <option value="">-- เลือกฤดูผลิต --</option>
 @foreach ($seasons as $option)
 <option value="{{ $option->id }}" @selected($season?->id === $option->id)>{{ $option->name }}</option>
 @endforeach
 </select>
 </form>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 @if (! $season)
 <p class="text-sm text-secondary px-3 py-4 mb-0">กรุณาเลือกฤดูผลิตด้านบนเพื่อดูผลการคำนวณ (ไม่มีฤดูผลิตที่กำลัง "active" อยู่ในขณะนี้)</p>
 @else
 <p class="text-xs text-secondary px-3">
 คำนวณสดโดย EconomicImpactService ทุกครั้งที่เปิดหน้านี้ (และบันทึกผลลง economic_impacts
 ของแต่ละครัวเรือนไปด้วย) — เป้าหมาย Net Benefit {{ number_format($targetBaht, 0) }} บาท/ครัวเรือน/ปี
 </p>
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ครัวเรือน</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Cost Saving</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Durian Income+</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Bioproduct Income</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Net Benefit</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">เป้าหมาย</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($rows as $row)
 <tr>
 <td>
 <h6 class="mb-0 text-sm px-2 py-1">{{ $row['household']->head_name }}</h6>
 <span class="text-secondary text-xs px-2">{{ $row['household']->household_code }}</span>
 </td>
 <td class="text-end text-sm">{{ number_format($row['impact']->cost_saving, 2) }}</td>
 <td class="text-end text-sm">{{ number_format($row['impact']->durian_income_increase, 2) }}</td>
 <td class="text-end text-sm">{{ number_format($row['impact']->bioproduct_income, 2) }}</td>
 <td class="text-end text-sm font-weight-bold">{{ number_format($row['impact']->net_benefit, 2) }}</td>
 <td>
 @if ($row['impact']->target_status === 'achieved')
 <span class="badge badge-sm bg-gradient-success">บรรลุ</span>
 @else
 <span class="badge badge-sm bg-gradient-secondary">ยังไม่ถึง</span>
 @endif
 </td>
 <td class="align-middle text-end">
 <a href="{{ route('households.economic-impact', ['household' => $row['household'], 'crop_season_id' => $season->id]) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="7" class="text-center text-secondary text-sm py-4">ไม่พบครัวเรือนในเขตที่รับผิดชอบ</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 @endif
 </div>
 </div>
 </div>
 </div>
 @if ($season)
 <div class="px-2">{{ $households->links() }}</div>
 @endif
</x-app-layout>
