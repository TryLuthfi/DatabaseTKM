(function (root, factory) {
    'use strict';
    var api = factory();
    if (typeof module === 'object' && module.exports) { module.exports = api; }
    if (root && root.document) { api.mount(root.document); }
}(typeof window !== 'undefined' ? window : null, function () {
    'use strict';
    function number(value) { var n = Number(value); return isFinite(n) ? n : 0; }
    function text(value) { return value === null || value === undefined ? '' : String(value); }
    function money(value) { var amount = number(value); return amount === 0 ? '-' : 'Rp' + amount.toLocaleString('id-ID', { maximumFractionDigits: 0 }); }
    function percent(value) { var amount = number(value); return amount === 0 ? '-' : amount.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%'; }
    function ratio(achievement, target) { return target > 0 ? achievement / target * 100 : 0; }
    function cleanRow(row) {
        var result = Object.assign({}, row);
        result.pic = text(row.pic).trim() || 'Belum dipetakan';
        result.bowheer = text(row.bowheer); result.type = text(row.type);
        ['regular', 'acceleration', 'achievement', 'regular_achievement', 'acceleration_achievement'].forEach(function (key) { result[key] = number(row[key]); });
        result.target = result.regular + result.acceleration;
        result.remaining = Math.max(0, result.target - result.achievement);
        result.regular_remaining = Math.max(0, result.regular - result.regular_achievement);
        result.acceleration_remaining = Math.max(0, result.acceleration - result.acceleration_achievement);
        result.percentage = ratio(result.achievement, result.target);
        return result;
    }
    function totals(rows) {
        var sum = { target: 0, regular: 0, acceleration: 0, achievement: 0, regular_achievement: 0, acceleration_achievement: 0, remaining: 0, regular_remaining: 0, acceleration_remaining: 0 };
        rows.forEach(function (row) { Object.keys(sum).forEach(function (key) { sum[key] += number(row[key]); }); });
        sum.percentage = ratio(sum.achievement, sum.target);
        sum.regular_percentage = ratio(sum.regular_achievement, sum.regular);
        sum.acceleration_percentage = ratio(sum.acceleration_achievement, sum.acceleration);
        return sum;
    }
    function filtered(rows, query, pic) {
        var terms = text(query).trim().toLocaleLowerCase('id-ID').split(/\s+/).filter(Boolean);
        return rows.filter(function (row) {
            var haystack = [row.pic, row.bowheer, row.type].join(' ').toLocaleLowerCase('id-ID');
            return (!pic || row.pic === pic) && terms.every(function (term) { return haystack.indexOf(term) !== -1; });
        });
    }
    function summary(rows) {
        var groups = Object.create(null);
        rows.forEach(function (row) { (groups[row.pic] || (groups[row.pic] = [])).push(row); });
        return Object.keys(groups).sort(function (a, b) { return a.localeCompare(b, 'id'); }).map(function (pic) { return Object.assign({ pic: pic }, totals(groups[pic])); });
    }
    function csv(rows) {
        function cell(value) { var s = text(value); if (typeof value !== 'number' && /^[\s]*[=+\-@\t\r]/.test(s)) { s = "'" + s; } return '"' + s.replace(/"/g, '""') + '"'; }
        var lines = [['No.', 'PIC', 'Bowheer', 'Type', 'Target Reguler', 'Akselerasi CO', 'Achievement', 'Sisa Total', 'Capaian (%)']];
        rows.forEach(function (r, i) { lines.push([i + 1, r.pic, r.bowheer, r.type, r.regular, r.acceleration, r.achievement, r.remaining, r.percentage]); });
        var t = totals(rows); lines.push(['TOTAL HASIL FILTER', '', '', '', t.regular, t.acceleration, t.achievement, t.remaining, t.percentage]);
        return '\uFEFF' + lines.map(function (line) { return line.map(cell).join(','); }).join('\r\n');
    }
    function mount(doc) {
        var container = doc.getElementById('akselerasi-dashboard'); if (!container) { return; }
        try {
            var payload = JSON.parse(doc.getElementById('aks-dashboard-data').textContent);
            var dashboard = payload.dashboard || {};
            if (payload.error) { doc.getElementById('aks-count').textContent = 'Data belum tersedia'; return; }
            var rows = (dashboard.rows || []).map(cleanRow);
            var input = doc.getElementById('aks-search'), select = doc.getElementById('aks-pic');
            var visible = rows.slice();
            function element(tag, value, className) { var el = doc.createElement(tag); if (value !== undefined) { el.textContent = text(value); } if (className) { el.className = className; } return el; }
            function clear(el) { while (el.firstChild) { el.removeChild(el.firstChild); } }
            function amountCell(tr, value) { var td = element('td', money(value), 'aks-number' + (value < 0 ? ' aks-negative' : '')); tr.appendChild(td); }
            function progress(tr, value) {
                var td = element('td', undefined, 'aks-number'), box = element('div', undefined, 'aks-capaian');
                var track = element('span', undefined, 'aks-mini-track'), bar = element('span');
                track.setAttribute('aria-hidden', 'true'); bar.style.width = Math.max(0, Math.min(100, value)) + '%'; track.appendChild(bar);
                box.appendChild(track); box.appendChild(element('span', percent(value), value < 0 ? 'aks-negative' : '')); td.appendChild(box); tr.appendChild(td);
            }
            function empty(body, span, label) { var tr = element('tr'), td = element('td', label, 'aks-empty'); td.colSpan = span; tr.appendChild(td); body.appendChild(tr); }
            function footer(id, t, isDetail) {
                var body = doc.getElementById(id); clear(body); var tr = element('tr');
                var heading = element('th', 'TOTAL HASIL FILTER'); heading.scope = 'row'; if (isDetail) { heading.colSpan = 4; } tr.appendChild(heading);
                (isDetail ? [t.regular, t.acceleration, t.achievement, t.remaining] : [t.target, t.achievement, t.remaining]).forEach(function (value) { amountCell(tr, value); });
                progress(tr, t.percentage); body.appendChild(tr);
            }
            function render() {
                visible = filtered(rows, input.value, select.value); var t = totals(visible);
                var detail = doc.getElementById('aks-detail-body'); clear(detail);
                visible.forEach(function (row, i) {
                    var tr = element('tr'); tr.appendChild(element('td', i + 1)); tr.appendChild(element('td', row.pic, 'aks-pic-cell')); tr.appendChild(element('td', row.bowheer, 'aks-bowheer-cell')); tr.appendChild(element('td', row.type, 'aks-type-cell'));
                    [row.regular, row.acceleration, row.achievement, row.remaining].forEach(function (value) { amountCell(tr, value); }); progress(tr, row.percentage); detail.appendChild(tr);
                });
                if (!visible.length) { empty(detail, 9, 'Tidak ada bowheer yang cocok dengan pencarian atau filter.'); }
                var body = doc.getElementById('aks-summary-body'); clear(body);
                summary(visible).forEach(function (row) { var tr = element('tr'); tr.appendChild(element('td', row.pic, 'aks-pic-cell')); [row.target, row.achievement, row.remaining].forEach(function (value) { amountCell(tr, value); }); progress(tr, row.percentage); body.appendChild(tr); });
                if (!visible.length) { empty(body, 5, 'Tidak ada PIC pada hasil pencarian.'); }
                footer('aks-detail-footer', t, true); footer('aks-summary-footer', t, false);
                Array.prototype.forEach.call(container.querySelectorAll('[data-aks-total]'), function (el) { var key = el.getAttribute('data-aks-total'); el.textContent = key.indexOf('percentage') !== -1 ? percent(t[key]) : money(t[key]); el.classList.toggle('aks-negative', t[key] < 0); });
                ['regular', 'acceleration'].forEach(function (kind) { var bar = doc.getElementById('aks-' + kind + '-progress'); var value = t[kind + '_percentage']; bar.setAttribute('aria-valuenow', Math.max(0, Math.min(100, value))); bar.setAttribute('aria-valuetext', percent(value)); bar.firstElementChild.style.width = Math.max(0, Math.min(100, value)) + '%'; });
                doc.getElementById('aks-count').textContent = visible.length + ' dari ' + rows.length + ' baris';
                doc.getElementById('aks-export').disabled = !visible.length;
            }
            summary(rows).forEach(function (group) { var option = element('option', group.pic); option.value = group.pic; select.appendChild(option); });
            input.addEventListener('input', render); select.addEventListener('change', render);
            doc.getElementById('aks-reset').addEventListener('click', function () { input.value = ''; select.value = ''; render(); input.focus(); });
            doc.getElementById('aks-date-form').addEventListener('submit', function (event) { var field = doc.getElementById('aks-end-date'); if (!field.checkValidity()) { event.preventDefault(); field.reportValidity(); return; } var button = this.querySelector('button[type="submit"]'); button.disabled = true; button.textContent = 'Memuat…'; });
            doc.getElementById('aks-export').addEventListener('click', function () { var blob = new Blob([csv(visible)], { type: 'text/csv;charset=utf-8;' }); var url = URL.createObjectURL(blob); var a = element('a'); a.href = url; a.download = 'akselerasi-2026-' + text(dashboard.to) + '.csv'; doc.body.appendChild(a); a.click(); doc.body.removeChild(a); setTimeout(function () { URL.revokeObjectURL(url); }, 1000); });
            function formatDate(value) { var match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(text(value)); return match ? match[3] + '/' + match[2] + '/' + match[1] : text(value); }
            doc.getElementById('aks-period').textContent = formatDate(dashboard.from) + ' – ' + formatDate(dashboard.to);
            doc.getElementById('aks-source-total').textContent = 'Total invoice sumber (periode): ' + money(dashboard.source_total);
            var unmapped = dashboard.unmapped || [];
            if (unmapped.length) {
                doc.getElementById('aks-unmapped').hidden = false; var unmappedTotal = 0; var unmappedBody = doc.getElementById('aks-unmapped-body');
                unmapped.forEach(function (row) { var tr = element('tr'); tr.appendChild(element('td', row.bowheer || '—')); tr.appendChild(element('td', row.type || '—')); amountCell(tr, number(row.achievement)); unmappedTotal += number(row.achievement); unmappedBody.appendChild(tr); });
                doc.getElementById('aks-unmapped-total').textContent = money(unmappedTotal);
            }
            render();
        } catch (error) { doc.getElementById('aks-runtime-error').hidden = false; doc.getElementById('aks-count').textContent = 'Dashboard gagal dimuat'; }
    }
    return { cleanRow: cleanRow, totals: totals, filtered: filtered, summary: summary, csv: csv, mount: mount };
}));
