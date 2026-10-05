<?php

namespace App\Http\Requests\Concerns;

use App\Models\District;
use App\Models\Tambon;
use Illuminate\Validation\Validator;

/**
 * Blueprint Appendix C.2 `farms` - province_id/district_id/tambon_id are
 * each validated independently with `exists:` only, which lets a
 * crafted (or simply out-of-sync, e.g. JS-disabled) request through with
 * a real district_id that doesn't actually belong to the submitted
 * province_id, or a tambon_id under a different district than the one
 * submitted. This trait adds the missing parent-child consistency check
 * on top of `exists:`, shared by StoreFarmRequest/UpdateFarmRequest (both
 * use the same cascading province/district/tambon selects - see
 * public/js/cascading-location.js) so the two stay in sync with each
 * other instead of drifting.
 */
trait ValidatesLocationHierarchy
{
    protected function validateLocationHierarchy(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $provinceId = $this->input('province_id');
            $districtId = $this->input('district_id');
            $tambonId = $this->input('tambon_id');

            // A child selected without its immediate parent means the
            // cascading select was bypassed rather than a legitimately
            // partial address - provinces alone (no district/tambon) is
            // fine and stays allowed.
            if ($districtId && ! $provinceId) {
                $validator->errors()->add('district_id', 'กรุณาเลือกจังหวัดก่อนเลือกอำเภอ/เขต');
            }

            if ($tambonId && ! $districtId) {
                $validator->errors()->add('tambon_id', 'กรุณาเลือกอำเภอ/เขตก่อนเลือกตำบล/แขวง');
            }

            if ($districtId && $provinceId) {
                $district = District::find($districtId);

                if ($district && (int) $district->province_id !== (int) $provinceId) {
                    $validator->errors()->add('district_id', 'อำเภอ/เขตที่เลือกไม่ได้อยู่ในจังหวัดที่เลือก');
                }
            }

            if ($tambonId && $districtId) {
                $tambon = Tambon::find($tambonId);

                if ($tambon && (int) $tambon->district_id !== (int) $districtId) {
                    $validator->errors()->add('tambon_id', 'ตำบล/แขวงที่เลือกไม่ได้อยู่ในอำเภอ/เขตที่เลือก');
                }
            }
        });
    }
}
