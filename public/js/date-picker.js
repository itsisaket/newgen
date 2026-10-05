/**
 * Forces every <input type="date"> in the app to always display as
 * dd/mm/yyyy, regardless of the browser/OS locale.
 *
 * Root cause of "the date field doesn't show as dd/mm/yyyy": a native
 * <input type="date"> always renders its VISIBLE text using whatever date
 * format the browser/OS locale dictates - the underlying `value`
 * attribute must always be yyyy-mm-dd (HTML5 spec), but that is not what
 * the user sees. On a machine set to an English/US locale, Chrome shows
 * "09/15/2026" (mm/dd/yyyy) for the exact same value a Thai-locale
 * machine would show as "15/09/2026" - nothing in Blade/Laravel can
 * change that, because it's the browser doing the rendering, not
 * anything the server sends. Every date already IS stored and *displayed*
 * (on show/index pages) correctly - see the ->format('d/m/Y') calls
 * throughout resources/views/**\/show.blade.php and index.blade.php -
 * it was only ever the native <input type="date"> widgets on the
 * create/edit forms that were locale-dependent.
 *
 * Fix: flatpickr (CDN, pinned - same unpkg.com pattern as
 * public/js/gps-picker.js's Leaflet include) replaces the native picker
 * with one that always shows `altFormat: "d/m/Y"` in a visible text
 * input, while the real <input type="date"> it's attached to keeps
 * submitting `dateFormat: "Y-m-d"` (ISO, unambiguous) under its original
 * `name` - so nothing on the backend (validation rules, model date
 * casts) needs to change, and offline draft-save (public/js/app.js,
 * which listens on the original [data-draft] element) keeps working
 * unchanged since flatpickr dispatches real change/input events on it.
 */
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof flatpickr === 'undefined') return;

        document.querySelectorAll('input[type="date"]').forEach(function (el) {
            var fp = flatpickr(el, {
                altInput: true,
                altFormat: 'd/m/Y',
                dateFormat: 'Y-m-d',
                allowInput: true,
                altInputClass: el.className,
            });

            // flatpickr's altInput doesn't inherit the `required` attribute
            // from the original (now visually hidden) <input> - without
            // this, the browser's "กรุณากรอกข้อมูล" native validation
            // message would silently stop appearing on this field.
            if (el.hasAttribute('required') && fp.altInput) {
                fp.altInput.setAttribute('required', 'required');
            }
        });
    });
})();
