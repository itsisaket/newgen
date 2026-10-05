/**
 * Two-way GPS map picker (Leaflet) for a lat/lng <input> pair. Click the
 * map or drag the marker to update the inputs; typing coordinates by hand
 * still works and moves the marker to match (see the `change` listener
 * below) - the map is an alternative way to fill the same two fields, not
 * a replacement for them.
 */
(function () {
    function markFilled(el) {
        // Material Dashboard's floating label only reacts to real
        // keyup/focusout events (see public/js/field-fill.js for the same
        // issue on page load) - a value set purely via JS needs the
        // `.is-filled` class added by hand so the label floats correctly.
        var group = el.closest('.input-group');
        if (group) group.classList.add('is-filled');
    }

    function initGpsPicker(config) {
        var mapEl = document.getElementById(config.mapId);
        var latEl = document.getElementById(config.latFieldId);
        var lngEl = document.getElementById(config.lngFieldId);

        if (!mapEl || !latEl || !lngEl || typeof L === 'undefined') return;

        var hasInitial = latEl.value !== '' && lngEl.value !== '';
        var initialLat = hasInitial ? parseFloat(latEl.value) : config.defaultLat;
        var initialLng = hasInitial ? parseFloat(lngEl.value) : config.defaultLng;

        var map = L.map(mapEl).setView([initialLat, initialLng], hasInitial ? 15 : (config.defaultZoom || 7));

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(map);

        var marker = null;

        function placeMarker(lat, lng) {
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                marker.on('dragend', function () {
                    var pos = marker.getLatLng();
                    writeFields(pos.lat, pos.lng);
                });
            }
        }

        function writeFields(lat, lng) {
            var roundedLat = Math.round(lat * 1e7) / 1e7;
            var roundedLng = Math.round(lng * 1e7) / 1e7;
            latEl.value = roundedLat;
            lngEl.value = roundedLng;
            markFilled(latEl);
            markFilled(lngEl);
        }

        if (hasInitial) {
            placeMarker(initialLat, initialLng);
        }

        map.on('click', function (e) {
            placeMarker(e.latlng.lat, e.latlng.lng);
            writeFields(e.latlng.lat, e.latlng.lng);
        });

        [latEl, lngEl].forEach(function (el) {
            el.addEventListener('change', function () {
                var lat = parseFloat(latEl.value);
                var lng = parseFloat(lngEl.value);
                if (isNaN(lat) || isNaN(lng)) return;
                placeMarker(lat, lng);
                map.setView([lat, lng], Math.max(map.getZoom(), 15));
            });
        });

        // A map initialised while its container is briefly hidden/mid-layout
        // (e.g. inside a card that's still animating in) can size itself
        // wrong; nudge it once after the page settles.
        setTimeout(function () {
            map.invalidateSize();
        }, 200);
    }

    window.drfisInitGpsPicker = initGpsPicker;
})();
