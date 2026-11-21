/* Uses only details rendered by the authorized cash report. No writes or extra requests. */
(function () {
    'use strict';
    var money = function (value) { return (Number(value || 0) / 100).toLocaleString('pt-BR', {style:'currency',currency:'BRL'}); };
    var date = function (value) { return !value || /^0000-00-00/.test(value) ? 'Não informado' : String(value).replace(/^(\d{4})-(\d{2})-(\d{2})/, '$3/$2/$1'); };
    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('.rc-title-detail');
        if (!trigger) return;
        var data;
        try { data = JSON.parse(trigger.dataset.payment); } catch (error) { return; }
        var dialog = document.createElement('dialog');
        dialog.className = 'rc-payment-dialog';
        dialog.setAttribute('aria-labelledby', 'rc-payment-heading');
        dialog.innerHTML = '<header><div><h2 id="rc-payment-heading"></h2><p></p></div><button type="button" class="rc-payment-close" aria-label="Fechar informações do pagamento">× Fechar</button></header><dl></dl><p class="rc-payment-note">Os dados do título refletem o cadastro atual. Tarifas e valores do lançamento correspondem à movimentação selecionada. Acréscimos já estão incluídos no valor recebido; tarifas já estão incluídas nas saídas.</p>';
        dialog.querySelector('h2').textContent = 'Pagamento do título #' + data.title;
        dialog.querySelector('header p').textContent = data.client + (data.login ? ' · ' + data.login : '');
        var fields = [
            ['Situação do título', data.status || 'Não informada'],
            ['Valor original', money(data.original)], ['Valor pago no título', money(data.paid), 'cash-in'],
            ['Vencimento', date(data.due)], ['Pagamento do título', date(data.paidDate)],
            ['Forma de recebimento', data.method || 'Não informada'], ['Conta bancária', data.account],
            ['Movimentação no caixa', date(data.movement)], ['Usuário do lançamento', data.user || 'Não informado'],
            ['Entrada deste lançamento', money(data.incoming), 'cash-in'],
            ['Acréscimos pagos identificados', data.extra === null ? 'Não identificado' : money(data.extra), 'cash-extra'],
            ['Tarifa vinculada ao recebimento', money(data.fee), 'cash-out'],
            ['Saída deste lançamento', money(data.outgoing), 'cash-out'], ['Histórico', data.history]
        ];
        fields.forEach(function (field) {
            var item = document.createElement('div'), label = document.createElement('dt'), value = document.createElement('dd');
            label.textContent = field[0]; value.textContent = field[1] || 'Não informado';
            if (field[2]) value.className = field[2];
            item.append(label, value); dialog.querySelector('dl').appendChild(item);
        });
        var previousOverflow = document.documentElement.style.overflow;
        document.documentElement.style.overflow = 'hidden';
        dialog.addEventListener('close', function () { document.documentElement.style.overflow = previousOverflow; dialog.remove(); trigger.focus(); });
        dialog.querySelector('button').addEventListener('click', function () { dialog.close(); });
        dialog.addEventListener('click', function (e) { if (e.target === dialog) { var box = dialog.getBoundingClientRect(); if (e.clientX < box.left || e.clientX > box.right || e.clientY < box.top || e.clientY > box.bottom) dialog.close(); } });
        document.body.appendChild(dialog); dialog.showModal();
    });
}());
