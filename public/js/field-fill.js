/**
 * NOTE: no Vite build wired up yet (same reasoning as public/js/app.js) —
 * this file is served directly from public/js/.
 *
 * Fixes a gap in Material Dashboard 2's own floating-label script
 * (material-dashboard.min.js, window.onload handler): it only adds the
 * "is-filled" class to an .input-group-outline field when the user types
 * into it (onkeyup) or leaves it (focusout) — it never checks a field's
 * value when the page first loads. So on every EDIT form (name, email,
 * phone, etc. already have a value from the database) the label sits on
 * top of the pre-filled value until the user clicks into that exact field
 * once. On CREATE forms this never shows up because every field starts
 * empty, which is why it wasn't caught earlier.
 *
 * This runs once, right after the page loads, and marks every already-
 * filled .input-group-outline field the same way Material Dashboard's own
 * script would after a blur — so labels start in the correct floated
 * position instead of only fixing themselves after a click.
 */
(function () {
    document.querySelectorAll('.input-group.input-group-outline .form-control').forEach(function (el) {
        if (el.value !== '') {
            el.parentElement.classList.add('is-filled');
        }
    });
})();
