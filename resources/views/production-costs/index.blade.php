<x-app-layout :title="'ต้นทุนการผลิต — ภาพรวม'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3 flex-wrap gap-2">
 <h6 class="text-white text-capitalize mb-0">ต้นทุนการผลิต (ทุกครัวเรือน)</h6>
 <form method="GET" action="{{ route('production-costs.index') }}" class="d-flex align-items-center gap-2">
 <select name="crop_season_id" class="form-control form-control-sm" onchange="this.form.submit()" style="width:auto;">
 <option value="">รวมทุกฤดูผลิต</option>
 @foreach ($seasons as $option)
 <option value="{{ $option->id }}" @selected($season?->id === $option->id)>{{ $option->name }}</option>
 @endforeach
 </select>
 </form>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <p class="text-xs text-secondary px-3">
 คำนวณสดจากข้อมูล (กิจกรรมสวน) ทุกครั้งที่เปิดหน้านี้ ไม่ใช่ตัวเลขที่บันทึกไว้ล่วงหน้า
 — กดที่ครัวเรือนเพื่อดูรายละเอียดแยกตามหมวด/แปลง
 </p>
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ครัวเรือน</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ต้นทุนรวม (บาท)</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ต้นทุน/ไร่</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ต้นทุน/ต้น</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ต้นทุน/กก.</th>
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
 <td class="text-end text-sm font-weight-bold">{{ number_format($row['summary']['total_cost'], 2) }}</td>
 <td class="text-end text-sm">{{ $row['summary']['cost_per_rai'] !== null ? number_format($row['summary']['cost_per_rai'], 2) : '-' }}</td>
 <td class="text-end text-sm">{{ $row['summary']['cost_per_tree'] !== null ? number_format($row['summary']['cost_per_tree'], 2) : '-' }}</td>
 <td class="text-end text-sm">{{ $row['summary']['cost_per_kg'] !== null ? number_format($row['summary']['cost_per_kg'], 2) : '-' }}</td>
 <td class="align-middle text-end">
 <a href="{{ route('households.production-cost', ['household' => $row['household'], 'crop_season_id' => $season?->id]) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="6" class="text-center text-secondary text-sm py-4">ไม่พบครัวเรือนในเขตที่รับผิดชอบ</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
 </div>
 <div class="px-2">{{ $households->links() }}</div>
</x-app-layout>
