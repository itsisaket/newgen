/**
 * Generic จังหวัด -> อำเภอ -> ตำบล (province -> district -> tambon)
 * cascading <select> filter, plus optional further selects filtered by
 * the chosen tambon (e.g. กลุ่มเกษตรกร/หมู่บ้าน on the household form).
 *
 * AJAX-driven (routes locations.districts / locations.tambons -
 * App\Http\Controllers\LocationController) - only the province <select>
 * is rendered with every <option> up front (77 rows, negligible). Once
 * LocationSeeder started installing the full official Thailand dataset
 * (930 districts / 7,452 tambons) instead of the old 3-province pilot
 * subset, inlining every district/tambon as a hidden <option> on every
 * page load (the original approach here, back when the total dataset was
 * a handful of rows) would have meant shipping several hundred KB of
 * mostly-irrelevant options to field officers who may be on a slow
 * mobile connection - so district/tambon options are now fetched one
 * province/district at a time instead.
 *
 * The district/tambon <select> elements start with only their
 * placeholder <option> already in the markup (see households/farms
 * create/edit blade views) - options are populated here after each
 * fetch. A `data-selected` attribute (set server-side from old()/the
 * model) carries the value to restore once its option list arrives,
 * since that <option> doesn't exist in the DOM until then.
 */
(function () {
    function notifyOptionsUpdated(select) {
        select.dispatchEvent(new CustomEvent('drfis:select-options-updated', { bubbles: true }));
    }

    function filterOptions(select, parentValue, dataAttr) {
        if (!select) return;

        var hasVisibleSelection = false;

        Array.prototype.forEach.call(select.options, function (opt) {
            if (!opt.value) {
                // Always keep the "-- เลือก / ไม่ระบุ --" placeholder visible.
                opt.hidden = false;
                return;
            }

            // No parent chosen yet -> show everything unfiltered. This
            // matters most on first load of a create form (nothing
            // selected yet) and keeps an old()-restored value visible even
            // though its own province/district/tambon wasn't re-selected.
            var parentId = opt.getAttribute(dataAttr);
            var show = !parentValue || parentId === String(parentValue);
            opt.hidden = !show;
            opt.disabled = !show;

            if (show && opt.selected) {
                hasVisibleSelection = true;
            }
        });

        if (!hasVisibleSelection) {
            select.value = '';
        }

        notifyOptionsUpdated(select);
    }

    /**
     * Replaces every non-placeholder <option> in `select` with one per
     * item in `items` ({id, name_th[, zip_code]}). The first existing
     * <option> (the "-- เลือก / ไม่ระบุ --" placeholder, value="") is kept.
     */
    function populateOptions(select, items, opts) {
        opts = opts || {};

        while (select.options.length > 1) {
            select.remove(1);
        }

        items.forEach(function (item) {
            var opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.name_th + (opts.withZip && item.zip_code ? ' (' + item.zip_code + ')' : '');
            select.appendChild(opt);
        });

        notifyOptionsUpdated(select);
    }

    function fetchJson(url) {
        return fetch(url, { headers: { Accept: 'application/json' } })
            .then(function (res) { return res.ok ? res.json() : []; })
            .catch(function () { return []; });
    }

    /**
     * @param {Object} config
     * @param {string} config.province - id of the province <select>
     * @param {string} config.district - id of the district <select>
     * @param {string} config.tambon - id of the tambon <select>
     * @param {string} config.districtsUrl - route('locations.districts')
     * @param {string} config.tambonsUrl - route('locations.tambons')
     * @param {string[]} [config.tambonDependents] - ids of further
     *   <select> elements whose <option>s carry data-tambon-id and should
     *   be filtered once a tambon is chosen (e.g. farmer_group_id/village_id).
     */
    function initLocationCascade(config) {
        var provinceEl = document.getElementById(config.province);
        var districtEl = document.getElementById(config.district);
        var tambonEl = document.getElementById(config.tambon);
        var villageEl = config.village ? document.getElementById(config.village) : null;
        var dependents = (config.tambonDependents || [])
            .map(function (id) { return document.getElementById(id); })
            .filter(Boolean);

        if (!provinceEl || !districtEl || !tambonEl) return;

        // Read once, up front - the district's/tambon's own <option> for
        // these values won't exist until after their fetch resolves, so
        // they can't be represented as a normal pre-selected <option> the
        // way the province <select> (rendered whole, server-side) still is.
        var initialDistrictId = districtEl.getAttribute('data-selected') || '';
        var initialTambonId = tambonEl.getAttribute('data-selected') || '';
        var initialVillageId = villageEl ? (villageEl.getAttribute('data-selected') || '') : '';

        function loadVillages(tambonId, selectValue) {
            if (!villageEl || !config.villagesUrl) return;

            populateOptions(villageEl, []);
            if (!tambonId) return;

            fetchJson(config.villagesUrl + '?tambon_id=' + encodeURIComponent(tambonId)).then(function (items) {
                populateOptions(villageEl, items);
                if (selectValue) villageEl.value = selectValue;
                notifyOptionsUpdated(villageEl);
            });
        }

        function applyDependents() {
            dependents.forEach(function (el) {
                filterOptions(el, tambonEl.value, 'data-tambon-id');
            });
        }

        function loadTambons(districtId, selectValue) {
            populateOptions(tambonEl, []);
            if (!districtId) {
                applyDependents();
                return;
            }

            fetchJson(config.tambonsUrl + '?district_id=' + encodeURIComponent(districtId)).then(function (items) {
                populateOptions(tambonEl, items, { withZip: true });
                if (selectValue) tambonEl.value = selectValue;
                notifyOptionsUpdated(tambonEl);
                applyDependents();
                loadVillages(tambonEl.value, initialVillageId);
            });
        }

        function loadDistricts(provinceId, selectValue, tambonSelectValue) {
            populateOptions(districtEl, []);
            populateOptions(tambonEl, []);
            if (!provinceId) {
                applyDependents();
                return;
            }

            fetchJson(config.districtsUrl + '?province_id=' + encodeURIComponent(provinceId)).then(function (items) {
                populateOptions(districtEl, items);
                if (selectValue) districtEl.value = selectValue;
                notifyOptionsUpdated(districtEl);
                loadTambons(districtEl.value, tambonSelectValue);
            });
        }

        provinceEl.addEventListener('change', function () {
            loadDistricts(provinceEl.value);
        });
        districtEl.addEventListener('change', function () {
            loadTambons(districtEl.value);
        });
        tambonEl.addEventListener('change', function () {
            applyDependents();
            loadVillages(tambonEl.value);
        });

        // Initial hydrate: covers an edit form (province pre-selected
        // server-side, district/tambon restored from data-selected) and a
        // create form re-displayed after a validation error (old()
        // carried the same way) - a fresh create form has no initial
        // province value, so this is a no-op and every select stays at
        // its blank placeholder, same as before.
        if (provinceEl.value) {
            loadDistricts(provinceEl.value, initialDistrictId, initialTambonId);
        }
    }

    window.drfisInitLocationCascade = initLocationCascade;
})();
