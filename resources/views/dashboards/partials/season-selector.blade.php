{{-- Season filter - the one filter row that scopes every KPI/chart/table
 below it (see dashboards/partials/header.blade.php). --}}
<form method="GET" class="dash-filter">
 <label for="dash_crop_season">ฤดูผลิต</label>
 <select id="dash_crop_season" name="crop_season_id" class="field-control" onchange="this.form.submit()">
 @foreach ($seasons as $s)
 <option value="{{ $s->id }}" @selected($season && $season->id === $s->id)>{{ $s->name }}</option>
 @endforeach
 </select>
 <noscript><button type="submit" class="btn btn-sm btn-outline-secondary mb-0">แสดง</button></noscript>
</form>
