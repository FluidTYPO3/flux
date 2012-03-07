<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Claus Due <claus@wildside.dk>, Wildside A/S
 *
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
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 * FlexForm configuration container ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Xml
 */
class Tx_Flux_ViewHelpers_Xml_NodeViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractTagBasedViewHelper {

    /**
     * @var string
     */
    protected $tagName = 'node';

    /**
     * Initialize
     */
    public function initializeArguments() {
        $this->registerArgument('tagName', 'string', 'Tag name of the node', FALSE, 'node');
		$this->registerArgument('type', 'string', 'Type of node (FlexForm compatible)', FALSE);
		$this->registerArgument('wrapWithTceForms', 'boolean', 'If TRUE, wraps tag content in <TCEforms>', FALSE, FALSE);
    }

    /**
     * Render
     */
    public function render() {
        $this->tagName = $this->arguments['tagName'];
        $this->tag->setTagName($this->tagName);
		if ($this->arguments['type']) {
			$this->tag->addAttribute('type', $this->arguments['type']);
		}
        $content = $this->renderChildren();
		if ((bool) $this->arguments['wrapWithTceForms'] === TRUE) {
			$content = '<TCEforms>' . LF . $content . LF . '</TCEforms>';
		}
        $this->tag->setContent($content);
		return $this->tag->render();
    }

}

?>