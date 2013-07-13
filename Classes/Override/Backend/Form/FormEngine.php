<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Claus Due <claus@wildside.dk>, Wildside A/S
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
 ***************************************************************/

/**
 * FormEngine Override
 *
 * @author Claus Due, Wildside A/S
 * @package Flux
 * @subpackage Override\Backend\Form
 */
class Tx_Flux_Override_Backend_Form_FormEngine extends t3lib_TCEforms {

	/**
	 * Rendering a single item for the form
	 *
	 * @param string $table Table name of record
	 * @param string $field Fieldname to render
	 * @param array $row The record
	 * @param array $PA Parameters array containing a lot of stuff. Value by Reference!
	 * @return string Returns the item as HTML code to insert
	 * @access private
	 * @see getSingleField(), getSingleField_typeFlex_draw()
	 * @todo Define visibility
	 */
	public function getSingleField_SW($table, $field, $row, &$PA) {
		try {
			$field = parent::getSingleField_SW($table, $field, $row, $PA);
		} catch (\TYPO3\CMS\Core\Resource\Exception $error) {
			$message = new t3lib_FlashMessage('WARNING! FAL resource problem detected. The field "' . $field . '" has been reset to ' .
				'an empty value in order to prevent fatal, unrecoverable errors. The actual message is a ' . get_class($error) .
				' which states: (' . $error->getCode() . ') ' . $error->getMessage(), 'WARNING', t3lib_div::SYSLOG_SEVERITY_FATAL);
			t3lib_FlashMessageQueue::addMessage($message);
			$PA['itemFormElValue'] = '';
			$field = parent::getSingleField_SW($table, $field, $row, $PA);
		} catch (Exception $error) {
			t3lib_div::sysLog($error->getMessage(), 'cms', t3lib_div::SYSLOG_SEVERITY_FATAL);
		}
		return $field;
	}

}
