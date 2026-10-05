/**
 * DRFIS dashboard charts (23 ก.ย. round).
 *
 * Views never write chart JavaScript. A chart is declared in Blade as
 *   <x-dash.chart title="..." :chart="[...spec...]" />
 * which renders <canvas data-chart="{json spec}">; this file finds every
 * such canvas and draws it with Chart.js (loaded just before this file by
 * resources/views/dashboards/partials/header.blade.php).
 *
 * Spec (all optional except labels/values):
 *   labels      string[]   category labels
 *   values      number[]   one value per label (single series)
 *   horizontal  bool       horizontal bars (use for long Thai labels)
 *   unit        string     appended in tooltip/labels, e.g. "ไร่"
 *   decimals    int        number format precision (default 0)
 *   max         number     fixed value-axis max (e.g. 100 for %)
 *   label       "max"|"all"|"none"  which bars get a value at the tip
 *                          (default "max" - label selectively, the axis,
 *                          tooltip and table view carry the rest)
 *   empty       string     message when there is no data
 *   table       {head: string[], rows: (string|number)[][]}  richer
 *                          table-view twin; defaults to label/value pairs
 *
 * Marks follow the dataviz kit: bars <= 24px thick with a 4px rounded
 * data-end (square at the baseline), hairline solid grid, colours from
 * the --viz-* CSS tokens in public/css/drfis-dashboard.css. Every chart
 * has a table-view twin (the "ตาราง" button) so no value is hover-only.
 * All label text goes into the DOM via textContent, never innerHTML.
 */
(function () {
    'use strict';

    var root = getComputedStyle(document.documentElement);
    function token(name, fallback) {
        var value = root.getPropertyValue(name).trim();
        return value || fallback;
    }

    var C = {
        series: token('--viz-series-1', '#2e7d32'),
        seriesHover: token('--viz-series-1-hover', '#43964a'),
        ink: token('--viz-ink', '#344767'),
        ink2: token('--viz-ink-2', '#67748e'),
        muted: token('--viz-muted', '#8a8f98'),
        grid: token('--viz-grid', '#eceae4'),
        axis: token('--viz-axis', '#c3c2b7'),
    };

    var formatters = {};
    function fmt(decimals) {
        if (!formatters[decimals]) {
            formatters[decimals] = new Intl.NumberFormat('th-TH', {
                minimumFractionDigits: 0,
                maximumFractionDigits: decimals,
            });
        }
        return formatters[decimals];
    }
    var compact = new Intl.NumberFormat('th-TH', { notation: 'compact', maximumFractionDigits: 1 });

    function withUnit(text, unit) {
        return unit ? text + ' ' + unit : text;
    }

    function parseSpec(canvas) {
        try {
            var spec = JSON.parse(canvas.getAttribute('data-chart') || '{}');
            spec.labels = (spec.labels || []).map(String);
            spec.values = (spec.values || []).map(function (v) {
                var n = Number(v);
                return isFinite(n) ? n : 0;
            });
            spec.decimals = spec.decimals == null ? 0 : spec.decimals;
            spec.label = spec.label || 'max';
            return spec;
        } catch (e) {
            return null;
        }
    }

    function isEmpty(spec) {
        return !spec || spec.labels.length === 0 || spec.values.every(function (v) { return v === 0; });
    }

    // Value at the tip of selected bars (never inside - a label that does
    // not fit its mark is never clipped; the tip always has room because
    // the layout padding below reserves it).
    var tipLabels = {
        id: 'drfisTipLabels',
        afterDatasetsDraw: function (chart, args, opts) {
            if (!opts || opts.mode === 'none') return;
            var meta = chart.getDatasetMeta(0);
            var values = chart.data.datasets[0].data;
            if (!meta || !values.length) return;

            var maxIndex = 0;
            values.forEach(function (v, i) { if (v > values[maxIndex]) maxIndex = i; });

            var ctx = chart.ctx;
            ctx.save();
            ctx.font = '600 12px Kanit, system-ui, sans-serif';
            ctx.fillStyle = C.ink;
            meta.data.forEach(function (bar, i) {
                if (opts.mode === 'max' && i !== maxIndex) return;
                if (values[i] === 0) return;
                var text = withUnit(opts.format(values[i]), opts.mode === 'max' ? opts.unit : '');
                if (opts.horizontal) {
                    ctx.textAlign = 'left';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(text, bar.x + 6, bar.y);
                } else {
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.fillText(text, bar.x, bar.y - 5);
                }
            });
            ctx.restore();
        },
    };

    // Width of the widest tip label, so it is never clipped by the card.
    var measureCtx = document.createElement('canvas').getContext('2d');
    function tipRoom(spec, numberFormat) {
        if (spec.label === 'none') return 8;
        measureCtx.font = '600 12px Kanit, system-ui, sans-serif';
        var widest = 0;
        var maxValue = Math.max.apply(null, spec.values);
        spec.values.forEach(function (v) {
            if (spec.label === 'max' && v !== maxValue) return;
            var text = withUnit(numberFormat.format(v), spec.label === 'max' ? spec.unit : '');
            widest = Math.max(widest, measureCtx.measureText(text).width);
        });
        return Math.ceil(widest) + 14;
    }

    function buildConfig(spec) {
        var horizontal = !!spec.horizontal;
        var numberFormat = fmt(spec.decimals);
        var valueAxis = horizontal ? 'x' : 'y';
        var categoryAxis = horizontal ? 'y' : 'x';

        var scales = {};
        scales[valueAxis] = {
            beginAtZero: true,
            max: spec.max,
            grid: { color: C.grid, lineWidth: 1, drawTicks: false },
            border: { display: false },
            ticks: {
                color: C.muted,
                padding: 6,
                maxTicksLimit: 5,
                precision: spec.decimals,
                callback: function (value) {
                    return Math.abs(value) >= 10000 ? compact.format(value) : fmt(spec.decimals).format(value);
                },
            },
        };
        scales[categoryAxis] = {
            grid: { display: false },
            border: { color: C.axis, width: 1 },
            ticks: {
                color: C.ink2,
                padding: 6,
                autoSkip: !horizontal,
                maxRotation: 0,
                font: { size: 12 },
            },
        };

        return {
            type: 'bar',
            data: {
                labels: spec.labels,
                datasets: [{
                    data: spec.values,
                    backgroundColor: C.series,
                    hoverBackgroundColor: C.seriesHover,
                    borderRadius: 4,
                    borderSkipped: 'start',
                    maxBarThickness: 24,
                    categoryPercentage: 0.72,
                    barPercentage: 0.9,
                }],
            },
            plugins: [tipLabels],
            options: {
                indexAxis: horizontal ? 'y' : 'x',
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 350 },
                layout: { padding: horizontal ? { right: tipRoom(spec, numberFormat) } : { top: spec.label === 'none' ? 4 : 22 } },
                interaction: { mode: 'nearest', axis: horizontal ? 'y' : 'x', intersect: false },
                plugins: {
                    legend: { display: false },
                    drfisTipLabels: {
                        mode: spec.label,
                        horizontal: horizontal,
                        unit: spec.unit || '',
                        format: function (v) { return numberFormat.format(v); },
                    },
                    tooltip: {
                        backgroundColor: '#1f2a37',
                        padding: 10,
                        cornerRadius: 6,
                        displayColors: false,
                        titleColor: '#c3c2b7',
                        titleFont: { weight: '400', size: 12, family: 'Kanit, system-ui, sans-serif' },
                        bodyColor: '#ffffff',
                        bodyFont: { weight: '600', size: 14, family: 'Kanit, system-ui, sans-serif' },
                        callbacks: {
                            label: function (context) {
                                return withUnit(numberFormat.format(context.parsed[valueAxis]), spec.unit);
                            },
                        },
                    },
                },
                scales: scales,
            },
        };
    }

    // Size the container to include the axis band, so the card never gets
    // a nested scrollbar: horizontal charts grow with the number of rows.
    function containerHeight(spec) {
        if (spec.height) return spec.height;
        if (spec.horizontal) return Math.max(150, spec.labels.length * 36 + 36);
        return 260;
    }

    function renderEmpty(wrap, message) {
        var box = document.createElement('div');
        box.className = 'viz-empty';
        box.textContent = message || 'ยังไม่มีข้อมูลในช่วงนี้';
        wrap.replaceChildren(box);
    }

    function buildTable(spec) {
        var table = document.createElement('table');
        table.className = 'viz-table';
        var head = spec.table && spec.table.head ? spec.table.head : [spec.categoryLabel || 'รายการ', withUnit('ค่า', spec.unit ? '(' + spec.unit + ')' : '')];
        var rows = spec.table && spec.table.rows
            ? spec.table.rows
            : spec.labels.map(function (label, i) { return [label, spec.values[i]]; });

        var thead = table.createTHead().insertRow();
        head.forEach(function (text, i) {
            var th = document.createElement('th');
            th.textContent = text;
            if (i > 0) th.className = 'num';
            thead.appendChild(th);
        });

        var tbody = table.createTBody();
        rows.forEach(function (row) {
            var tr = tbody.insertRow();
            row.forEach(function (cell, i) {
                var td = tr.insertCell();
                if (i > 0) {
                    td.className = 'num';
                    td.textContent = typeof cell === 'number' ? fmt(spec.decimals).format(cell) : String(cell);
                } else {
                    td.textContent = String(cell);
                }
            });
        });

        var scroll = document.createElement('div');
        scroll.className = 'viz-table-scroll';
        scroll.appendChild(table);
        return scroll;
    }

    function wireToggle(canvasId, spec) {
        var button = document.querySelector('[data-viz-toggle="' + canvasId + '"]');
        var wrap = document.getElementById(canvasId + '-wrap');
        var tableHolder = document.getElementById(canvasId + '-table');
        if (!button || !wrap || !tableHolder) return;

        if (isEmpty(spec)) {
            button.hidden = true;
            return;
        }

        var label = button.querySelector('[data-label]');
        var icon = button.querySelector('.material-symbols-rounded');
        button.addEventListener('click', function () {
            var showTable = button.getAttribute('aria-pressed') !== 'true';
            if (showTable && !tableHolder.firstChild) {
                tableHolder.appendChild(buildTable(spec));
            }
            tableHolder.hidden = !showTable;
            wrap.hidden = showTable;
            button.setAttribute('aria-pressed', showTable ? 'true' : 'false');
            if (label) label.textContent = showTable ? 'กราฟ' : 'ตาราง';
            if (icon) icon.textContent = showTable ? 'bar_chart' : 'table_rows';
        });
    }

    // On phones the tab bar scrolls sideways - bring the current tab into
    // view without scrolling the page itself.
    function revealActiveTab() {
        var bar = document.querySelector('.dash-tabs');
        var active = bar && bar.querySelector('.dash-tab.active');
        if (!active || bar.scrollWidth <= bar.clientWidth) return;
        bar.scrollLeft = active.offsetLeft - (bar.clientWidth - active.offsetWidth) / 2;
    }

    function init() {
        revealActiveTab();
        if (!window.Chart) return;

        Chart.defaults.font.family = 'Kanit, system-ui, -apple-system, "Segoe UI", sans-serif';
        Chart.defaults.font.size = 12;
        Chart.defaults.color = C.muted;

        document.querySelectorAll('canvas[data-chart]').forEach(function (canvas) {
            var spec = parseSpec(canvas);
            var wrap = canvas.parentElement;
            wireToggle(canvas.id, spec);

            if (isEmpty(spec)) {
                renderEmpty(wrap, spec && spec.empty);
                return;
            }

            wrap.style.height = containerHeight(spec) + 'px';
            new Chart(canvas, buildConfig(spec));
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
