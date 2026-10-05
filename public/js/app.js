/**
 * NOTE: this file is currently served directly as public/js/app.js (no
 * Vite build step yet, matching Sprint 1's CDN-Bootstrap approach) - if
 * you edit this file, copy it over public/js/app.js too, or wire up
 * `@vite(['resources/js/app.js'])` and drop the public copy once a build
 * step is set up.
 *
 * Offline draft-save and queued sync for the F13 field form.
 *
 * Scope of this MVP: if the device loses signal or the tab/browser closes
 * before a field officer finishes a form, the in-progress values and the
 * record's client_uuid survive in localStorage and are restored next time
 * the same form is opened - so nothing typed is lost and re-syncing later
 * won't create a duplicate record (Blueprint requires every offline-created
 * record to carry a client-generated UUID).
 *
 * Multiple pending records are stored locally and posted automatically
 * when connectivity returns. client_uuid makes retries idempotent.
 */
(function () {
    var form = document.getElementById('farm-activity-form');
    if (!form) return;

    var DRAFT_KEY = 'drfis_draft_farm_activity_new';
    var QUEUE_KEY = 'drfis_queue_farm_activities';
    var uuidField = document.getElementById('client_uuid');
    var indicator = document.getElementById('offline-indicator');

    function hasStorage() {
        try {
            var t = '__drfis_test__';
            window.localStorage.setItem(t, '1');
            window.localStorage.removeItem(t);
            return true;
        } catch (e) {
            return false;
        }
    }

    function generateUuid() {
        if (window.crypto && window.crypto.randomUUID) {
            return window.crypto.randomUUID();
        }
        // RFC4122-ish fallback for older browsers/webviews.
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = (Math.random() * 16) | 0;
            var v = c === 'x' ? r : (r & 0x3) | 0x8;
            return v.toString(16);
        });
    }

    function updateOnlineIndicator() {
        if (!indicator) return;
        indicator.textContent = navigator.onLine
            ? ''
            : '⚠ ออฟไลน์อยู่ตอนนี้ — ข้อมูลที่กรอกจะถูกเก็บไว้ในเครื่องจนกว่าจะกดบันทึกสำเร็จ (ต้องมีสัญญาณตอนกดบันทึก)';
    }

    var storageOk = hasStorage();

    // A `?plot_id=` query param means we just arrived here from the
    // household->farm->plot->tools auto-chaining registration flow (or the
    // "+ บันทึกกิจกรรม" shortcut on a plot's page) with a specific plot
    // pre-selected server-side. That is a stronger signal than whatever
    // stale draft might be sitting in localStorage from an unrelated
    // earlier visit, so treat it as "start fresh" rather than letting an
    // old saved plot_id silently override the pre-selected one.
    var arrivedWithPlotPrefill = /[?&]plot_id=/.test(window.location.search);

    // Restore a previous draft, or start a fresh client_uuid for this record.
    if (storageOk) {
        var saved = null;
        if (!arrivedWithPlotPrefill) {
            try {
                saved = JSON.parse(window.localStorage.getItem(DRAFT_KEY) || 'null');
            } catch (e) {
                saved = null;
            }
        }

        if (saved && saved.client_uuid) {
            uuidField.value = saved.client_uuid;
            Object.keys(saved.fields || {}).forEach(function (name) {
                var el = form.querySelector('[name="' + name + '"]');
                if (el) {
                    el.value = saved.fields[name];
                    if (el.tagName === 'SELECT') {
                        el.dispatchEvent(new CustomEvent('drfis:select-options-updated', { bubbles: true }));
                    }
                }
            });
        } else if (!uuidField.value) {
            uuidField.value = generateUuid();
        }

        var saveDraft = debounce(function () {
            var fields = {};
            form.querySelectorAll('[data-draft]').forEach(function (el) {
                fields[el.name] = el.value;
            });
            window.localStorage.setItem(DRAFT_KEY, JSON.stringify({
                client_uuid: uuidField.value,
                fields: fields,
                saved_at: new Date().toISOString(),
            }));
        }, 400);

        form.querySelectorAll('[data-draft]').forEach(function (el) {
            el.addEventListener('input', saveDraft);
            el.addEventListener('change', saveDraft);
        });

        form.addEventListener('submit', function (event) {
            if (navigator.onLine) {
                window.localStorage.removeItem(DRAFT_KEY);
                return;
            }

            event.preventDefault();
            var fields = {};
            new FormData(form).forEach(function (value, name) { fields[name] = value; });
            var queue = readQueue();
            queue.push({ action: form.action, fields: fields, queued_at: new Date().toISOString() });
            window.localStorage.setItem(QUEUE_KEY, JSON.stringify(queue));
            window.localStorage.removeItem(DRAFT_KEY);
            indicator.textContent = 'บันทึกไว้ในคิวออฟไลน์แล้ว ระบบจะส่งให้อัตโนมัติเมื่อกลับมาออนไลน์';
        });
    } else if (!uuidField.value) {
        uuidField.value = generateUuid();
    }

    window.addEventListener('online', function () {
        updateOnlineIndicator();
        flushQueue();
    });
    window.addEventListener('offline', updateOnlineIndicator);
    updateOnlineIndicator();
    if (navigator.onLine) flushQueue();

    function readQueue() {
        try {
            return JSON.parse(window.localStorage.getItem(QUEUE_KEY) || '[]');
        } catch (e) {
            return [];
        }
    }

    async function flushQueue() {
        if (!storageOk || !navigator.onLine) return;
        var queue = readQueue();
        if (!queue.length) return;

        var remaining = [];
        var csrfToken = document.querySelector('meta[name="csrf-token"]');
        for (var i = 0; i < queue.length; i++) {
            try {
                var fields = Object.assign({}, queue[i].fields);
                if (csrfToken) fields._token = csrfToken.content;
                var response = await fetch(queue[i].action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                    },
                    body: new URLSearchParams(fields).toString()
                });
                if (!response.ok) remaining.push(queue[i]);
            } catch (e) {
                remaining = remaining.concat(queue.slice(i));
                break;
            }
        }

        window.localStorage.setItem(QUEUE_KEY, JSON.stringify(remaining));
        if (indicator && queue.length !== remaining.length) {
            indicator.textContent = remaining.length
                ? 'ส่งข้อมูลจากคิวได้บางส่วน ยังเหลือ ' + remaining.length + ' รายการ'
                : 'ส่งข้อมูลจากคิวออฟไลน์เรียบร้อยแล้ว';
        }
    }

    function debounce(fn, wait) {
        var t;
        return function () {
            clearTimeout(t);
            var args = arguments;
            t = setTimeout(function () { fn.apply(null, args); }, wait);
        };
    }
})();
