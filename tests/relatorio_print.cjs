const assert=require('assert'),fs=require('fs');
const {build,totals}=require('../addons/rel_caixa/relatorio_print.js');
const data={start:'2026-11-01',end:'2026-11-17',scope:'Todos os movimentos · Inclui taxas',generated:'17/11/2026 12:00',rows:[{client:'Cliente <script>alert(1)</script>',title:'5117',date:'17/11/2026',user:'Atendente',account:'Conta A',method:'Boleto',history:'Recebimento',incoming:11399,outgoing:138,extra:409,extraKnown:true,fee:138}]};
assert.deepStrictEqual(totals(data.rows),{incoming:11399,outgoing:138,extra:409,fees:138});
const simple=build(data,false),detail=build(data,true);assert(simple.includes('R$ 112,61'));assert(simple.includes('01/11/2026 a 17/11/2026'));assert(!simple.includes('<script>alert'));assert(simple.includes('&lt;script&gt;'));assert(!simple.includes('Por conta bancária'));['Por conta bancária','Por forma de recebimento','Por motivo','Por usuário','Taxa de boleto','Conta A'].forEach(x=>assert(detail.includes(x)));assert(!detail.includes('mk-auth.css'));assert(detail.includes('Acréscimos identificados: R$ 4,09'));
if(process.env.RC_REPORT_PREVIEW){fs.writeFileSync('../relatorio-simples-preview.html',simple);fs.writeFileSync('../relatorio-detalhado-preview.html',detail);}
assert(build({...data,rows:[]},false).includes('Nenhum lançamento atende aos filtros.'));
const hidden=build({...data,scope:'Taxas ocultas',rows:data.rows.map(r=>({...r,outgoing:0,fee:0}))},true);assert(hidden.includes('Resultado: R$ 113,99'));assert(!hidden.includes('<td>Taxa de boleto</td>'));
console.log('OK: relatório simples/detalhado, totais em centavos, filtros, taxas e escape de HTML.');

const longReport=build({...data,rows:Array.from({length:16},()=>({...data.rows[0],date:'17-11-2026 18:01:05'}))},true);
assert(longReport.includes('class="report-break"'));assert(longReport.includes('17/11/2026 18:01:05'));assert(longReport.includes('class="summaries"'));assert(longReport.includes('font-size:11px'));
console.log('OK: relatório longo tem resumos compactos, datas brasileiras e quebra antes dos lançamentos.');

const branded=build({...data,provider:{name:'Provedor Exemplo LTDA',cnpj:'11.222.333/0001-44',logo:'data:image/png;base64,aGVsbG8='}},false);
assert(branded.includes('Provedor Exemplo LTDA'));assert(branded.includes('CNPJ: 11.222.333/0001-44'));assert(branded.includes('alt="Logo do provedor"'));assert(branded.indexOf('Provedor Exemplo LTDA')<branded.indexOf('<h1>Relatório simples'));
const unsafeLogo=build({...data,provider:{name:'<script>',cnpj:'123',logo:'javascript:alert(1)'}},true);assert(!unsafeLogo.includes('src="javascript'));assert(unsafeLogo.includes('&lt;script&gt;'));
console.log('OK: identidade do provedor no cabeçalho, CNPJ e logo com conteúdo seguro.');

const identified=build({...data,provider:{name:'Nome do Provedor',legalName:'Razão Social Exemplo LTDA',cnpj:'11.222.333/0001-44',logo:''}},true);assert(identified.includes('Razão social: Razão Social Exemplo LTDA'));assert(identified.indexOf('Nome do Provedor')<identified.indexOf('Razão social:'));assert(identified.indexOf('Razão social:')<identified.indexOf('CNPJ:'));
console.log('OK: nome do provedor primeiro, razão social e CNPJ separados.');

for (const report of [simple,detail]) {assert(report.includes('Tarifas de boleto</th>'));assert(report.includes('Tarifas de boleto: R$ 1,38'));assert(report.includes('Saída total'));assert(report.includes('Resultado: R$ 112,61'));}
const mixedFees=totals([...data.rows,{...data.rows[0],incoming:0,outgoing:1000,fee:0,extra:0}]);assert.strictEqual(mixedFees.fees,138);assert.strictEqual(mixedFees.outgoing,1138);assert.strictEqual(mixedFees.incoming-mixedFees.outgoing,10261);
assert(hidden.includes('Tarifas de boleto: R$ 0,00'));
console.log('OK: coluna e total de tarifas separados, outras saídas e resultado sem desconto duplicado.');
