const fs = require('fs'), vm = require('vm'), assert = require('assert');
const source = fs.readFileSync('addons/rel_caixa/index.php','utf8');
const fragment = source.slice(source.indexOf("    let movementFilter ="), source.indexOf("    window.addEventListener('beforeprint'"));
function row(movement,fee) { const classes = new Set(fee ? ['tarifa-row'] : []); return {querySelectorAll:()=>[],dataset:{movement,search:'cliente teste'},classList:{contains:c=>classes.has(c),remove:c=>classes.delete(c),toggle:(c,on)=>on?classes.add(c):classes.delete(c)}, classes}; }
const entry=row('entrada',false), expense=row('saida',true), rows=[entry,expense];
const footer={'rc-footer-incoming':{},'rc-footer-extra':{},'rc-footer-outgoing':{}};
const button={}, search={value:''};
const context={window:{},document:{getElementById:id=>id==='search'?search:(footer[id]||button),querySelectorAll:sel=>sel.includes('[data-search]')?rows:sel.includes('tarifa-row')?[expense]:[],querySelector:()=>{throw Error('Filtros não podem reescrever os totais do caixa');}}};
vm.createContext(context);vm.runInContext(fragment,context);
vm.runInContext('applyTableFilters()',context); assert(rows.every(r=>!r.classes.has('hidden')&&!r.classes.has('filter-hidden')));
vm.runInContext("setMovementFilter('entrada', null)",context);assert(!entry.classes.has('filter-hidden')&&expense.classes.has('filter-hidden'));
vm.runInContext("setMovementFilter('saida', null)",context);assert(entry.classes.has('filter-hidden')&&!expense.classes.has('filter-hidden'));
vm.runInContext("setMovementFilter('todos', null); tariffsVisible=false; applyTableFilters()",context);assert(expense.classes.has('hidden')&&!entry.classes.has('hidden'));
assert(source.includes('number_format($saldo'));console.log('OK: filtros preservam totais; abertura exibe entradas e saídas; tarifas filtram somente tabela.');

entry.dataset.extra='409';
vm.runInContext("setMovementFilter('acrescimos', null)",context);assert(!entry.classes.has('filter-hidden')&&expense.classes.has('filter-hidden'));
entry.dataset.extra='0';vm.runInContext('applyTableFilters()',context);assert(rows.every(r=>r.classes.has('filter-hidden')));
entry.dataset.extra='409';search.value='outro';vm.runInContext('applyTableFilters()',context);assert(entry.classes.has('filter-hidden'));
search.value='';vm.runInContext("setMovementFilter('todos', null)",context);assert(rows.every(r=>!r.classes.has('filter-hidden')));
assert(source.includes('data-filter="acrescimos" role="button" tabindex="0"'));
console.log('OK: card de acréscimos filtra positivos, combina busca e retorna à visão completa.');

entry.dataset.incoming='11399'; entry.dataset.outgoing='138';entry.dataset.fee='138';entry.dataset.extra='409';
vm.runInContext("tariffsVisible=true; setMovementFilter('todos', null)",context);
assert.strictEqual(footer['rc-footer-incoming'].textContent.replace(/\s/g,''),'R$113,99');
assert.strictEqual(footer['rc-footer-extra'].textContent.replace(/\s/g,''),'R$4,09');
assert.strictEqual(footer['rc-footer-outgoing'].textContent.replace(/\s/g,''),'R$1,38');
vm.runInContext('tariffsVisible=false; applyTableFilters()',context);assert.strictEqual(footer['rc-footer-outgoing'].textContent.replace(/\s/g,''),'R$0,00');
search.value='outro';vm.runInContext('applyTableFilters()',context);assert.strictEqual(footer['rc-footer-incoming'].textContent.replace(/\s/g,''),'R$0,00');
console.log('OK: totais do rodapé respeitam agrupamento, busca e tarifas sem alterar cards.');
