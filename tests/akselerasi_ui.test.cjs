'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const ui = require('../assets/dist/js/akselerasi-2026.js');

const sample = [
    { pic: 'PIC Satu', bowheer: 'MOREP', type: 'NRO', regular: 100, acceleration: 50, achievement: 170, regular_achievement: 100, acceleration_achievement: 70 },
    { pic: 'PIC Satu', bowheer: 'IFORTE', type: 'LBS', regular: 60, acceleration: 20, achievement: -10, regular_achievement: -10, acceleration_achievement: 0 },
    { pic: 'PIC Dua', bowheer: 'VGREEN', type: 'BSS', regular: 20, acceleration: 10, achievement: 5, regular_achievement: 5, acceleration_achievement: 0 }
];

class Element {
    constructor(tag) { this.tagName = tag; this.children = []; this.attributes = {}; this.style = {}; this.events = {}; this.value = ''; this._text = ''; this.hidden = false; this.disabled = false; this.classList = { toggle() {} }; }
    set textContent(value) { this._text = String(value); this.children = []; }
    get textContent() { return this._text + this.children.map(c => c.textContent).join(''); }
    get firstChild() { return this.children[0]; }
    get firstElementChild() { return this.children[0]; }
    appendChild(child) { this.children.push(child); return child; }
    removeChild(child) { this.children.splice(this.children.indexOf(child), 1); }
    setAttribute(key, value) { this.attributes[key] = String(value); }
    getAttribute(key) { return this.attributes[key]; }
    addEventListener(type, callback) { this.events[type] = callback; }
    dispatch(type) { this.events[type].call(this, {}); }
    focus() { this.focused = true; }
}
function documentFor(rows, error = '') {
    const ids = ['akselerasi-dashboard','aks-dashboard-data','aks-count','aks-search','aks-pic','aks-detail-body','aks-summary-body','aks-detail-footer','aks-summary-footer','aks-regular-progress','aks-acceleration-progress','aks-reset','aks-date-form','aks-end-date','aks-export','aks-period','aks-source-total','aks-unmapped','aks-unmapped-body','aks-unmapped-total','aks-runtime-error'];
    const map = Object.fromEntries(ids.map(id => [id, new Element('div')]));
    map['aks-dashboard-data'].textContent = JSON.stringify({dashboard:{rows,from:'2026-11-16',to:'2026-11-17',source_total:165,unmapped:[{bowheer:'<script>alert(1)</script>',type:'X',achievement:20}]},error});
    map['aks-runtime-error'].hidden = true;
    map['aks-unmapped'].hidden = true;
    ['regular','acceleration'].forEach(key => map['aks-'+key+'-progress'].appendChild(new Element('span')));
    const metrics = ['target','regular','acceleration','achievement','remaining','percentage','regular_achievement','acceleration_achievement','regular_remaining','acceleration_remaining','regular_percentage','acceleration_percentage'].map(key => { const e = new Element('strong'); e.setAttribute('data-aks-total',key); return e; });
    map['akselerasi-dashboard'].querySelectorAll = () => metrics;
    return { getElementById:id => map[id], createElement:tag => new Element(tag), map, metrics };
}

test('net negative reverses regular and excess does not mask another bowheer remaining', () => {
    const t = ui.totals(sample.map(ui.cleanRow));
    assert.equal(t.target,260); assert.equal(t.achievement,165); assert.equal(t.remaining,115);
    assert.equal(t.regular_achievement,95); assert.equal(t.acceleration_achievement,70);
    assert.equal(t.regular_remaining,85); assert.equal(t.acceleration_remaining,30);
});
test('search intersects PIC and token search across PIC bowheer type', () => {
    const rows = sample.map(ui.cleanRow);
    assert.deepEqual(ui.filtered(rows,'satu lbs','PIC Satu').map(r => r.bowheer),['IFORTE']);
    assert.equal(ui.filtered(rows,'morep','PIC Dua').length,0);
    assert.equal(ui.filtered(rows,'   ','').length,3);
});
test('mounted UI updates row numbers, totals, PIC summary and empty/reset state', () => {
    const d = documentFor(sample); ui.mount(d); const m = d.map;
    assert.equal(m['aks-runtime-error'].hidden,true);
    assert.equal(m['aks-count'].textContent,'3 dari 3 baris');
    assert.equal(m['aks-detail-body'].children.length,3);
    assert.equal(m['aks-summary-body'].children.length,2);
    assert.match(m['aks-detail-footer'].textContent,/Rp165/);
    m['aks-search'].value = 'IFORTE'; m['aks-search'].dispatch('input');
    assert.equal(m['aks-count'].textContent,'1 dari 3 baris');
    assert.equal(m['aks-detail-body'].children[0].children[0].textContent,'1');
    assert.match(m['aks-detail-footer'].textContent,/-10/);
    assert.equal(m['aks-summary-body'].children.length,1);
    m['aks-pic'].value='PIC Dua'; m['aks-pic'].dispatch('change');
    assert.equal(m['aks-count'].textContent,'0 dari 3 baris'); assert.equal(m['aks-export'].disabled,true);
    m['aks-reset'].dispatch('click'); assert.equal(m['aks-detail-body'].children.length,3); assert.equal(m['aks-search'].focused,true);
});
test('unmapped values use text nodes and source reconciliation stays period scoped', () => {
    const d = documentFor(sample); ui.mount(d);
    assert.equal(d.map['aks-unmapped'].hidden,false);
    assert.equal(d.map['aks-unmapped-body'].children[0].children[0].textContent,'<script>alert(1)</script>');
    assert.equal(d.map['aks-unmapped-total'].textContent,'Rp20');
    assert.match(d.map['aks-source-total'].textContent,/Rp165/);
    d.map['aks-search'].value = 'MOREP'; d.map['aks-search'].dispatch('input');
    assert.match(d.map['aks-source-total'].textContent,/Rp165/);
});
test('server error does not render zero achievement as a successful empty result', () => {
    const d=documentFor([], 'Sumber gagal'); ui.mount(d);
    assert.equal(d.map['aks-count'].textContent,'Data belum tersedia');
    assert.equal(d.map['aks-summary-body'].children.length,0);
    assert.equal(d.metrics[0].textContent,'');
});
test('CSV exports all filtered rows and guards spreadsheet formula strings', () => {
    const rows=sample.map(ui.cleanRow); rows[0].pic='=HYPERLINK("bad")';
    const output=ui.csv(rows);
    assert.ok(output.startsWith('\uFEFF')); assert.match(output,/'=HYPERLINK/); assert.match(output,/"-10"/);
    assert.equal(output.split('\r\n').length,5);
});
test('zero amounts and percentages display a dash without changing numeric data or CSV', () => {
    const rows=[{pic:'WARDANI',bowheer:'MOREP',type:'NRO',regular:100,acceleration:0,achievement:0,regular_achievement:0,acceleration_achievement:0}];
    const d=documentFor(rows); const payload=JSON.parse(d.map['aks-dashboard-data'].textContent);
    payload.dashboard.source_total=0; payload.dashboard.unmapped=[{bowheer:'UNKNOWN',type:'X',achievement:0}];
    d.map['aks-dashboard-data'].textContent=JSON.stringify(payload); ui.mount(d);
    const detail=d.map['aks-detail-body'].children[0].children;
    assert.equal(detail[4].textContent,'Rp100');
    assert.equal(detail[5].textContent,'-'); assert.equal(detail[6].textContent,'-'); assert.equal(detail[8].textContent,'-');
    const summary=d.map['aks-summary-body'].children[0].children;
    assert.equal(summary[1].textContent,'Rp100'); assert.equal(summary[2].textContent,'-'); assert.equal(summary[4].textContent,'-');
    const footer=d.map['aks-detail-footer'].children[0].children;
    assert.equal(footer[2].textContent,'-'); assert.equal(footer[3].textContent,'-'); assert.equal(footer[5].textContent,'-');
    ['achievement','percentage','acceleration','regular_achievement','acceleration_achievement','acceleration_percentage'].forEach(key => {
        assert.equal(d.metrics.find(e=>e.getAttribute('data-aks-total')===key).textContent,'-');
    });
    assert.equal(d.map['aks-unmapped-total'].textContent,'-');
    assert.match(d.map['aks-source-total'].textContent,/: -$/);
    assert.equal(d.map['aks-acceleration-progress'].attributes['aria-valuenow'],'0');
    assert.equal(d.map['aks-acceleration-progress'].firstElementChild.style.width,'0%');
    const cleaned=rows.map(ui.cleanRow); assert.equal(cleaned[0].achievement,0); assert.equal(ui.totals(cleaned).percentage,0);
    const exported=ui.csv(cleaned).split('\r\n')[1];
    assert.equal(exported,'"1","WARDANI","MOREP","NRO","100","0","0","100","0"');
});
