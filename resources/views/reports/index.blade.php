<x-app-layout :title="'รายงานและส่งออกข้อมูล'">
 <div class="card">
 <div class="card-body">
 <h5>รายงานและส่งออกข้อมูลวิจัย</h5>
 <p class="text-sm text-secondary">ข้อมูลถูกจำกัดตามพื้นที่รับผิดชอบ และไม่รวมชื่อ เบอร์โทร หรือเลขประจำตัวประชาชน</p>
 <div class="d-flex gap-2 flex-wrap">
 <a class="btn btn-outline-success" href="{{ route('reports.exports.csv') }}">CSV</a>
 <a class="btn btn-outline-success" href="{{ route('reports.exports.xlsx') }}">Excel</a>
 <a class="btn btn-outline-danger" href="{{ route('reports.exports.pdf') }}">PDF</a>
 </div>
 </div>
 </div>
</x-app-layout>
