<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Tambon;
use App\Models\Village;
use Illuminate\Http\Request;

/**
 * JSON endpoints backing the จังหวัด -> อำเภอ -> ตำบล cascading selects
 * (public/js/cascading-location.js) on the household/farm create+edit
 * forms. Now that LocationSeeder installs the full official dataset
 * (77 provinces / 930 districts / 7,452 tambons) instead of the old
 * 3-province pilot subset, rendering every district/tambon as an inline
 * <option> on every page load (as the pilot-data version of these forms
 * used to) would mean shipping thousands of hidden options down to field
 * officers who may be on a slow mobile connection - these two lightweight
 * endpoints are fetched on demand instead, one province/district at a
 * time.
 */
class LocationController extends Controller
{
    public function districts(Request $request)
    {
        $provinceId = $request->integer('province_id');

        if (! $provinceId) {
            return response()->json([]);
        }

        return District::where('province_id', $provinceId)
            ->orderBy('name_th')
            ->get(['id', 'name_th']);
    }

    public function tambons(Request $request)
    {
        $districtId = $request->integer('district_id');

        if (! $districtId) {
            return response()->json([]);
        }

        return Tambon::where('district_id', $districtId)
            ->orderBy('name_th')
            ->get(['id', 'name_th', 'zip_code']);
    }

    public function villages(Request $request)
    {
        $tambonId = $request->integer('tambon_id');

        if (! $tambonId) {
            return response()->json([]);
        }

        return Village::where('tambon_id', $tambonId)
            ->where('is_active', true)
            ->orderByRaw('village_no IS NULL, village_no')
            ->orderBy('name_th')
            ->get()
            ->map(fn (Village $village) => [
                'id' => $village->id,
                'name_th' => $village->display_name,
            ]);
    }
}
