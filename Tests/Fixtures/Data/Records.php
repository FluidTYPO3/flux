<?php
namespace FluidTYPO3\Flux\Tests\Fixtures\Data;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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

use FluidTYPO3\Flux\Service\ContentService;

/**
 * @package Flux
 */
class Records {

	const DEFAULT_CONTENTAREA = 'content';
	const UID_CONTENT_NOPARENTNOCHILDREN = 90000001;
	const UID_CONTENT_NOPARENTWITHCHILDREN = 90000002;
	const UID_CONTENT_PARENT = 90000003;
	const UID_CONTENT_CHILD = 90000004;
	const UID_CONTENT_PARENTANDCHILDREN = 90000005;

	const UID_TEMPLATE_ROOT = 91000001;

	/**
	 * @var array
	 */
	public static $sysTemplateRoot = array(
		'uid' => self::UID_TEMPLATE_ROOT,
		'root' => 1,
		'include_static_file' => '',
	);

	/**
	 * @var array
	 */
	public static $contentRecordWithoutParentAndWithoutChildren = array(
		'uid' => self::UID_CONTENT_NOPARENTNOCHILDREN,
		'header' => 'Has no parent',
		'colPos' => 0,
		'tx_flux_parent' => 0,
		'tx_flux_column' => '',
		'test' => '<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<T3FlexForms>
    <data>
        <sheet index="options">
            <language index="lDEF">
                <field index="settings.flux.placeholder">
                    <value index="vDEF">0</value>
                </field>
            </language>
        </sheet>
    </data>
</T3FlexForms>
'
	);

	/**
	 * @var array
	 */
	public static $contentRecordIsParentAndHasChildren = array(
		'uid' => self::UID_CONTENT_PARENT,
		'header' => 'Is itself parent, has no parent',
		'colPos' => 0,
		'tx_flux_parent' => 0,
		'tx_flux_column' => '',
		'tx_flux_children' => 1
	);

	/**
	 * @var array
	 */
	public static $contentRecordWithParentAndWithoutChildren = array(
		'uid' => self::UID_CONTENT_CHILD,
		'header' => 'Has parent, is in default content area',
		'colPos' => ContentService::COLPOS_FLUXCONTENT,
		'tx_flux_parent' => self::UID_CONTENT_NOPARENTWITHCHILDREN,
		'tx_flux_column' => self::DEFAULT_CONTENTAREA
	);

	/**
	 * @var array
	 */
	public static $contentRecordWithParentAndChildren = array(
		'uid' => self::UID_CONTENT_NOPARENTWITHCHILDREN,
		'header' => 'Has parent, is in default content area',
		'colPos' => ContentService::COLPOS_FLUXCONTENT,
		'tx_flux_parent' => 0,
		'tx_flux_column' => '',
		'tx_flux_children' => 1
	);

}