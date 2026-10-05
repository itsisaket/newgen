<x-app-layout :title="'รายการขาย'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">รายการขาย</h6>
 </div>
 </div>
 <div class="card-body">
 <div class="row">
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ครัวเรือน</p>
 <p class="text-sm mb-0"><a href="{{ route('households.show', $sale->sellerHousehold) }}">{{ $sale->sellerHousehold->head_name }}</a></p>
 </div>
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ผู้ซื้อ</p>
 <p class="text-sm mb-0">{{ $sale->buyer->name ?? '-' }}</p>
 </div>
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ฤดูผลิต</p>
 <p class="text-sm mb-0">{{ $sale->cropSeason->name }}</p>
 </div>
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ประเภทสินค้า</p>
 <p class="text-sm mb-0">{{ $sale->product_type === 'durian' ? 'ทุเรียน' : ($sale->product->name ?? '-') }}</p>
 </div>
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ปริมาณ</p>
 <p class="text-sm mb-0">{{ number_format($sale->quantity, 2) }}</p>
 </div>
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ราคาต่อหน่วย</p>
 <p class="text-sm mb-0">{{ number_format($sale->unit_price, 2) }} บาท</p>
 </div>
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">มูลค่ารวม</p>
 <p class="text-sm font-weight-bold mb-0">{{ number_format($sale->total_amount, 2) }} บาท</p>
 </div>
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">วันที่ขาย</p>
 <p class="text-sm mb-0">{{ $sale->sale_date->format('d/m/Y') }}</p>
 </div>
 @if ($sale->notes)
 <div class="col-12">
 <p class="text-xs text-secondary mb-0">หมายเหตุ</p>
 <p class="text-sm mb-0">{{ $sale->notes }}</p>
 </div>
 @endif
 </div>
 </div>
 </div>
 <p><a href="{{ route('households.show', $sale->sellerHousehold) }}" class="text-secondary text-sm">&larr; กลับไปหน้าครัวเรือน</a></p>
 </div>
 </div>
</x-app-layout>
