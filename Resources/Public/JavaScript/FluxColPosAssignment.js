/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
class FluxColPosAssignment
{
	constructor()
	{
		var targetNodes = document.querySelectorAll('.t3-flex-container');
		var observer = new MutationObserver(this.handleMutation);
		Array.prototype.forEach.call(targetNodes, function (targetNode) {
			observer.observe(targetNode, { childList: true, characterData: false });
		});
	}

	isVisibleSection(element)
	{
		var classes = element.classList;
		return element.nodeType === Node.ELEMENT_NODE && classes.contains('t3js-flex-section') && !classes.contains('t3js-flex-section-deleted');
	}

	getSectionColPosInput(section)
	{
		return section.querySelector('.flux-flex-colPos-input');
	}

	getSectionColPosText(section)
	{
		return section.querySelector('.flux-flex-colPos-text');
	}

	determineCurrentlyTakenColPos(container)
	{
		return Array.prototype.reduce.call(container.childNodes, function (acc, containerChild) {
			if (colPosAssigner.isVisibleSection(containerChild)) {
				var value = colPosAssigner.getSectionColPosInput(containerChild).value;
				if (value !== '') {
					acc.push(parseInt(value));
				}
			}
			return acc;
		}, []);
	}

	determineFreeColPos(minColPos, maxColPos, takenColPos)
	{
		for (var colPos = minColPos; colPos <= maxColPos; colPos++) {
			if (takenColPos.indexOf(colPos) === -1) {
				return colPos;
			}
		}
	}

	handleAddedSection(section, container)
	{
		var input = colPosAssigner.getSectionColPosInput(section);
		if (input === null || input.value !== '') {
			return;
		}
		var minColPos = parseInt(input.dataset.minValue);
		var maxColPos = parseInt(input.dataset.maxValue);
		var takenColPos = [];
		if (input.dataset.takenValues !== '') {
			takenColPos = input.dataset.takenValues.split(',').map(function (colPosStr) { return parseInt(colPosStr); });
		}
		takenColPos = takenColPos.concat(colPosAssigner.determineCurrentlyTakenColPos(container));

		var colPos = colPosAssigner.determineFreeColPos(minColPos, maxColPos, takenColPos);
		input.value = colPos;

		var label = colPosAssigner.getSectionColPosText(section);
		label.innerText = colPos;
	}

	handleMutation(mutationList)
	{
		mutationList.forEach(function (mutation) {
			Array.prototype.forEach.call(mutation.addedNodes, function (addedElement) {
				if (!colPosAssigner.isVisibleSection(addedElement)) {
					return;
				}
				colPosAssigner.handleAddedSection(addedElement, mutation.target);
			});
		});
	}
};

var colPosAssigner = new FluxColPosAssignment;

export default colPosAssigner;
