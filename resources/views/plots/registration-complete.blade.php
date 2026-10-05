<x-app-layout :title="'ลงทะเบียนเสร็จสิ้น'">
 <div class="row">
 <div class="col-12">
 <x-wizard-steps :current="4" />

 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-success shadow-success border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">
 <i class="material-symbols-rounded align-middle me-1">check_circle</i>
 ลงทะเบียนครัวเรือน / สวน / แปลง เรียบร้อยแล้ว
 </h6>
 </div>
 </div>
 <div class="card-body">
 <p class="text-sm text-secondary">
 แปลง <strong>{{ $plot->plot_code }}</strong>
 ของสวน <strong>{{ $plot->farm->farm_name ?? $plot->farm->farm_code }}</strong>
 (ครัวเรือน {{ $plot->farm->household->head_name }})
 พร้อมใช้งานแล้ว ขั้นตอนต่อไป: เลือกเครื่องมือวิจัยที่ต้องการบันทึกข้อมูลสำหรับแปลงนี้
 </p>

 <div class="row g-3 mt-2">
 <div class="col-md-6">
 <div class="card border h-100">
 <div class="card-body d-flex flex-column">
 <div class="d-flex align-items-center mb-2">
 <i class="material-symbols-rounded text-success me-2">assignment</i>
 <h6 class="mb-0">ข้อมูลพื้นฐานครัวเรือน</h6>
 </div>
 <p class="text-xs text-secondary flex-grow-1">
 บันทึกข้อมูลพื้นฐานของครัวเรือน (ต้นทุน/รายได้/แนวทางจัดการเดิม) สำหรับฤดูผลิตนี้
 </p>
 <a href="{{ route('household-baselines.create', ['household_id' => $plot->farm->household_id]) }}"
 class="btn bg-gradient-success btn-sm mb-0">ไปบันทึก Baseline</a>
 </div>
 </div>
 </div>
 <div class="col-md-6">
 <div class="card border h-100">
 <div class="card-body d-flex flex-column">
 <div class="d-flex align-items-center mb-2">
 <i class="material-symbols-rounded text-gold me-2">agriculture</i>
 <h6 class="mb-0">กิจกรรมสวน</h6>
 </div>
 <p class="text-xs text-secondary flex-grow-1">
 บันทึกกิจกรรมดูแลสวน (ใส่ปุ๋ย พ่นยา ตัดแต่งกิ่ง เก็บเกี่ยว ฯลฯ) ของแปลงนี้
 </p>
 <a href="{{ route('farm-activities.create', ['plot_id' => $plot->id]) }}"
 class="btn bg-gradient-gold btn-sm mb-0">ไปบันทึก กิจกรรม</a>
 </div>
 </div>
 </div>
 </div>

 <hr class="horizontal dark my-4">

 <div class="d-flex flex-wrap gap-3">
 <a href="{{ route('plots.create', ['farm_id' => $plot->farm_id]) }}" class="text-secondary text-sm">
 <i class="material-symbols-rounded align-middle text-sm">add</i> เพิ่มแปลงอีกแปลงในสวนนี้
 </a>
 <a href="{{ route('farms.create', ['household_id' => $plot->farm->household_id]) }}" class="text-secondary text-sm">
 <i class="material-symbols-rounded align-middle text-sm">add</i> เพิ่มสวนอีกแห่งของครัวเรือนนี้
 </a>
 <a href="{{ route('households.create') }}" class="text-secondary text-sm">
 <i class="material-symbols-rounded align-middle text-sm">add</i> ลงทะเบียนครัวเรือนใหม่อีกราย
 </a>
 </div>

 <p class="mt-4 mb-0">
 <a href="{{ route('plots.show', $plot) }}" class="text-secondary text-sm">ดูรายละเอียดแปลงนี้</a>
 ·
 <a href="{{ route('plots.index') }}" class="text-secondary text-sm">กลับไปหน้ารายการแปลงทั้งหมด</a>
 </p>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
