const assert=require('assert'),fs=require('fs');
const {build,totals}=require('../addons/rel_caixa/relatorio_print.js');
const data={start:'2026-11-01',end:'2026-11-17',scope:'Todos os movimentos · Inclui taxas',generated:'17/11/2026 12:00',rows:[{client:'Cliente <script>alert(1)</script>',title:'5117',date:'17/11/2026',user:'Atendente',account:'Conta A',method:'Boleto',history:'Recebimento',incoming:11399,outgoing:138,extra:409,extraKnown:true,fee:138}]};
assert.deepStrictEqual(totals(data.rows),{incoming:11399,outgoing:138,extra:409});
const simple=build(data,false),detail=build(data,true);assert(simple.includes('R$ 112,61'));assert(simple.includes('01/11/2026 a 17/11/2026'));assert(!simple.includes('<script>alert'));assert(simple.includes('&lt;script&gt;'));assert(!simple.includes('Por conta bancária'));['Por conta bancária','Por forma de recebimento','Por motivo','Por usuário','Taxa de boleto','Conta A'].forEach(x=>assert(detail.includes(x)));assert(!detail.includes('mk-auth.css'));assert(detail.includes('Acréscimos identificados: R$ 4,09'));
if(process.env.RC_REPORT_PREVIEW){fs.writeFileSync('../relatorio-simples-preview.html',simple);fs.writeFileSync('../relatorio-detalhado-preview.html',detail);}
assert(build({...data,rows:[]},false).includes('Nenhum lançamento atende aos filtros.'));
const hidden=build({...data,scope:'Taxas ocultas',rows:data.rows.map(r=>({...r,outgoing:0,fee:0}))},true);assert(hidden.includes('Resultado: R$ 113,99'));assert(!hidden.includes('<td>Taxa de boleto</td>'));
console.log('OK: relatório simples/detalhado, totais em centavos, filtros, taxas e escape de HTML.');
