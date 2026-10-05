<x-app-layout :title="'ผู้ใช้งานและสิทธิ์'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">ผู้ใช้งานและสิทธิ์ (RBAC)</h6>
 <a href="{{ route('users.create') }}" class="btn btn-sm bg-gradient-success mb-0">+ เพิ่มผู้ใช้</a>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ชื่อ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">อีเมล</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">บทบาท (Role)</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">สถานะ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">เข้าสู่ระบบล่าสุด</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($users as $user)
 <tr>
 <td>
 <div class="d-flex px-2 py-1">
 <div class="d-flex flex-column justify-content-center">
 <h6 class="mb-0 text-sm">{{ $user->name }}</h6>
 </div>
 </div>
 </td>
 <td class="ps-2">
 <span class="text-secondary text-xs">{{ $user->email }}</span>
 </td>
 <td class="ps-2">
 @if ($user->primaryRole)
 <span class="badge badge-sm bg-gradient-secondary">{{ $user->primaryRole->name }}</span>
 @else
 <span class="text-secondary text-xs">ยังไม่กำหนด</span>
 @endif
 </td>
 <td class="align-middle text-center text-sm">
 @if ($user->status === 'active')
 <span class="badge badge-sm bg-gradient-success">ใช้งานอยู่</span>
 @else
 <span class="badge badge-sm bg-gradient-secondary">ระงับการใช้งาน</span>
 @endif
 </td>
 <td class="ps-2">
 <span class="text-secondary text-xs font-weight-bold">{{ $user->last_login_at?->format('d/m/Y H:i') ?? '-' }}</span>
 </td>
 <td class="align-middle text-end">
 <a href="{{ route('users.edit', $user) }}" class="text-secondary font-weight-bold text-xs">แก้ไข</a>
 </td>
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

 <div class="px-2">
 {{ $users->links() }}
 </div>
</x-app-layout>
