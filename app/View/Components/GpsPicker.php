<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Leaflet.js (CDN, unpkg, v1.9.4 - deliberately without SRI hashes, same
 * as every other CDN tag in this project, since an unverifiable/possibly
 * wrong hash silently breaking the map is worse than no hash at all) map
 * for picking gps_lat/gps_lng by clicking or dragging a marker, two-way
 * synced with the existing number inputs so typing coordinates by hand
 * still works exactly as before.
 *
 * $latField/$lngField must match the `id` (and usually `name`) of the
 * existing gps_lat/gps_lng <input> elements already on the page - this
 * component does not render those inputs itself, only the map that
 * controls them.
 */
class GpsPicker extends Component
{
    public function __construct(
        public string $latField = 'gps_lat',
        public string $lngField = 'gps_lng',
    ) {
    }

    public function render(): View
    {
        return view('components.gps-picker');
    }
}
