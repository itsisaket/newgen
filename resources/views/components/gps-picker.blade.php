@php
 $mapId = $latField . '_map';
@endphp
<div class="col-12">
 <label class="field-label">เลือกตำแหน่ง GPS จากแผนที่ (คลิกบนแผนที่ หรือลากหมุดเพื่อปรับตำแหน่ง)</label>
 <div id="{{ $mapId }}" class="border" style="height: 280px; border-radius: 0.5rem;"></div>
</div>

@once
 @push('scripts')
 <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
 <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
 <script src="{{ asset('js/gps-picker.js') }}"></script>
 @endpush
@endonce

@push('scripts')
 <script>
 document.addEventListener('DOMContentLoaded', function () {
 if (typeof drfisInitGpsPicker !== 'function') return;
 drfisInitGpsPicker({
 mapId: '{{ $mapId }}',
 latFieldId: '{{ $latField }}',
 lngFieldId: '{{ $lngField }}',
 // Centred roughly between the pilot provinces
 // (จันทบุรี/ระยอง/ชุมพร - LocationSeeder) when no
 // coordinates are set yet.
 defaultLat: 12.9,
 defaultLng: 101.5,
 defaultZoom: 7,
 });
 });
 </script>
@endpush
