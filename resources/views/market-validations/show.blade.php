<x-app-layout :title="'ความต้องการตลาด'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">ความต้องการตลาด</h6>
 </div>
 </div>
 <div class="card-body">
 <div class="row">
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ผู้ซื้อ</p>
 <p class="text-sm mb-0"><a href="{{ route('buyers.show', $validation->buyer) }}">{{ $validation->buyer->name }}</a></p>
 </div>
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ประเภทสินค้า</p>
 <p class="text-sm mb-0">{{ $validation->product_type === 'durian' ? 'ทุเรียน' : 'ผลิตภัณฑ์ชีวมวล' }}</p>
 </div>
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ปริมาณที่ต้องการ</p>
 <p class="text-sm mb-0">{{ $validation->demand_quantity ?? '-' }}</p>
 </div>
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ราคา</p>
 <p class="text-sm mb-0">{{ $validation->price ?? '-' }}</p>
 </div>
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ความถี่</p>
 <p class="text-sm mb-0">{{ $validation->frequency ?? '-' }}</p>
 </div>
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ช่วงที่มีผล</p>
 <p class="text-sm mb-0">{{ $validation->valid_from?->format('d/m/Y') ?? '-' }} - {{ $validation->valid_to?->format('d/m/Y') ?? '-' }}</p>
 </div>
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">LOI/MOU</p>
 <p class="text-sm mb-0"><x-status-badge :status="$validation->has_loi_mou ? 'approved' : 'draft'" /></p>
 </div>
 @if ($validation->notes)
 <div class="col-12">
 <p class="text-xs text-secondary mb-0">หมายเหตุ</p>
 <p class="text-sm mb-0">{{ $validation->notes }}</p>
 </div>
 @endif
 </div>
 </div>
 </div>
 <p><a href="{{ route('buyers.show', $validation->buyer) }}" class="text-secondary text-sm">&larr; กลับไปหน้าผู้ซื้อ</a></p>
 </div>
 </div>
</x-app-layout>
