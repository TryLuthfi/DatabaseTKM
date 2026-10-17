/* Execute the production view's coordinator with isolated AJAX/DataTable doubles.
 * No application bootstrap, browser session, or database access is involved.
 */
'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const view = fs.readFileSync(path.join(__dirname, '../application/views/PO_Monitor/index.php'), 'utf8');
const start = view.indexOf('            var poBreakdownState = {');
const end = view.indexOf("            $('.js-po-purge-form')", start);
assert.ok(start > 0 && end > start, 'Production coordinator boundaries exist');
const source = view.slice(start, end).replace(/<\?=[\s\S]*?\?>/g, 'fixture-url');
const fields = ['project', 'pic', 'regional', 'area', 'month', 'week'];
const optionKeys = ['projects', 'pics', 'regionals', 'areas', 'months', 'weeks'];
const escapeHtml = value => String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
const unescapeHtml = value => value.replace(/&quot;/g, '"').replace(/&#039;/g, "'").replace(/&gt;/g, '>').replace(/&lt;/g, '<').replace(/&amp;/g, '&');
function harness() {
    const elements = new Map(), requests = [], tableRequests = [], timers = new Map();
    let timerId = 0, serverVersion = 1;
    const element = key => {
        if (!elements.has(key)) elements.set(key, { key, handlers: {}, value: key === '#po_breakdown_limit' ? '10' : '', options: [], props: {}, html: '' });
        return elements.get(key);
    };
    function $(selector) {
        if (selector && selector.__wrapped) return selector;
        const nodes = typeof selector === 'string' ? selector.split(',').map(s => element(s.trim())) : [selector];
        const first = nodes[0];
        const api = {
            __wrapped: true, length: nodes.length,
            val(value) {
                if (arguments.length === 0) return first.value;
                nodes.forEach(n => { n.value = Array.isArray(value) ? value.slice() : value; }); return api;
            },
            prop(key, value) {
                if (arguments.length === 1) return first.props[key];
                nodes.forEach(n => { n.props[key] = value; }); return api;
            },
            is() { return !!first.props.checked; },
            data(key) { return first.data && first.data[key]; },
            html(value) {
                if (arguments.length === 0) return first.html;
                nodes.forEach(n => {
                    n.html = value;
                    n.options = Array.from(String(value).matchAll(/<option value="([^"]*)">([\s\S]*?)<\/option>/g), m => ({ value: unescapeHtml(m[1]), label: unescapeHtml(m[2]) }));
                }); return api;
            },
            text(value) {
                if (arguments.length === 0) return first.label || first.text || '';
                nodes.forEach(n => { n.text = value; }); return api;
            },
            find(query) {
                if (query !== 'option:selected') return $(element(first.key + ' ' + query));
                const chosen = first.options.filter(o => (first.value || []).includes(o.value));
                return { map(fn) { return { get() { return chosen.map(o => fn.call(o)); } }; } };
            },
            toggleClass() { return api; }, select2() { return api; },
            trigger() { return api; },
            modal(action) { first.modalAction = action; return api; },
            off(events) { nodes.forEach(n => { delete n.handlers[events]; }); return api; },
            on(events, selectorOrHandler, maybeHandler) {
                nodes.forEach(n => { n.handlers[events] = maybeHandler || selectorOrHandler; }); return api;
            },
            DataTable(options) {
                const mode = /data-breakdown-table="([^"]+)"/.exec(first.key)[1];
                const table = { limit: options.pageLength, mode };
                function request(kind, resetPaging) {
                    const data = options.ajax.data({ draw: tableRequests.length + 1 });
                    table.renderedVersion = serverVersion;
                    tableRequests.push(JSON.parse(JSON.stringify({ mode, kind, length: table.limit, data, resetPaging, serverVersion })));
                }
                table.page = { len(value) { table.limit = value; return table; } };
                table.ajax = { reload(callback, resetPaging) { request('reload', resetPaging); return table; }, json() { return {}; } };
                table.columns = { adjust() { return table; } };
                table.draw = function() { request('draw'); return table; };
                request('init'); return table;
            }
        };
        return api;
    }
    $.extend = Object.assign; $.trim = value => value.trim();
    $.ajax = options => {
        const request = { options, aborted: false };
        request.abort = () => { request.aborted = true; options.error?.({}, 'abort'); options.complete?.(); };
        requests.push(request); return request;
    };
    fields.forEach(field => { element('#po_breakdown_filter_' + field).value = []; });
    const document = element('document');
    const context = {
        $, document, escapeHtml,
        window: {
            clearTimeout(id) { timers.delete(id); },
            setTimeout(fn) { const id = ++timerId; timers.set(id, fn); return id; }
        }
    };
    vm.runInNewContext(source + '\nglobalThis.coordinator = { state: poBreakdownState, reload: poBreakdownReload };', context);
    function fire(selector, event, thisNode) {
        const node = element(selector), handler = node.handlers[event];
        assert.ok(handler, 'Registered handler: ' + selector + ' / ' + event);
        handler.call(thisNode || node, { currentTarget: { id: selector.replace(/^#/, '') } });
    }
    function tab(mode) { fire('.po-breakdown-tabs .nav-link', 'shown.bs.tab.poBreakdown', { data: { 'breakdown-mode': mode } }); }
    function optionsResponse(index, values = {}) {
        const options = Object.fromEntries(optionKeys.map(key => [key, values[key] || []]));
        requests[index].options.success({ options }); requests[index].options.complete?.();
    }
    function choose(field, values) {
        const node = element('#po_breakdown_filter_' + field);
        node.options = values.map(value => ({ value, label: value })); node.value = values.slice();
        fire(node.key, 'change.poBreakdown');
    }
    return {
        $, element, requests, tableRequests, fire, tab, optionsResponse, choose,
        open() { fire('#po_breakdown_filter_modal', 'shown.bs.modal.poBreakdown'); },
        apply() { fire('#po_breakdown_apply', 'click.poBreakdown'); },
        flush() { const pending = [...timers.values()]; timers.clear(); pending.forEach(fn => fn()); },
        state: context.coordinator.state,
        updateServer() { serverVersion++; }
    };
}
let passed = 0;
function test(name, fn) { fn(); passed++; console.log('PASS ' + name); }
test('Initial load makes one project table request and zero filter option requests', () => {
    const h = harness(); assert.equal(h.requests.length, 0); assert.equal(h.tableRequests.length, 1); assert.equal(h.tableRequests[0].mode, 'project');
});
test('New tab initializes once; clean existing tabs fetch once and preserve paging', () => {
    const h = harness(); h.tab('pic'); assert.equal(h.tableRequests.length, 2); h.tab('project'); h.tab('pic'); assert.equal(h.tableRequests.length, 4);
    assert.equal(h.tableRequests[2].resetPaging, false); assert.equal(h.tableRequests[3].resetPaging, false);
});
test('Reopening a clean hidden tab receives changed server data even with unchanged filters', () => {
    const h = harness(); h.tab('pic'); const prior = h.state.tables.project.renderedVersion;
    h.updateServer(); h.tab('project'); assert.equal(h.state.tables.project.renderedVersion, prior + 1);
    assert.equal(h.tableRequests.length, 3); assert.equal(h.tableRequests[2].data.breakdown_search, ''); assert.equal(h.tableRequests[2].resetPaging, false);
});
test('Filter reload refreshes active only; hidden initialized tab refreshes once upon opening', () => {
    const h = harness(); h.tab('pic'); h.tab('project');
    h.$('#po_breakdown_limit').val('25'); h.fire('#po_breakdown_limit', 'change.poBreakdown');
    assert.equal(h.tableRequests.length, 4); assert.equal(h.tableRequests[3].mode, 'project'); assert.equal(h.tableRequests[3].length, 25);
    assert.equal(h.tableRequests[3].resetPaging, true);
    assert.equal(h.state.dirty.pic, true); h.tab('pic'); assert.equal(h.tableRequests.length, 5); assert.equal(h.tableRequests[4].length, 25); assert.equal(h.tableRequests[4].resetPaging, true);
    h.tab('project'); assert.equal(h.tableRequests.length, 6); assert.equal(h.tableRequests[5].resetPaging, false);
});
test('Changing state before opening a new tab uses current filters in its only initial request', () => {
    const h = harness(); h.$('#po_breakdown_invoiced_only').prop('checked', true); h.fire('#po_breakdown_invoiced_only', 'change.poBreakdown'); h.tab('area');
    assert.equal(h.tableRequests.length, 3); assert.equal(h.tableRequests[2].data.invoiced_only, 1); assert.equal(h.tableRequests[2].kind, 'init');
});
test('Debounced search reloads active tab once without hidden-tab requests', () => {
    const h = harness(); h.tab('regional'); h.tab('project');
    h.$('#po_breakdown_search').val('n'); h.fire('#po_breakdown_search', 'input.poBreakdown');
    h.$('#po_breakdown_search').val('NRO'); h.fire('#po_breakdown_search', 'input.poBreakdown'); h.flush();
    assert.equal(h.tableRequests.length, 4); assert.equal(h.tableRequests[3].data.breakdown_search, 'nro'); assert.equal(h.state.dirty.regional, true);
});
test('Immediate filter actions cancel an obsolete search debounce instead of reloading twice', () => {
    const h = harness(); h.$('#po_breakdown_search').val('NRO'); h.fire('#po_breakdown_search', 'input.poBreakdown');
    h.$('#po_breakdown_limit').val('25'); h.fire('#po_breakdown_limit', 'change.poBreakdown'); h.flush();
    assert.equal(h.tableRequests.length, 2); assert.equal(h.tableRequests[1].data.breakdown_search, 'nro');
});
test('Options load on open and refresh on each reopen, never through persistent cache', () => {
    const h = harness(); h.open(); assert.equal(h.requests.length, 1); assert.equal(h.$('#po_breakdown_apply').prop('disabled'), true);
    h.optionsResponse(0); assert.equal(h.$('#po_breakdown_apply').prop('disabled'), false); h.open(); assert.equal(h.requests.length, 2);
});
test('Rapid changes abort earlier requests and an obsolete success cannot erase newest selection', () => {
    const h = harness(); h.open(); h.choose('project', ['NRO']); h.choose('project', ['NRO', 'IFORTE']);
    assert.ok(h.requests[0].aborted && h.requests[1].aborted);
    h.optionsResponse(0); h.optionsResponse(1); assert.deepEqual(h.$('#po_breakdown_filter_project').val(), ['NRO', 'IFORTE']);
    assert.equal(h.$('#po_breakdown_apply').prop('disabled'), true);
    h.optionsResponse(2, { pics: [{ value: 'PIC-A', label: 'PIC A' }] });
    assert.deepEqual(h.$('#po_breakdown_filter_project').val(), ['NRO', 'IFORTE']); assert.equal(h.$('#po_breakdown_apply').prop('disabled'), false);
});
test('Dependent options preserve valid selections and clear invalid ones while excluding changed field', () => {
    const h = harness(); h.element('#po_breakdown_filter_pic').options = [{ value: 'A', label: 'A' }, { value: 'B', label: 'B' }];
    h.$('#po_breakdown_filter_pic').val(['A', 'B']); h.choose('project', ['NRO']);
    assert.equal(h.requests[0].options.data.except, 'project');
    h.optionsResponse(0, { pics: [{ value: 'A', label: 'A' }] });
    assert.deepEqual(Array.from(h.$('#po_breakdown_filter_pic').val()), ['A']); assert.deepEqual(h.$('#po_breakdown_filter_project').val(), ['NRO']);
});
test('Fingerprint rejects a response when current filters changed without the usual event', () => {
    const h = harness(); h.open(); h.$('#po_breakdown_search').val('latest'); h.optionsResponse(0);
    assert.equal(h.requests.length, 2); assert.equal(h.requests[1].options.data.breakdown_search, 'latest'); assert.equal(h.$('#po_breakdown_apply').prop('disabled'), true);
    h.optionsResponse(1); assert.equal(h.$('#po_breakdown_apply').prop('disabled'), false);
});
test('Apply during loading is guarded; success permits one active-table request', () => {
    const h = harness(); h.open(); h.apply(); assert.equal(h.tableRequests.length, 1);
    h.optionsResponse(0); h.apply(); assert.equal(h.tableRequests.length, 2); assert.equal(h.element('#po_breakdown_filter_modal').modalAction, 'hide');
});
test('Failure retains selected values, blocks Apply and supports retry by reopening', () => {
    const h = harness(); h.choose('project', ['NRO']); h.requests[0].options.error({}, 'timeout'); h.requests[0].options.complete();
    assert.deepEqual(h.$('#po_breakdown_filter_project').val(), ['NRO']); assert.equal(h.$('#po_breakdown_apply').prop('disabled'), true);
    assert.match(h.element('#po_breakdown_filter_status').text, /Gagal/); h.apply(); assert.equal(h.tableRequests.length, 1);
    h.open(); h.optionsResponse(1, { projects: [{ value: 'NRO', label: 'NRO' }] }); assert.equal(h.$('#po_breakdown_apply').prop('disabled'), false);
});
test('Malformed response does not erase selections and can be retried', () => {
    const h = harness(); h.choose('project', ['NRO']); h.requests[0].options.success({});
    assert.deepEqual(h.$('#po_breakdown_filter_project').val(), ['NRO']); assert.equal(h.$('#po_breakdown_apply').prop('disabled'), true);
});
test('Reset aborts pending request, resets all controls, reloads active only and ignores stale success', () => {
    const h = harness(); h.tab('date'); h.tab('project'); h.choose('project', ['NRO']);
    h.$('#po_breakdown_search').val('query'); h.$('#po_breakdown_limit').val('50'); h.$('#po_breakdown_invoiced_only').prop('checked', true);
    h.fire('#po_breakdown_reset', 'click.poBreakdown'); assert.ok(h.requests[0].aborted); assert.equal(h.requests.length, 2);
    h.optionsResponse(0); assert.deepEqual(Array.from(h.$('#po_breakdown_filter_project').val()), []); assert.equal(h.$('#po_breakdown_search').val(), '');
    assert.equal(h.tableRequests.length, 4); assert.equal(h.tableRequests[3].mode, 'project'); assert.equal(h.tableRequests[3].length, 10);
    assert.equal(h.tableRequests[3].data.invoiced_only, 0); assert.equal(h.state.dirty.date, true);
});
test('All six tabs receive the same current request filters with no duplicate initial draw', () => {
    const h = harness(); h.$('#po_breakdown_search').val('NRO');
    ['pic', 'regional', 'area', 'period', 'date'].forEach(mode => h.tab(mode)); assert.equal(h.tableRequests.length, 6);
    h.tableRequests.slice(1).forEach(request => { assert.equal(request.data.breakdown_search, 'nro'); assert.equal(request.kind, 'init'); });
});
console.log('RESULT ' + passed + '/' + passed + ' tests passed');
