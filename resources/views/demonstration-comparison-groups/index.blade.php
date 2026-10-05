<x-app-layout :title="'แปลงสาธิต'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">ชุดเปรียบเทียบแปลงสาธิต (Demonstration Plot)</h6>
 @can('create', App\Models\DemonstrationComparisonGroup::class)
 <a href="{{ route('demonstration-comparison-groups.create') }}" class="btn btn-sm bg-white text-dark mb-0">+ สร้างชุดเปรียบเทียบ</a>
 @endcan
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <p class="text-xs text-secondary px-3 mb-2">
 เพิ่มแปลงเข้าร่วมชุดเปรียบเทียบได้จากหน้ารายละเอียดของแปลงนั้น ๆ (ปุ่ม "+ เพิ่มเข้าแปลงสาธิต")
 </p>
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ชื่อชุดเปรียบเทียบ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ฤดูกาล</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">เทคโนโลยี</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">จำนวนแปลง</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">สถานะ</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($groups as $group)
 <tr>
 <td><span class="text-xs font-weight-bold px-2">{{ $group->name }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $group->cropSeason->name }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $group->technology->name ?? '-' }}</span></td>
 <td class="text-center"><span class="text-sm">{{ $group->demonstration_plots_count }}</span></td>
 <td class="align-middle text-center text-sm"><x-status-badge :status="$group->status" /></td>
 <td class="align-middle text-end">
 <a href="{{ route('demonstration-comparison-groups.show', $group) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="6" class="text-center text-secondary text-sm py-4">ยังไม่มีชุดเปรียบเทียบ</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
 </div>
 <div class="px-2">{{ $groups->links() }}</div>
</x-app-layout>
