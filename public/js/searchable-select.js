/**
 * Enhances every normal <select> with Choices.js search while keeping the
 * original element as the submitted form control. Cascading location fields
 * dispatch `drfis:select-options-updated` whenever AJAX replaces their
 * options; the matching Choices instance is then refreshed in-place.
 */
(function () {
    function enhance(select) {
        if (!select || select.dataset.noSearch === 'true' || select._drfisChoices || typeof Choices === 'undefined') {
            return;
        }

        select._drfisChoices = new Choices(select, {
            allowHTML: false,
            shouldSort: false,
            searchEnabled: true,
            searchFloor: 0,
            searchResultLimit: 100,
            searchPlaceholderValue: 'พิมพ์เพื่อค้นหา...',
            noResultsText: 'ไม่พบรายการที่ค้นหา',
            noChoicesText: 'ไม่มีรายการให้เลือก',
            itemSelectText: '',
            placeholder: true,
            position: 'auto',
        });

        var fieldContainer = select.closest('.col-12, [class*="col-"], .form-group, form');
        var label = fieldContainer ? fieldContainer.querySelector('label') : null;
        if (label && select._drfisChoices.input && select._drfisChoices.input.element) {
            select._drfisChoices.input.element.setAttribute(
                'aria-label',
                'ค้นหา'.concat(label.textContent.trim())
            );
        }

        if (select.classList.contains('form-control-sm') || select.dataset.compact === 'true') {
            select._drfisChoices.containerOuter.element.classList.add('drfis-choices-compact');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('select').forEach(enhance);
    });

    document.addEventListener('drfis:select-options-updated', function (event) {
        var select = event.target;
        if (!(select instanceof HTMLSelectElement)) return;

        if (!select._drfisChoices) {
            enhance(select);
            return;
        }

        select._drfisChoices.refresh(false, false);
    });

    document.addEventListener('drfis:enhance-selects', function (event) {
        var root = event.target instanceof Element ? event.target : document;
        if (root instanceof HTMLSelectElement) enhance(root);
        root.querySelectorAll('select').forEach(enhance);
    });

    document.addEventListener('reset', function (event) {
        window.setTimeout(function () {
            event.target.querySelectorAll('select').forEach(function (select) {
                if (select._drfisChoices) select._drfisChoices.refresh(false, false);
            });
        }, 0);
    }, true);

    new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            var select = mutation.target;
            if (!(select instanceof HTMLSelectElement) || !select._drfisChoices) return;
            var instance = select._drfisChoices;
            if (select.disabled && !instance.containerOuter.isDisabled) instance.disable();
            else if (!select.disabled && instance.containerOuter.isDisabled) instance.enable();
        });
    }).observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['disabled'],
        subtree: true,
    });
})();
