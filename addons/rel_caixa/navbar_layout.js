/* No native menu changes: only the page's single HTML offset is updated. */
(function () {
    'use strict';
    var root = document.getElementById('rc-caixa-page');
    var top = document.getElementById('rc-native-top');
    if (!root || !top) return;
    var queued = false;
    function measure() {
        queued = false;
        var bottom = 0;
        var menus = top.querySelectorAll('.navbar, nav, [role="navigation"]');
        for (var i = 0; i < menus.length; i++) {
            var menu = menus[i], style = window.getComputedStyle(menu);
            if (style.position !== 'fixed' || style.display === 'none' || style.visibility === 'hidden') continue;
            var box = menu.getBoundingClientRect();
            if (box.height > 0 && box.top <= 1) bottom = Math.max(bottom, box.bottom);
        }
        var value = Math.ceil(bottom) + 'px';
        if (root.style.getPropertyValue('--rc-navbar-height') !== value) root.style.setProperty('--rc-navbar-height', value);
    }
    function schedule() {
        if (!queued) { queued = true; window.requestAnimationFrame(measure); }
    }
    measure();
    window.addEventListener('resize', schedule);
    window.addEventListener('load', schedule);
    top.addEventListener('transitionend', schedule);
    if (window.ResizeObserver) {
        var sizes = new ResizeObserver(schedule);
        top.querySelectorAll('.navbar, nav, [role="navigation"]').forEach(function (menu) { sizes.observe(menu); });
    }
    if (window.MutationObserver) {
        new MutationObserver(schedule).observe(top, {subtree: true, childList: true, attributes: true, attributeFilter: ['class', 'style', 'hidden']});
    }
}());
