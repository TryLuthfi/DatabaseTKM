'use strict';
// Run the production comparison layout/switch coordinator without network or database access.
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const test = require('node:test');
const view = fs.readFileSync(path.join(__dirname, '../application/views/PO_Monitor/index.php'), 'utf8').replace(/\r\n/g, '\n');
const layout = view.slice(view.indexOf('            var compareAdjustFrame ='), view.indexOf('            $(window)\n', view.indexOf('            var compareAdjustFrame =')));
const switches = view.slice(view.indexOf('            function syncCompareSwitches()'), view.indexOf("            $('#po_compare_data_only, #po_compare_week_mode')"));
assert.ok(layout.includes('function scheduleCompareTableAdjust()') && switches.includes('activeTable.draw()'));

function harness(options = {}) {
    let nextId = 0, width = 1000, weekMode = false, loads = 0;
    const frames = new Map(), timers = new Map(), panels = {};
    function table() {
        const result = { draws: 0, adjustments: 0, page: 3 };
        result.draw = () => { result.draws++; result.page = 0; return result; };
        result.columns = { adjust() { result.adjustments++; return result; } };
        return result;
    }
    const context = {
        compareTables: { month: table(), week: options.weekLoaded ? table() : null },
        document: { getElementById() { return { get clientWidth() { return width; } }; } },
        window: {
            setTimeout(fn, delay) { const id = ++nextId; timers.set(id, { fn, delay }); return id; },
            clearTimeout(id) { timers.delete(id); }
        },
        $(selector) { return { is() { return weekMode; }, toggleClass(name, value) { panels[selector] = value; } }; },
        loadWeekCompareTable(callback) { loads++; context.pendingWeek = callback; }
    };
    if (!options.noRaf) context.window.requestAnimationFrame = fn => { const id = ++nextId; frames.set(id, fn); return id; };
    vm.createContext(context);
    vm.runInContext(layout + switches, context);
    const flush = map => { const pending = [...map.values()]; map.clear(); pending.forEach(entry => (entry.fn || entry)()); };
    return {
        context, frames, timers, panels, table,
        get loads() { return loads; }, setWeek(value) { weekMode = value; }, setWidth(value) { width = value; },
        frame() { flush(frames); }, settle() { flush(timers); }
    };
}

test('a resize burst queues one frame and one final width check', () => {
    const h = harness();
    for (let i = 0; i < 40; i++) h.context.scheduleCompareTableAdjust();
    assert.equal(h.frames.size, 1); assert.equal(h.timers.size, 1);
    h.frame(); h.settle();
    assert.equal(h.context.compareTables.month.adjustments, 1);
    assert.equal(h.context.compareTables.month.draws, 0);
    assert.equal(h.context.compareTables.month.page, 3);
    assert.equal(h.loads, 0);
});

test('sidebar final width is aligned without repeated data/footer draws', () => {
    const h = harness(); h.context.scheduleCompareTableAdjust(); h.frame();
    h.setWidth(1200); h.settle();
    assert.equal(h.context.compareTables.month.adjustments, 2);
    assert.equal(h.context.compareTables.month.draws, 0);
});

test('unchanged ResizeObserver notifications do no further layout work', () => {
    const h = harness(); h.context.scheduleCompareTableAdjust(); h.frame(); h.settle();
    h.context.scheduleCompareTableAdjust(); h.frame(); h.settle();
    assert.equal(h.context.compareTables.month.adjustments, 1);
});

test('zero-width hidden container defers alignment until visible', () => {
    const h = harness(); h.setWidth(0); h.context.scheduleCompareTableAdjust(); h.frame(); h.settle();
    assert.equal(h.context.compareTables.month.adjustments, 0);
    h.setWidth(800); h.context.scheduleCompareTableAdjust(); h.frame();
    assert.equal(h.context.compareTables.month.adjustments, 1);
});

test('month mode draws only month and never preloads week data', () => {
    const h = harness({ weekLoaded: true }); h.context.syncCompareSwitches(); h.frame(); h.settle();
    assert.equal(h.context.compareTables.month.draws, 1);
    assert.equal(h.context.compareTables.week.draws, 0);
    assert.equal(h.context.compareTables.week.adjustments, 0);
    assert.equal(h.loads, 0);
    assert.equal(h.panels['#po_compare_week_panel'], true);
});

test('week mode draws only week so data-only filtering updates on activation', () => {
    const h = harness({ weekLoaded: true }); h.setWeek(true); h.context.syncCompareSwitches(); h.frame();
    assert.equal(h.context.compareTables.month.draws, 0);
    assert.equal(h.context.compareTables.week.draws, 1);
    assert.equal(h.context.compareTables.week.adjustments, 1);
    assert.equal(h.panels['#po_compare_month_panel'], true);
});

test('switching back aligns the formerly hidden table even at the same width', () => {
    const h = harness({ weekLoaded: true }); h.context.syncCompareSwitches(); h.frame();
    h.setWeek(true); h.context.syncCompareSwitches(); h.frame();
    h.setWeek(false); h.context.syncCompareSwitches(); h.frame();
    assert.equal(h.context.compareTables.month.adjustments, 2);
    assert.equal(h.context.compareTables.week.adjustments, 1);
});

test('new week initialization is lazy and does not add a second data redraw', () => {
    const h = harness(); h.setWeek(true); h.context.syncCompareSwitches(); h.frame();
    assert.equal(h.loads, 1); assert.equal(h.context.compareTables.month.draws, 0);
    h.context.compareTables.week = h.table(); h.context.pendingWeek(true); h.frame(); h.settle();
    assert.equal(h.context.compareTables.week.draws, 0);
    assert.equal(h.context.compareTables.week.adjustments, 1);
});

test('week response arriving after returning to month does not align hidden week', () => {
    const h = harness(); h.setWeek(true); h.context.syncCompareSwitches(); h.frame();
    h.setWeek(false); h.context.syncCompareSwitches(); h.frame();
    h.context.compareTables.week = h.table(); h.context.pendingWeek(true); h.frame(); h.settle();
    assert.equal(h.context.compareTables.week.draws, 0);
    assert.equal(h.context.compareTables.week.adjustments, 0);
});

test('failed week load never redraws or adjusts another table', () => {
    const h = harness(); h.setWeek(true); h.context.syncCompareSwitches(); h.frame();
    h.context.pendingWeek(false); h.settle();
    assert.equal(h.context.compareTables.month.draws, 0);
    assert.equal(h.context.compareTables.month.adjustments, 0);
});

test('older browser timer fallback still batches and never redraws data', () => {
    const h = harness({ noRaf: true });
    for (let i = 0; i < 20; i++) h.context.scheduleCompareTableAdjust();
    assert.equal(h.timers.size, 2); h.settle();
    assert.equal(h.context.compareTables.month.adjustments, 1);
    assert.equal(h.context.compareTables.month.draws, 0);
});
