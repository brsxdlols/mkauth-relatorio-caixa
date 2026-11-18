/* Keep the original cells and datasets used by filters, CSV and PDF. */
(function () {
    'use strict';
    function prepare() {
        document.querySelectorAll('#rc-caixa table').forEach(function (table) {
            var heading = table.tHead && table.tHead.rows[table.tHead.rows.length - 1];
            if (!heading) return;
            var labels = [];
            Array.prototype.forEach.call(heading.cells, function (cell) {
                for (var n = 0; n < cell.colSpan; n++) labels.push(cell.textContent.trim());
            });
            table.classList.add('rc-mobile-table');
            Array.prototype.forEach.call(table.rows, function (row) {
                if (row.parentNode === table.tHead) return;
                var column = 0;
                Array.prototype.forEach.call(row.cells, function (cell) {
                    if (cell.colSpan === 1) cell.setAttribute('data-label', labels[column] || '');
                    else cell.classList.add('rc-mobile-wide-cell');
                    if (cell.colSpan > 1 && !cell.textContent.trim()) cell.classList.add('rc-mobile-empty-cell');
                    column += cell.colSpan;
                });
            });
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', prepare);
    else prepare();
}());
