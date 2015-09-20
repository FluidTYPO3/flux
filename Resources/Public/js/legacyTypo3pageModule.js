/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

Ext.ns('TYPO3', 'TYPO3.Components');

TYPO3.Components.PageModule = {
	/**
	 * Initialization
	 */
	init: function() {
		this.enableHighlighting();
		this.enableDragDrop();
	},

	/**
	 * This method is used to bind the higlighting function "setElementActive"
	 * to the mouseover event and the "setElementInactive" to the mouseout event.
	 */
	enableHighlighting: function() {
		Ext.select('div.t3-page-ce').removeClass('active');
		Ext.select('div.t3-page-ce-dragitem')
			.on('mouseover',this.setElementActive, this)
			.on('mouseout',this.setElementInactive, this);
		Ext.select('td.t3-page-column')
			.on('mouseover',this.setColumnActive, this)
			.on('mouseout',this.setColumnInactive, this);
		Ext.select('#typo3-dblist-sysnotes div.single-note')
			.on('mouseover',this.setSysnoteActive, this)
			.on('mouseout',this.setSysnoteInactive, this);
	},

	/**
	 * This method is used to unbind the higlighting function "setElementActive"
	 * from the mouseover event and the "setElementInactive" from the mouseout event.
	 */
	disableHighlighting: function() {
		Ext.select('div.t3-page-ce-dragitem')
			.un('mouseover', this.setElementActive, this)
			.un('mouseout', this.setElementInactive, this);
		Ext.select('td.t3-page-column')
			.un('mouseover',this.setColumnActive, this)
			.un('mouseout',this.setColumnInactive, this);
	},

	/**
	 * This method is used as an event handler when the
	 * user hovers the a content element.
	 */
	setElementActive: function(event, target) {
		Ext.get(target).findParent('div.t3-page-ce-dragitem', null, true).findParent('div.t3-page-ce', null, true).addClass('active');
	},

	/**
	 * This method is used as event handler to unset active state of
	 * a content element when the mouse of the user leaves the
	 * content element.
	 */
	setElementInactive: function(event, target) {
		Ext.get(target).findParent('div.t3-page-ce-dragitem', null, true).findParent('div.t3-page-ce', null, true).removeClass('active');

	},

	/**
	 * This method is used as an event handler when the
	 * user hovers the a content element.
	 */
	setColumnActive: function(event, target) {
		Ext.get(target).findParent('td.t3-page-column', null, true).addClass('active');
	},

	/**
	 * This method is used as event handler to unset active state of
	 * a content element when the mouse of the user leaves the
	 * content element.
	 */
	setColumnInactive: function(event, target) {
		Ext.get(target).findParent('td.t3-page-column', null, true).removeClass('active');
	},

	/**
	 * This method is used as an event handler when the
	 * user hovers the a sysnote.
	 */
	setSysnoteActive: function(event, target) {
		Ext.get(target).findParent('div.single-note', null, true).addClass('active');
	},

	/**
	 * This method is used as event handler to unset active state of
	 * a sysnote when the mouse of the user leaves the sysnote.
	 */
	setSysnoteInactive: function(event, target) {
		Ext.get(target).findParent('div.single-note', null, true).removeClass('active');

	},

	/**
	 * This method configures the drag'n'drop behavior in the page module
	 */
	enableDragDrop: function() {
		var overrides = {
			// Called the instance the element is dragged.
			b4StartDrag: function () {
				// Cache the drag element
				if (!this.el) {
					this.el = Ext.get(this.getEl());
				}

				// Add css class for the drag shadow
				this.el.child('.t3-page-ce-dragitem').addClass('dragitem-shadow');
				// Hide create new element button
				this.el.select('.t3-icon-document-new').addClass('drag-start');

				// Hide create new element button
				this.el.findParent('td.t3-page-column', null, true).removeClass('active');
				// Disable highlighting
				TYPO3.Components.PageModule.disableHighlighting();

				// Cache the original XY Coordinates of the element, we'll use this later.
				this.originalXY = this.el.getXY();

				// Cache previous and invalid drop zones
				this.previousDropZone = this.el.prev().select('.t3-page-ce-dropzone').last();
				this.invalidDropZones = this.el.select('.t3-page-ce-dropzone').add(this.previousDropZone);

				// Cache previous drop zone height
				this.previousDropZoneOldHeight = this.previousDropZone.getHeight();

				// Make element position absolute
				// To do this, the previous drop zone must cover for the element
				// Also set the current elements width so it doesnt shrink
				this.previousDropZone.setHeight(this.el.getBottom() - this.previousDropZone.getY());
				this.el.setWidth(this.el.getWidth());
				this.el.dom.style.position = 'absolute';
				this.el.dom.style.zIndex = '9999';

				// Go through all drop zones and make them available if not invalid
				var dropZones = Ext.select('.t3-page-ce-dropzone');
				var self = this;
				Ext.each(dropZones.elements, function(el) {
					var dropZoneElement = Ext.get(el);
					// Only highlight valid drop targets
					if (self.invalidDropZones.indexOf(dropZoneElement) == -1) {
						dropZoneElement.addClass('t3-page-ce-dropzone-available');
					}
				});
			},
			// Called when element is dropped not anything other than a dropzone with the same ddgroup
			onInvalidDrop: function () {
				// Set a flag to invoke the animated repair
				this.invalidDrop = true;
			},
			// Called when the drag operation completes
			endDrag: function () {
				// Invoke the animation if the invalidDrop flag is set to true
				if (this.invalidDrop === true) {
					// Remove the drop invitation
					this.el.removeClass('dropOK');

					// Create the animation configuration object
					var animCfgObj = {
						easing:'easeOut',
						duration:0.3,
						scope:this,
						callback: function () {
							// Remove the position attribute
							// Restore previous drop zone height
							this.previousDropZone.setHeight(this.previousDropZoneOldHeight);
							this.el.dom.style.position = '';
							this.el.dom.style.zIndex = '';
							this.el.dom.style.top = '';
							this.el.dom.style.left = '';
							this.el.dom.style.width = '';

							// Enable Highlighting
							TYPO3.Components.PageModule.enableHighlighting();
						}
					};

					// Apply the repair animation
					this.el.moveTo(this.originalXY[0], this.originalXY[1], animCfgObj);
					delete this.invalidDrop;
				}

				// Go through all drop zones and make them unavailable
				var dropZones = Ext.select('.t3-page-ce-dropzone');
				Ext.each(dropZones.elements, function(el) {
					Ext.get(el).removeClass('t3-page-ce-dropzone-available');
				});

				// Remove dragitem-shadow after dragging
				this.el.child('.t3-page-ce-dragitem').removeClass('dragitem-shadow');
				// Show create new element button again
				this.el.select('.t3-icon-document-new').removeClass('drag-start');

				// Show create new element button
				this.el.findParent('td.t3-page-column', null, true).addClass('active');
			},

			// Called upon successful drop of an element on a DDTarget with the same
			onDragDrop: function (evtObj, targetElId) {
				// Wrap the drop target element with Ext.Element
				var dropEl = Ext.get(targetElId);

				// Perform the node move only if not dropped on the dropzone directly above
				// this element
				if (this.invalidDropZones.indexOf(dropEl) == -1) {
					// Remove the drag invitation
					this.onDragOut(evtObj, targetElId);

					// Set to new width
					this.el.setWidth(dropEl.parent().getWidth());

					// Add height to drop zone
					var oldHeight = dropEl.getHeight();
					dropEl.setHeight(oldHeight + this.el.getHeight(), {duration: 0.3});

					// Calculate new y position for element
					var dropElStyle = dropEl.dom.currentStyle || window.getComputedStyle(dropEl.dom);
					var elementNewY = dropEl.getY() + oldHeight + parseInt(dropElStyle.marginBottom);

					// Create the animation configuration object
					var animCfgObj = {
						easing: 'easeOut',
						duration: 0.4,
						scope: this,
						callback: function () {
							// Move the element
							dropEl.parent().insertSibling(this.el, 'after');

							// restore dropzone height
							dropEl.setHeight(oldHeight);

							// Clear the styles
							this.el.dom.style.position = '';
							this.el.dom.style.zIndex = '';
							this.el.dom.style.top = '';
							this.el.dom.style.left = '';
							this.el.dom.style.width = '';

							// Restore height of previous drop zone
							this.previousDropZone.setHeight(this.previousDropZoneOldHeight, {duration: 0.3, callback: function () {
								// Enable Highlighting
								TYPO3.Components.PageModule.enableHighlighting();
							}});
						}
					};

					// Animate to new position and width
					this.el.moveTo(dropEl.parent().getX(), elementNewY, animCfgObj);

					// Try to save changes to the backend
					// There is no feedback from the server side functions, just hope for the best
					TYPO3.Components.DragAndDrop.CommandController.moveContentElement(
						this.el.id,
						targetElId,
						dropEl.parent().id,
						this
					);

				} else {
					// This was an invalid drop, initiate a repair
					this.onInvalidDrop();
				}
			},
			// Only called when the drag element is dragged over the a drop target with the same ddgroup
			onDragEnter: function (evtObj, targetElId) {
				// Wrap the drop target element with Ext.Element
				var dropEl = Ext.get(targetElId);
				// Perform the node move only if not dropped on the dropzone directly above
				// this element
				if (this.invalidDropZones.indexOf(dropEl) == -1) {
					this.el.addClass('dropOK');
					Ext.get(targetElId).addClass('dropReceiveOK');
				} else {
					// Remove the invitation
					this.onDragOut();
				}
			},
			// Only called when element is dragged out of a dropzone with the same ddgroup
			onDragOut: function (evtObj, targetElId) {
				this.el.removeClass('dropOK');
				if (targetElId) {
					Ext.get(targetElId).removeClass('dropReceiveOK');
				}
			},

			/**
			 * Evaluates a response from an ext direct call and shows a flash message
			 * if it was an exceptional result
			 *
			 * @param {Object} response
			 * @return {Boolean}
			 */
			evaluateResponse: function (response) {
				if (response.success === false) {
					TYPO3.Flashmessage.display(4, 'Exception', response.message);
					return false;
				}

				return true;
			}
		};

		var contentElements = Ext.select('.t3-page-ce');
		Ext.each(contentElements.elements, function (el) {
			if (Ext.DomQuery.is(el, 'div:has(.t3-page-ce-dragitem)')) {
				var dd = new Ext.dd.DD(el, 'ceDDgroup', {
					isTarget : false
				});
				// Apply overrides to newly created instance
				Ext.apply(dd, overrides);
			}
		});

		// Find dropzones and add them to the group
		var dropZones = Ext.select('.t3-page-ce-dropzone');
		Ext.each(dropZones.elements, function(el) {
			var dropTarget = new Ext.dd.DDTarget(el, 'ceDDgroup');
		});
	}
}

Ext.onReady(function() {
	TYPO3.Components.PageModule.init();
});
