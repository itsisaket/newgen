<x-app-layout :title="'การเดินเตา'">
 <div class="row">
 <div class="col-12">
 <div class="d-flex justify-content-between align-items-start mb-3">
 <div>
 <h5 class="mb-1">{{ $batch->batch_code }}</h5>
 <p class="text-sm text-secondary mb-0">
 {{ $batch->household->head_name }} · {{ $batch->technologyAsset->asset_code }} · {{ $batch->batch_date->format('d/m/Y') }}
 </p>
 </div>
 <x-status-badge :status="$batch->status" />
 <x-revision-button :record="$batch" type="kiln-batch" />
 </div>

 <div class="card mb-3">
 <div class="card-body">
 <div class="row">
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">วัตถุดิบเข้า</p>
 <p class="text-sm mb-0">{{ number_format($batch->biomass_input_kg, 2) }} กก.</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">เวลาที่ใช้ผลิต</p>
 <p class="text-sm mb-0">{{ $batch->production_time_hours ?? '-' }} ชม.</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ต้นทุนรวม (แรงงาน+พลังงาน+อื่นๆ)</p>
 <p class="text-sm font-weight-bold mb-0">{{ number_format($batch->totalCost(), 2) }} บาท</p>
 </div>
 <div class="col-md-3 col-6 mb-0">
 <p class="text-xs text-secondary mb-0">ผู้ควบคุมเตา / ผู้บันทึก</p>
 <p class="text-sm mb-0">{{ $batch->operator->name }} / {{ $batch->recordedBy->name }}</p>
 </div>
 @if ($batch->rejection_reason)
 <div class="col-12 mt-3">
 <p class="text-xs text-danger mb-0">เหตุผลตีกลับล่าสุด</p>
 <p class="text-sm text-danger mb-0">{{ $batch->rejection_reason }}</p>
 </div>
 @endif
 </div>
 </div>
 </div>

 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-gold shadow-gold border-radius-lg pt-4 pb-3 px-3">
 <h6 class="text-white text-capitalize mb-0">ผลผลิตจาก Batch นี้</h6>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ผลิตภัณฑ์</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ปริมาณ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">เกรด</th>
 </tr>
 </thead>
 <tbody>
 @foreach ($batch->outputs as $output)
 <tr>
 <td><span class="text-xs font-weight-bold px-2">{{ $output->product->name }}</span></td>
 <td class="text-end"><span class="text-sm">{{ number_format($output->output_quantity, 2) }} {{ $output->unit }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $output->quality_grade ?? '-' }}</span></td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 @if ($batch->status !== 'approved')
 <p class="text-xs text-secondary px-3 pt-2 mb-0">
 ผลผลิตด้านบนจะยังไม่ถูกบันทึกเข้าสต็อก (Inventory Ledger) จนกว่าจะ "อนุมัติ" ข้อมูลนี้ก่อน
 </p>
 @endif
 </div>
 </div>

 <div class="d-flex gap-2 flex-wrap mb-3">
 @if ($batch->status === 'draft')
 <form method="POST" action="{{ route('kiln-batches.submit', $batch) }}">
 @csrf
 <button class="btn bg-gradient-info btn-sm mb-0">ส่งข้อมูล (Submit)</button>
 </form>
 @endif
 @if ($batch->status === 'submitted')
 <form method="POST" action="{{ route('kiln-batches.verify', $batch) }}">
 @csrf
 <button class="btn bg-gradient-info btn-sm mb-0">ตรวจสอบ (Verify)</button>
 </form>
 @endif
 @if ($batch->status === 'verified')
 <form method="POST" action="{{ route('kiln-batches.approve', $batch) }}">
 @csrf
 <button class="btn bg-gradient-success btn-sm mb-0">อนุมัติ (บันทึกเข้าสต็อก)</button>
 </form>
 @endif
 @if (in_array($batch->status, ['submitted', 'verified']))
 <button type="button" class="btn btn-outline-danger btn-sm mb-0" data-bs-toggle="collapse" data-bs-target="#rejectForm">ตีกลับ (Reject)</button>
 @endif
 </div>

 @if (in_array($batch->status, ['submitted', 'verified']))
 <div class="collapse mb-3" id="rejectForm">
 <div class="card col-lg-6 col-12">
 <div class="card-body">
 <form method="POST" action="{{ route('kiln-batches.reject', $batch) }}">
 @csrf
 <label class="field-label">เหตุผลที่ตีกลับ</label>
 <textarea name="rejection_reason" class="field-control mb-2" required></textarea>
 <button class="btn bg-gradient-danger btn-sm mb-0">ยืนยันตีกลับ</button>
 </form>
 </div>
 </div>
 </div>
 @endif

 <div class="card mb-3">
 <div class="card-body">
 <h6 class="text-sm mb-2">หลักฐาน</h6>
 @forelse ($batch->evidences as $evidence)
 <p class="text-xs mb-1 d-flex align-items-center gap-2">
 @if ($evidence->thumbnail_path)
 <img src="{{ route('evidences.download', ['evidence' => $evidence, 'thumbnail' => 1]) }}" alt="" style="width:32px;height:32px;object-fit:cover;border-radius:4px;">
 @endif
 <a href="{{ route('evidences.download', $evidence) }}" target="_blank">{{ basename($evidence->file_path) }}</a>
 </p>
 @empty
 <p class="text-secondary text-xs mb-2">ยังไม่มีหลักฐาน</p>
 @endforelse
 <form method="POST" action="{{ route('evidences.store') }}" enctype="multipart/form-data" class="mt-2 row g-2 align-items-end">
 @csrf
 <input type="hidden" name="evidenceable_type" value="{{ \App\Models\KilnBatch::class }}">
 <input type="hidden" name="evidenceable_id" value="{{ $batch->id }}">
 <div class="col-sm-8 col-12">
 <input type="file" name="file" class="field-control" required accept="image/*,.pdf">
 </div>
 <div class="col-sm-4 col-12">
 <button class="btn btn-outline-secondary mb-0 w-100">อัปโหลด</button>
 </div>
 </form>
 </div>
 </div>

 <p><a href="{{ route('kiln-batches.index') }}" class="text-secondary text-sm">&larr; กลับไปหน้ารายการ</a></p>
 </div>
 </div>
</x-app-layout>
