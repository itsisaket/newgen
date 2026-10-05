<x-app-layout :title="'สต็อกผลิตภัณฑ์'">
 <div class="row">
 <div class="col-12">
 <div class="d-flex justify-content-between align-items-start mb-3">
 <div>
 <h5 class="mb-1">{{ $product->name }}</h5>
 <p class="text-sm text-secondary mb-0">หน่วย: {{ $product->unit }}</p>
 </div>
 </div>

 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-gold shadow-gold border-radius-lg pt-4 pb-3 px-3">
 <h6 class="text-white text-capitalize mb-0">คงเหลือแยกตามครัวเรือน</h6>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ครัวเรือน</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">คงเหลือ ({{ $product->unit }})</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">เคลื่อนไหวล่าสุด</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($balances as $row)
 <tr>
 <td><span class="text-xs font-weight-bold px-2">{{ $row->household->head_name }}</span></td>
 <td class="text-end"><span class="text-sm font-weight-bold">{{ number_format($row->balance_after, 2) }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $row->transaction_date->format('d/m/Y') }}</span></td>
 <td class="align-middle text-end">
 <a href="{{ route('households.show', $row->household) }}" class="text-secondary font-weight-bold text-xs">ดูครัวเรือน</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="4" class="text-center text-secondary text-sm py-4">ยังไม่มีการเคลื่อนไหวสต็อกของผลิตภัณฑ์นี้</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 <p><a href="{{ route('products.index') }}" class="text-secondary text-sm">&larr; กลับไปหน้ารายการ</a></p>
 </div>
 </div>
</x-app-layout>
