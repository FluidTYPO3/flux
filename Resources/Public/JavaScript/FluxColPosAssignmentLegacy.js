/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
define(['jquery'], function ($) {
	function isVisibleSection(element) {
		var classes = element.classList;
		return element.nodeType === Node.ELEMENT_NODE && classes.contains('t3js-flex-section') && !classes.contains('t3js-flex-section-deleted');
	}

	function getSectionColPosInput(section) {
		return section.querySelector('.flux-flex-colPos-input');
	}

	function getSectionColPosText(section) {
		return section.querySelector('.flux-flex-colPos-text');
	}

	function determineCurrentlyTakenColPos(container) {
		return Array.prototype.reduce.call(container.childNodes, function (acc, containerChild) {
			if (isVisibleSection(containerChild)) {
				var value = getSectionColPosInput(containerChild).value;
				if (value !== '') {
					acc.push(parseInt(value));
				}
			}
			return acc;
		}, []);
	}

	function determineFreeColPos(minColPos, maxColPos, takenColPos) {
		for (var colPos = minColPos; colPos <= maxColPos; colPos++) {
			if (takenColPos.indexOf(colPos) === -1) {
				return colPos;
			}
		}
	}

	function handleAddedSection(section, container) {
		var input = getSectionColPosInput(section);
		if (input === null || input.value !== '') {
			return;
		}
		var minColPos = parseInt(input.dataset.minValue);
		var maxColPos = parseInt(input.dataset.maxValue);
		var takenColPos = [];
		if (input.dataset.takenValues !== '') {
			takenColPos = input.dataset.takenValues.split(',').map(function (colPosStr) { return parseInt(colPosStr); });
		}
		takenColPos = takenColPos.concat(determineCurrentlyTakenColPos(container));

		var colPos = determineFreeColPos(minColPos, maxColPos, takenColPos);
		input.value = colPos;

		var label = getSectionColPosText(section);
		label.innerText = colPos;
	}

	function handleMutation(mutationList) {
		mutationList.forEach(function (mutation) {
			Array.prototype.forEach.call(mutation.addedNodes, function (addedElement) {
				if (!isVisibleSection(addedElement)) {
					return;
				}
				handleAddedSection(addedElement, mutation.target);
			});
		});
	}

	function init() {
		var targetNodes = document.querySelectorAll('.t3-flex-container');
		var observer = new MutationObserver(handleMutation);
		Array.prototype.forEach.call(targetNodes, function (targetNode) {
			observer.observe(targetNode, { childList: true, characterData: false });
		});
	}

	$(function () {
		init();
	});

	return {};
});
