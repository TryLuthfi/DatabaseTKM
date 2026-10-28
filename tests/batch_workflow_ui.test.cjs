'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../application/views/Batch_Approval_MyRep/detail.php'), 'utf8');
const start = source.indexOf("        var workflowSaku = document.getElementById('batch-workflow-saku');");
const end = source.indexOf('        var MAX_PIC_ROWS = 5;', start);
assert.ok(start > 0 && end > start, 'actual correction coordinator is present');
const code = source.slice(start, end);
test('evidence requirement follows every actual Saku action including reset', () => {
  const listeners = new Map();
  const action = { value: 'KEEP', addEventListener(event, callback) { listeners.set(event, callback); } };
  const evidence = { required: false, attrs: {}, setAttribute(key, value) { this.attrs[key] = value; } };
  vm.runInNewContext(code, { document: { getElementById(id) { return id === 'batch-workflow-saku' ? action : evidence; } } });
  assert.equal(evidence.required, false);
  assert.equal(evidence.attrs['aria-required'], 'false');
  for (const value of ['REQUEST', 'APPROVE', 'REJECT', 'RESET', 'KEEP']) {
    action.value = value;
    listeners.get('change')();
    assert.equal(evidence.required, value !== 'KEEP');
    assert.equal(evidence.attrs['aria-required'], String(value !== 'KEEP'));
  }
});
test('non-admin page without correction controls runs safely', () => {
  assert.doesNotThrow(() => vm.runInNewContext(code, { document: { getElementById() { return null; } } }));
});
