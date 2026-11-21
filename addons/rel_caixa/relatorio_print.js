/* Standalone report: no native MK-AUTH styles or financial writes. */
(function (global) {
    'use strict';
    function esc(value) { return String(value == null ? '' : value).replace(/[&<>"']/g, function(c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
    function money(value) { return 'R$ ' + (value / 100).toLocaleString('pt-BR', {minimumFractionDigits:2,maximumFractionDigits:2}); }
    function date(value) { return String(value).replace(/^(\d{4})-(\d{2})-(\d{2})(?=$|[ T])/, '$3/$2/$1').replace(/^(\d{2})-(\d{2})-(\d{4})(?=$|[ T])/, '$1/$2/$3'); }
    function totals(rows) { return rows.reduce(function(t,r) { t.incoming+=r.incoming; t.outgoing+=r.outgoing; t.extra+=r.extra; t.fees+=Number(r.fee||0); return t; }, {incoming:0,outgoing:0,extra:0,fees:0}); }
    function cells(values,tag) { return values.map(function(v){return '<'+tag+'>'+esc(v)+'</'+tag+'>';}).join(''); }
    function grouped(rows,key) {
        var groups=Object.create(null);
        function add(name,incoming,outgoing,fee) { if(!groups[name])groups[name]={incoming:0,outgoing:0,fees:0};groups[name].incoming+=incoming;groups[name].outgoing+=outgoing;groups[name].fees+=Number(fee||0); }
        rows.forEach(function(r) {
            if(key==='reason') { if(r.incoming)add(r.title && r.title!=='--' ? 'Recebimento de título' : (r.history||'Outras entradas'),r.incoming,0); if(r.fee)add('Taxa de boleto',0,r.fee,r.fee); if(r.outgoing-r.fee)add(r.history||'Outras saídas',0,r.outgoing-r.fee); }
            else add(r[key]||'Não identificado',r.incoming,r.outgoing,r.fee);
        });
        return Object.keys(groups).sort().map(function(name){return Object.assign({name:name},groups[name]);});
    }
    function summary(rows,key) {
        return grouped(rows,key).map(function(v){return '<tr><td>'+esc(v.name)+'</td><td class="number cash-in">'+money(v.incoming)+'</td><td class="number cash-out">'+money(v.fees)+'</td><td class="number cash-out">'+money(v.outgoing)+'</td><td class="number">'+money(v.incoming-v.outgoing)+'</td></tr>';}).join('');
    }

    function build(data,detailed) {
        var provider=data.provider||{},logo=/^data:image\/(png|jpeg|gif);base64,[A-Za-z0-9+/=]+$/.test(provider.logo||'')?provider.logo:'';
        var identity='<div class="provider">'+(logo?'<img src="'+esc(logo)+'" alt="Logo do provedor">':'')+'<div><strong>'+esc(provider.name||'Provedor não informado')+'</strong>'+(provider.legalName?'<p>Razão social: '+esc(provider.legalName)+'</p>':'')+'<p>CNPJ: '+esc(provider.cnpj||'Não informado no MK-AUTH')+'</p></div></div>';
        var rows=data.rows,t=totals(rows),heading=detailed?'Relatório detalhado de caixa':'Relatório simples de caixa';
        var html='<!doctype html><html lang="pt-BR"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'+esc(heading)+'</title><style>'+
        '@page{size:A4 '+(detailed?'landscape':'portrait')+';margin:12mm}*{box-sizing:border-box}body{margin:24px;color:#172b43;background:#fff;font:12px Arial,sans-serif}h1{font-size:23px;margin:0 0 7px;color:#075a9e}h2{font-size:16px;margin:18px 0 8px;break-after:avoid}p{line-height:1.5;margin:6px 0}.provider{display:flex;align-items:center;gap:16px;margin-bottom:18px;break-inside:avoid}.provider img{width:100px;max-height:64px;object-fit:contain}.provider strong{font-size:18px;color:#183c5b}.provider p{margin:5px 0 0;font-size:13px}header{border-bottom:2px solid #1685d1;padding-bottom:12px}table{width:100%;border-collapse:collapse;margin:12px 0;table-layout:auto}section{break-inside:avoid}.summaries{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;align-items:start}.summaries h2{margin-top:12px}.summaries table{margin-top:8px}.ledger{table-layout:fixed}.ledger td,.ledger th{overflow-wrap:anywhere;word-break:normal}.ledger th{white-space:normal;line-height:1.3}th,td{padding:7px;border:1px solid #d3dfeb;text-align:left;vertical-align:top;overflow-wrap:anywhere}th{background:#e9f2fb;color:#123c63}section td:not(:first-child),section th:not(:first-child){text-align:right;white-space:nowrap}thead{display:table-header-group}tfoot{display:table-row-group}.ledger tfoot th{background:white}.ledger tfoot th:first-child{text-align:right}tr{break-inside:avoid}tbody tr:nth-child(even){background:#f8fafc}.cash-in{color:#087f4f}.cash-extra{color:#076ab7}.cash-out{color:#be123c}.net-line{margin-top:12px;border-top:1px solid #b6cde1;padding-top:12px;font-size:15px}.number{text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}.final{border:1px solid #b6cde1;padding:12px;break-inside:avoid;background:white}.final strong{display:inline-block;margin-right:24px}.note{font-size:11px;color:#485e74}.tools{display:flex;gap:10px;align-items:center;margin:0 0 20px}button{padding:10px 16px;background:#076eb9;color:white;border:0;border-radius:4px;cursor:pointer}@media screen and (max-width:640px){body{margin:12px}.summaries{grid-template-columns:minmax(0,1fr)}.summaries table,.ledger{display:block;max-width:100%;overflow-x:auto}.provider{gap:10px;align-items:flex-start}.provider img{width:72px}.provider strong{font-size:16px}.tools{flex-wrap:wrap}.final strong{display:block;margin:6px 0}}@media print{body{margin:0;font-size:11px}.tools{display:none}h1{font-size:20px}.report-break{break-before:page}th,td{padding:5px}a{color:inherit;text-decoration:none}}' +
        '</style><body><div class="tools"><button onclick="window.print()">Imprimir / Salvar PDF</button><span>Sem URL na impressão: desmarque “Cabeçalhos e rodapés” nas opções do navegador.</span></div><header>'+identity+'<h1>'+esc(heading)+'</h1><p><strong>Período: '+esc(date(data.start)+' a '+date(data.end))+'</strong></p><p>'+esc(data.scope)+'</p><p class="note">Gerado em '+esc(data.generated)+' · '+rows.length+' linha(s). Conta e forma de recebimento correspondem ao cadastro atual do título.</p></header>';
        if(detailed) html+='<div class="summaries">';
        if(detailed) [['account','Por conta bancária'],['method','Por forma de recebimento'],['reason','Por motivo'],['user','Por usuário']].forEach(function(item){html+='<section><h2>'+item[1]+'</h2><table><thead><tr>'+cells(['Descrição','Entrada','Tarifas','Saída total','Subtotal do caixa'],'th')+'</tr></thead><tbody>'+summary(rows,item[0])+'</tbody></table></section>';});
        if(detailed) html+='</div>';
        var widths=detailed?[20,9,12,6,13,8,8,8,8,8]:[28,7,15,10,10,10,10,10];
        html+='<h2 class="'+(detailed && rows.length>10?'report-break':'')+'">Lançamentos do período filtrado</h2><table class="ledger"><colgroup>'+widths.map(function(w){return '<col style="width:'+w+'%">';}).join('')+'</colgroup><thead><tr>'+cells(detailed?['Cliente / título','Data','Conta / recebimento','Usuário','Histórico','Entrada','Acréscimos','Tarifas de boleto','Saída total','Subtotal do caixa']:['Cliente','Título','Data','Entrada','Acréscimos','Tarifas de boleto','Saída total','Subtotal do caixa'],'th')+'</tr></thead><tbody>';
        rows.forEach(function(r){var text=detailed?[r.client+' / #'+r.title,date(r.date),r.account+' / '+r.method,r.user,r.history]:[r.client,r.title,date(r.date)];html+='<tr>'+cells(text,'td')+'<td class="number cash-in">'+money(r.incoming)+'</td><td class="number cash-extra">'+(r.extraKnown?money(r.extra):'Não identificado')+'</td><td class="number cash-out">'+money(Number(r.fee||0))+'</td><td class="number cash-out">'+money(r.outgoing)+'</td><td class="number">'+money(r.incoming-r.outgoing)+'</td></tr>';});
        if(!rows.length)html+='<tr><td colspan="'+(detailed?10:8)+'">Nenhum lançamento atende aos filtros.</td></tr>';
        html+='</tbody><tfoot><tr>'+ '<th colspan="'+(detailed?5:3)+'">TOTAL</th><th class="number cash-in">'+money(t.incoming)+'</th><th class="number cash-extra">'+money(t.extra)+'</th><th class="number cash-out">'+money(t.fees)+'</th><th class="number cash-out">'+money(t.outgoing)+'</th><th class="number">'+money(t.incoming-t.outgoing)+'</th></tr></tfoot></table><div class="final"><strong class="cash-in">Entradas: '+money(t.incoming)+'</strong><strong class="cash-out">Saídas: '+money(t.outgoing)+'</strong><p class="net-line"><strong>Subtotal do caixa: '+money(t.incoming-t.outgoing)+'</strong></p><p><strong class="cash-out">Tarifas de boleto: '+money(t.fees)+'</strong> — já incluídas nas saídas. Outras saídas: '+money(t.outgoing-t.fees)+'.</p><p><strong class="cash-extra">Acréscimos identificados: '+money(t.extra)+'</strong> — já incluídos nas entradas.</p></div><p class="note">Subtotal do caixa = entradas − saídas, sem saldo anterior. Este relatório respeita os filtros descritos acima; pode diferir dos cards que mostram o período completo. Acréscimos não identificados não são tratados como zero confirmado.</p></body></html>';
        return html;
    }
    function collect(tab) {
        var providerNode=document.getElementById('rc-provider-data');
        var data={rows:[],provider:providerNode?JSON.parse(providerNode.textContent):{},generated:new Date().toLocaleString('pt-BR')};
        if(tab==='loja') {
            var form=document.getElementById('lo-filter-form');
            var exported=document.getElementById('lo-export-data');data.start=exported.dataset.periodStart;data.end=exported.dataset.periodEnd;
            var applied=Array.from(document.querySelectorAll('#content-loja .lo-message')).find(function(el){return el.textContent.indexOf('Filtros aplicados:')>=0;});data.scope='Caixa personalizado · '+(applied?applied.textContent.trim():'Filtros aplicados na consulta');
            JSON.parse(document.getElementById('lo-export-data').textContent).slice(1,-1).forEach(function(r){var cents=function(v){return Math.round(Number(String(v).replace(',','.'))*100)||0;};data.rows.push({client:r[1],title:r[2],date:r[0],user:r[3],method:r[4],account:r[5],history:r[6],incoming:cents(r[7]),outgoing:cents(r[8]),extra:0,extraKnown:false,fee:/tarif|taxa/i.test(r[6])?cents(r[8]):0});});
        } else {
            var table=document.querySelector('.finance-table');data.start=table.dataset.periodStart;data.end=table.dataset.periodEnd;
            var state=global.rcPrintFilterState(),names={todos:'Todos os movimentos',entrada:'Recebimentos',saida:'Linhas com saídas',acrescimos:'Recebimentos com acréscimos'};
            data.scope=(names[state.movement]||state.movement)+' · '+(state.fees?'Inclui taxas de boleto':'Taxas ocultas, excluídas deste relatório')+' · Busca: '+(document.getElementById('search').value||'todos os clientes');
            document.querySelectorAll('#financeTableBody tr[data-search]').forEach(function(row){if(row.classList.contains('hidden')||row.classList.contains('filter-hidden'))return;var c=Array.from(row.cells).map(function(cell){return cell.textContent.trim();}),fee=Number(row.dataset.fee||0),out=Number(row.dataset.outgoing||0);data.rows.push({client:row.dataset.client||c[0],title:c[8],date:c[2],user:c[3],history:c[4],account:row.dataset.account||'Não identificada',method:row.dataset.method||'Não informada',incoming:Number(row.dataset.incoming||0),outgoing:out-(state.fees?0:fee),extra:Number(row.dataset.extra||0),extraKnown:c[6].indexOf('Não identificado')<0,fee:state.fees?(fee|| (row.classList.contains('tarifa-row')?out:0)):0});});
        }
        return data;
    }
    function pdfDefinition(data,detailed) {
        var rows=data.rows,t=totals(rows),p=data.provider||{},content=[],heading=detailed?'Relatório detalhado de caixa':'Relatório simples de caixa';
        var blue='#076ab7',green='#087f4f',red='#be123c',ink='#183c5b';
        function text(value,style) { return Object.assign({text:String(value==null?'':value)},style||{}); }
        function number(value,color,bold) { return text(money(value),{alignment:'right',color:color||ink,bold:!!bold}); }
        var lineLayout={hLineWidth:function(){return .5;},vLineWidth:function(){return .5;},hLineColor:function(){return '#d3dfeb';},vLineColor:function(){return '#d3dfeb';},paddingLeft:function(){return 4;},paddingRight:function(){return 4;},paddingTop:function(){return 5;},paddingBottom:function(){return 5;}};
        function header(labels) { return labels.map(function(v){return text(v,{bold:true,fontSize:8.5,fillColor:'#e9f2fb',color:ink});}); }
        var company=[text(p.name||'Provedor não informado',{bold:true,fontSize:15,color:ink}),text(p.legalName?'Razão social: '+p.legalName:'',{margin:[0,4,0,0]}),text('CNPJ: '+(p.cnpj||'Não informado no MK-AUTH'),{margin:[0,4,0,0]})];
        if(/^data:image\/(png|jpeg);base64,[A-Za-z0-9+/=]+$/.test(p.logo||''))content.push({columns:[{image:p.logo,fit:[85,55],width:95},{stack:company}],margin:[0,0,0,16]});else content.push({stack:company,margin:[0,0,0,16]});
        content.push(text(heading,{bold:true,fontSize:18,color:blue,margin:[0,0,0,8]}),text('Período: '+date(data.start)+' a '+date(data.end),{bold:true,margin:[0,0,0,5]}),text(data.scope,{margin:[0,0,0,5]}),text('Gerado em '+data.generated+' · '+rows.length+' linha(s).',{fontSize:8,color:'#485e74',margin:[0,0,0,14]}));
        if(detailed) {
            var dims=[['account','Por conta bancária'],['method','Por forma de recebimento'],['reason','Por motivo'],['user','Por usuário']];
            function groupBlock(item) {
                var body=[header(['Descrição','Entrada','Tarifas','Saída total','Subtotal'])];
                grouped(rows,item[0]).forEach(function(v){body.push([text(v.name),number(v.incoming,green),number(v.fees,red),number(v.outgoing,red),number(v.incoming-v.outgoing)]);});
                return {stack:[text(item[1],{bold:true,fontSize:11,color:ink,margin:[0,0,0,7]}),{table:{headerRows:1,widths:['*',57,51,57,57],body:body,dontBreakRows:true},layout:lineLayout,fontSize:8}],margin:[0,0,0,16]};
            }
            for(var d=0;d<dims.length;d+=2)content.push({columns:[groupBlock(dims[d]),groupBlock(dims[d+1])],columnGap:14});
        }
        content.push(text('Lançamentos do período filtrado',{bold:true,fontSize:12,color:ink,margin:[0,8,0,8],pageBreak:detailed&&rows.length>10?'before':undefined}));
        var labels=detailed?['Cliente / título','Data','Conta / recebimento','Usuário','Histórico']:['Cliente','Título','Data'];
        var span=labels.length;labels=labels.concat(['Entrada','Acréscimos','Tarifas','Saída total','Subtotal']);
        var body=[header(labels)];
        rows.forEach(function(r){var values=detailed?[r.client+' / #'+r.title,date(r.date),r.account+' / '+r.method,r.user,r.history]:[r.client,r.title,date(r.date)];body.push(values.map(function(v){return text(v);}).concat([number(r.incoming,green),r.extraKnown?number(r.extra,blue):text('Não identificado',{color:blue}),number(Number(r.fee||0),red),number(r.outgoing,red),number(r.incoming-r.outgoing)]));});
        if(!rows.length){var empty=[{text:'Nenhum lançamento atende aos filtros.',colSpan:labels.length}];while(empty.length<labels.length)empty.push({});body.push(empty);}
        var last=[{text:'TOTAL',colSpan:span,alignment:'right',bold:true}];while(last.length<span)last.push({});body.push(last.concat([number(t.incoming,green,true),number(t.extra,blue,true),number(t.fees,red,true),number(t.outgoing,red,true),number(t.incoming-t.outgoing,ink,true)]));
        var available=(detailed?841.89:595.28)-64,percent=detailed?[20,9,12,6,13,8,8,8,8,8]:[28,7,15,10,10,10,10,10];
        content.push({table:{headerRows:1,widths:percent.map(function(w){return (available-labels.length*8-labels.length*.5)*w/100;}),body:body,dontBreakRows:true},layout:lineLayout,fontSize:detailed?8:9});
        content.push({unbreakable:true,stack:[text('Fechamento do caixa',{bold:true,fontSize:12,color:ink,margin:[0,16,0,7]}),text('Entradas: '+money(t.incoming),{bold:true,color:green,margin:[0,0,0,5]}),text('Saídas: '+money(t.outgoing),{bold:true,color:red,margin:[0,0,0,5]}),text('Tarifas de boleto: '+money(t.fees)+' — já incluídas nas saídas.',{color:red,margin:[0,0,0,5]}),text('Outras saídas: '+money(t.outgoing-t.fees),{margin:[0,0,0,5]}),text('Acréscimos identificados: '+money(t.extra)+' — já incluídos nas entradas.',{color:blue,margin:[0,0,0,8]}),text('Subtotal do caixa: '+money(t.incoming-t.outgoing),{bold:true,fontSize:13,color:ink})]});
        content.push(text('Subtotal = entradas − saídas, sem saldo anterior. Conta e forma de recebimento vêm do cadastro atual do título. Valores não identificados não são zero confirmado.',{fontSize:8,color:'#485e74',margin:[0,12,0,0]}));
        return {pageSize:'A4',pageOrientation:detailed?'landscape':'portrait',pageMargins:[32,32,32,36],defaultStyle:{font:'Roboto',fontSize:9,color:ink},content:content,info:{title:heading,author:p.name||'Provedor'},footer:function(current,total){return {text:current+' / '+total,alignment:'right',margin:[32,10,32,0],fontSize:8,color:'#485e74'};}};
    }
    var pdfAssets=typeof document!=='undefined'&&document.currentScript?new URL('vendor/pdfmake/',document.currentScript.src).href:'vendor/pdfmake/';
    var pdfReady;
    function loadPdf() {
        function script(file){return new Promise(function(resolve,reject){var el=document.createElement('script');el.src=pdfAssets+file;el.onload=resolve;el.onerror=function(){reject(new Error('Não foi possível carregar o gerador de PDF.'));};document.head.appendChild(el);});}
        if(!pdfReady)pdfReady=script('pdfmake.min.js').then(function(){return script('vfs_fonts.js');}).catch(function(error){pdfReady=null;throw error;});
        return pdfReady;
    }
    function choose(tab,action) {
        var existing=document.getElementById('rc-print-dialog');if(existing)existing.remove();
        var dialog=document.createElement('dialog');dialog.id='rc-print-dialog';dialog.style.cssText='position:fixed;inset:0;margin:auto;overflow:auto;box-sizing:border-box;padding:24px;border:1px solid #b7cce0;border-radius:10px;max-width:560px;max-height:calc(100dvh - 32px);width:calc(100% - 32px);color:#193b5a;background:white';
        dialog.innerHTML='<h2 style="color:#075a9e;font-size:21px;margin-bottom:12px">Como deseja o relatório?</h2><p style="font-size:14px;line-height:1.5">O relatório usa o período e os filtros aplicados, incluindo a escolha de exibir taxas.</p><div class="rc-print-options"><button type="button" class="rc-print-option" data-mode="simple"><strong>Simples</strong><span>Clientes, títulos, valores e fechamento do caixa.</span></button><button type="button" class="rc-print-option" data-mode="detailed"><strong>Detalhado</strong><span>Resumo por conta, recebimento, motivo e usuário, com todos os lançamentos.</span></button></div><button type="button" class="rc-print-cancel" data-mode="cancel">Cancelar</button><p role="alert"></p>';
        var previousOverflow=document.documentElement.style.overflow;document.documentElement.style.overflow='hidden';
        dialog.addEventListener('close',function(){document.documentElement.style.overflow=previousOverflow;dialog.remove();});
        if(action==='download'){dialog.querySelector('h2').textContent='Baixar relatório em PDF';dialog.querySelector('p').textContent='Escolha o modelo. O PDF terá logo, dados do provedor e os totais dos filtros aplicados.';}
        document.body.appendChild(dialog);
        dialog.addEventListener('click',function(event){var trigger=event.target.closest('[data-mode]');var mode=trigger?trigger.dataset.mode:'';if(!mode)return;if(mode==='cancel'){dialog.close();dialog.remove();return;}var report,data;try{data=collect(tab);report=build(data,mode==='detailed');}catch(error){dialog.querySelector('[role=alert]').textContent='Não foi possível preparar o relatório. Atualize a página e tente novamente.';return;}if(action==='download'){var status=dialog.querySelector('[role=alert]');status.textContent='Gerando PDF…';dialog.querySelectorAll('.rc-print-option').forEach(function(button){button.disabled=true;});loadPdf().then(function(){if(!dialog.isConnected)return;global.pdfMake.createPdf(pdfDefinition(data,mode==='detailed')).download('caixa-'+(mode==='detailed'?'detalhado':'simples')+'-'+data.start+'-a-'+data.end+'.pdf',function(){if(dialog.isConnected)dialog.close();});}).catch(function(error){status.textContent='Falha ao gerar PDF. Tente novamente.';dialog.querySelectorAll('.rc-print-option').forEach(function(button){button.disabled=false;});});return;}dialog.style.cssText='position:fixed;inset:0;margin:auto;overflow:hidden;box-sizing:border-box;display:flex;flex-direction:column;gap:10px;padding:14px;border:1px solid #b7cce0;border-radius:8px;width:94vw;max-width:1600px;height:90dvh;max-height:94dvh;background:white;color:#193b5a';dialog.innerHTML='<div style="display:flex;justify-content:space-between;align-items:center;gap:12px;min-height:40px;flex-shrink:0"><strong>Prévia do relatório</strong><button type="button" class="rc-preview-close" data-mode="cancel" aria-label="Fechar prévia do relatório" title="Fechar prévia (Esc)"><span aria-hidden="true">×</span> Fechar</button></div>';var frame=document.createElement('iframe');frame.title='Relatório de caixa para impressão';frame.style.cssText='display:block;flex:1;min-height:0;width:100%;border:1px solid #d3dfeb;background:white';frame.srcdoc=report;dialog.appendChild(frame);});
        dialog.showModal();
    }
    if(typeof module!=='undefined'&&module.exports)module.exports={build:build,totals:totals,pdfDefinition:pdfDefinition};
    if(global)global.rcPrintChoose=choose;
}(typeof window!=='undefined'?window:null));
