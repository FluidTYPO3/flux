/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Danilo Bürger <danilo.buerger@hmspl.de>, Heimspiel GmbH
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

Ext.ns('FluidTYPO3', 'FluidTYPO3.Components');

FluidTYPO3.Components.FluxCollapse = {

	init: function() {
		Ext.select('.toggle-content').on('click', this.fluxCollapse, this);
	},

	fluxCollapse: function(event, target) {
		var cookie = Ext.decode(Ext.util.Cookies.get('fluxCollapseStates'));
		if (cookie == '') {
			cookie = [];
		};

		var toggle = Ext.get(target),
			toggleContent = toggle.findParent('.toggle-content', null, true),
			fluxGrid = toggleContent.next('.flux-grid'),
			uid = toggleContent.getAttribute('data-uid');

		if (fluxGrid.hasClass('flux-grid-hidden')) {
			fluxGrid.removeClass('flux-grid-hidden');
			toggle.replaceClass('t3-icon-view-table-expand', 't3-icon-view-table-collapse');
			for (var i in cookie) {
				if (cookie[i] == uid) {
					delete(cookie[i]);
				};
			};
		} else {
			fluxGrid.addClass('flux-grid-hidden');
			toggle.replaceClass('t3-icon-view-table-collapse', 't3-icon-view-table-expand');
			if (cookie.indexOf(uid) < 0) {
				cookie.push(uid);
			};
		};

		Ext.util.Cookies.set('fluxCollapseStates', Ext.encode(cookie));
	},

}

Ext.onReady(function() {
	FluidTYPO3.Components.FluxCollapse.init();
});
