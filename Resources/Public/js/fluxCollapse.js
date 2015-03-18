

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

Ext.ns('FluidTYPO3', 'FluidTYPO3.Components');

FluidTYPO3.Components.FluxCollapse = {

	init: function () {
		Ext.select('.toggle-content').on('click', this.fluxCollapse, this);
	},

	fluxCollapse: function (event, target) {
		var cookie = Ext.decode(Ext.util.Cookies.get('fluxCollapseStates'));
		if (cookie == '') {
			cookie = [];
		}

		var toggle = Ext.get(target),
			toggleContent = toggle.findParent('.toggle-content', null, true),
			fluxGrid = toggleContent.next('.flux-grid'),
			uid = toggleContent.getAttribute('data-uid');

		if (fluxGrid.hasClass('flux-grid-hidden')) {
			fluxGrid.removeClass('flux-grid-hidden');
			toggle.replaceClass('t3-icon-view-table-expand', 't3-icon-view-table-collapse');
			for (var i in cookie) {
				if (cookie.hasOwnProperty(i)) {
					if (cookie[i] == uid) {
						delete(cookie[i]);
					}
				}
			}
		} else {
			fluxGrid.addClass('flux-grid-hidden');
			toggle.replaceClass('t3-icon-view-table-collapse', 't3-icon-view-table-expand');
			if (cookie.indexOf(uid) < 0) {
				cookie.push(uid);
			}
		}
		Ext.util.Cookies.set('fluxCollapseStates', Ext.encode(cookie));
	}
};

Ext.onReady(function () {
	FluidTYPO3.Components.FluxCollapse.init();
});
