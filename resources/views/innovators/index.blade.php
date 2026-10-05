<x-app-layout :title="'นวัตกรชุมชน'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">ทะเบียนนวัตกรชุมชน </h6>
 @can('create', App\Models\Innovator::class)
 <a href="{{ route('innovators.create') }}" class="btn btn-sm bg-white text-dark mb-0">+ ลงทะเบียนนวัตกร/แกนนำ (ไม่มีครัวเรือน)</a>
 @endcan
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <p class="text-xs text-secondary px-3 mb-2">
 ครัวเรือนส่วนใหญ่ถูกเพิ่มเข้าทะเบียนนี้อัตโนมัติเมื่อผ่านการประเมินนวัตกรชุมชน (ดูหน้าครัวเรือน)
 — ปุ่มด้านบนไว้สำหรับลงทะเบียนนวัตกร/แกนนำที่ไม่มีครัวเรือนในระบบเท่านั้น
 </p>
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ชื่อ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ครัวเรือน</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ระดับ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ลงทะเบียนเมื่อ</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($innovators as $innovator)
 <tr>
 <td><span class="text-sm font-weight-bold px-2">{{ $innovator->name }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $innovator->household->household_code ?? '— (ไม่มีครัวเรือน)' }}</span></td>
 <td><span class="text-secondary text-xs">{{ $innovator->level ?? '-' }}</span></td>
 <td><span class="text-secondary text-xs">{{ $innovator->registered_at->format('d/m/Y') }}</span></td>
 <td class="align-middle text-end">
 <a href="{{ route('innovators.show', $innovator) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="5" class="text-center text-secondary text-sm py-4">ยังไม่มีนวัตกรในทะเบียน</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
 </div>
 <div class="px-2">{{ $innovators->links() }}</div>
</x-app-layout>
