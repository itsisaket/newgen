<x-app-layout :title="'บัญชีสต็อก'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">บัญชีเคลื่อนไหวสต็อกผลิตภัณฑ์ชีวมวล (Inventory Ledger)</h6>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <p class="text-xs text-secondary px-3 mb-2">
 บันทึกการเคลื่อนไหวแบบ Sale/Transfer/Loss/Adjustment ใหม่ได้จากหน้ารายละเอียดครัวเรือน
 — Production มาจากการอนุมัติ และ Farm Use มาจากการบันทึก โดยอัตโนมัติ
 </p>
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">วันที่</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ครัวเรือน</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ผลิตภัณฑ์</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ประเภท</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ปริมาณ</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">คงเหลือหลังรายการ</th>
 </tr>
 </thead>
 <tbody>
 @php
 $typeLabels = ['production' => 'ผลิต', 'farm_use' => 'ใช้ในสวน', 'sale' => 'ขาย', 'transfer' => 'โอนย้าย', 'loss' => 'สูญเสีย', 'adjustment' => 'ปรับยอด'];
 @endphp
 @forelse ($transactions as $t)
 <tr>
 <td><span class="text-secondary text-xs font-weight-bold px-2">{{ $t->transaction_date->format('d/m/Y') }}</span></td>
 <td class="ps-2"><span class="text-xs">{{ $t->household->head_name }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $t->product->name }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $typeLabels[$t->transaction_type] ?? $t->transaction_type }}</span></td>
 <td class="text-end"><span class="text-secondary text-xs font-weight-bold">{{ number_format($t->quantity, 2) }}</span></td>
 <td class="text-end"><span class="text-sm font-weight-bold">{{ number_format($t->balance_after, 2) }}</span></td>
 </tr>
 @empty
 <tr><td colspan="6" class="text-center text-secondary text-sm py-4">ยังไม่มีข้อมูล</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
 </div>
 <div class="px-2">{{ $transactions->links() }}</div>
</x-app-layout>
