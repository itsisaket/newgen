<x-app-layout :title="'ประวัติการเปลี่ยนแปลงระบบ'">
<div class="card">
 <div class="card-header pb-0">
 <h5>ประวัติการเปลี่ยนแปลงระบบ (Audit Trail)</h5>
 <p class="text-sm text-secondary mb-0">แสดงผู้ดำเนินการ เวลา รายการ และค่าก่อน–หลัง เพื่อใช้ตรวจสอบย้อนหลัง</p>
 </div>
 <div class="card-body">
 <form method="GET" class="row g-2 mb-3">
 <div class="col-md-4">
 <select name="action" class="form-select">
 <option value="">ทุกการดำเนินการ</option>
 @foreach ($actions as $action)
 <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-3">
 <input type="number" min="1" name="user_id" class="form-control" value="{{ request('user_id') }}" placeholder="รหัสผู้ใช้">
 </div>
 <div class="col-md-3">
 <button class="btn btn-dark mb-0">กรองข้อมูล</button>
 <a href="{{ route('audit-logs.index') }}" class="btn btn-outline-secondary mb-0">ล้างตัวกรอง</a>
 </div>
 </form>

 <div class="table-responsive">
 <table class="table align-items-center mb-0">
 <thead><tr><th>เวลา</th><th>ผู้ดำเนินการ</th><th>รายการ</th><th>ระเบียน</th><th>ค่าก่อน–หลัง</th><th>IP</th></tr></thead>
 <tbody>
 @forelse ($logs as $log)
 <tr>
 <td class="text-sm text-nowrap">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
 <td class="text-sm">{{ $log->user?->name ?? 'ระบบ' }}<br><small class="text-secondary">{{ $log->user?->email }}</small></td>
 <td><span class="badge bg-gradient-info">{{ $log->action }}</span></td>
 <td class="text-sm"><code>{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</code></td>
 <td class="text-sm" style="min-width:320px">
 @if ($log->old_values)<div><strong>ก่อน:</strong> <code>{{ json_encode($log->old_values, JSON_UNESCAPED_UNICODE) }}</code></div>@endif
 @if ($log->new_values)<div><strong>หลัง:</strong> <code>{{ json_encode($log->new_values, JSON_UNESCAPED_UNICODE) }}</code></div>@endif
 </td>
 <td class="text-sm">{{ $log->ip_address ?: '-' }}</td>
 </tr>
 @empty
 <tr><td colspan="6" class="text-center text-secondary py-4">ยังไม่มีประวัติ</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 <div class="mt-3">{{ $logs->links() }}</div>
 </div>
</div>
</x-app-layout>
