<x-app-layout :title="'การใช้ผลิตภัณฑ์'">
 <div class="row">
 <div class="col-12">
 <h5 class="mb-1">การใช้ผลิตภัณฑ์ชีวมวล</h5>
 <p class="text-sm text-secondary mb-3">
 {{ $usage->plot->plot_code }} · {{ $usage->plot->farm->household->head_name }} · {{ $usage->application_date->format('d/m/Y') }}
 </p>
 <div class="card mb-3">
 <div class="card-body">
 <div class="row">
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ผลิตภัณฑ์</p>
 <p class="text-sm mb-0">{{ $usage->inventoryTransaction->product->name }}</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ปริมาณที่ใช้</p>
 <p class="text-sm mb-0">{{ number_format($usage->inventoryTransaction->quantity, 2) }} {{ $usage->inventoryTransaction->product->unit }}</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">อัตราการใช้</p>
 <p class="text-sm mb-0">{{ $usage->usage_rate ?? '-' }}</p>
 </div>
 <div class="col-md-3 col-6 mb-0">
 <p class="text-xs text-secondary mb-0">วิธีการใช้</p>
 <p class="text-sm mb-0">{{ $usage->application_method ?? '-' }}</p>
 </div>
 </div>
 </div>
 </div>
 <p><a href="{{ route('product-usages.index') }}" class="text-secondary text-sm">&larr; กลับไปหน้ารายการ</a></p>
 </div>
 </div>
</x-app-layout>
